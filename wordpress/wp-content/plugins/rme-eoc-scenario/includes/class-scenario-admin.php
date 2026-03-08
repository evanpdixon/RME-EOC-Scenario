<?php
/**
 * WordPress admin page: UI, AJAX handlers, file downloads.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RME_EOC_Scenario_Admin {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_assets' ) );
        add_action( 'wp_ajax_rme_eoc_lookup', array( $this, 'ajax_lookup' ) );
        add_action( 'wp_ajax_rme_eoc_generate', array( $this, 'ajax_generate' ) );
        add_action( 'wp_ajax_rme_eoc_download', array( $this, 'handle_download' ) );
    }

    public function add_menu_page() {
        add_menu_page(
            'EOC Scenario Generator',
            'EOC Scenario',
            'manage_options',
            'rme-eoc-scenario',
            array( $this, 'render_page' ),
            'dashicons-megaphone',
            30
        );
    }

    public function enqueue_assets( $hook ) {
        if ( $hook !== 'toplevel_page_rme-eoc-scenario' ) {
            return;
        }
        wp_enqueue_style( 'rme-eoc-admin', RME_EOC_URL . 'assets/css/admin.css', array(), RME_EOC_VERSION );
        wp_enqueue_script( 'rme-eoc-admin', RME_EOC_URL . 'assets/js/admin.js', array( 'jquery' ), RME_EOC_VERSION, true );
        wp_localize_script( 'rme-eoc-admin', 'rmeEoc', array(
            'ajaxUrl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'rme_eoc_nonce' ),
            'presets' => RME_EOC_Location_Presets::get_all(),
        ) );
    }

    public function render_page() {
        include RME_EOC_PATH . 'templates/admin-main.php';
    }

    /**
     * AJAX: Look up location data from free APIs.
     */
    public function ajax_lookup() {
        check_ajax_referer( 'rme_eoc_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        $address  = sanitize_text_field( wp_unslash( $_POST['address'] ?? '' ) );
        $radius_m = absint( $_POST['radius'] ?? 16000 );

        if ( empty( $address ) ) {
            wp_send_json_error( 'Address is required.' );
        }

        // Geocode
        $geo = RME_EOC_Location_Lookup::geocode( $address );
        if ( is_wp_error( $geo ) ) {
            wp_send_json_error( $geo->get_error_message() );
        }

        // Find nearby POIs and roads
        $nearby = RME_EOC_Location_Lookup::find_nearby( $geo['lat'], $geo['lon'], $radius_m );
        if ( is_wp_error( $nearby ) ) {
            wp_send_json_error( $nearby->get_error_message() );
        }

        // Flood zones
        $flood = RME_EOC_Location_Lookup::find_flood_zones( $geo['lat'], $geo['lon'] );
        if ( is_wp_error( $flood ) ) {
            $flood = array( 'has_flood_zones' => false, 'zone_types' => array() );
        }

        wp_send_json_success( array(
            'geocode' => $geo,
            'nearby'  => $nearby,
            'flood'   => $flood,
        ) );
    }

    /**
     * AJAX: Generate scenario PDFs and TXT.
     */
    public function ajax_generate() {
        check_ajax_referer( 'rme_eoc_nonce', 'nonce' );

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_send_json_error( 'Unauthorized' );
        }

        $cfg = array();
        $fields = array(
            'location', 'venue', 'main_road', 'flooded_road', 'blocked_road',
            'highway', 'main_st', 'addr_a', 'addr_grandma', 'addr_uncle',
            'addr_neighbor', 'grandma_name', 'uncle_name', 'hospital',
            'gas_station', 'local_store',
        );

        foreach ( $fields as $field ) {
            $cfg[ $field ] = sanitize_text_field( wp_unslash( $_POST[ $field ] ?? '' ) );
        }

        $class_size = absint( $_POST['class_size'] ?? 20 );
        $formats    = isset( $_POST['formats'] ) ? array_map( 'sanitize_text_field', (array) $_POST['formats'] ) : array( 'pdf' );

        // Validate required fields
        $missing = array();
        foreach ( $fields as $field ) {
            if ( empty( $cfg[ $field ] ) ) {
                $missing[] = $field;
            }
        }
        if ( ! empty( $missing ) ) {
            wp_send_json_error( 'Missing fields: ' . implode( ', ', $missing ) );
        }

        // Build tasks and distribute
        $tasks       = RME_EOC_Task_Definitions::build_tasks( $cfg );
        $assignments = RME_EOC_Distribution::distribute( $class_size, $tasks );

        $result = array( 'files' => array() );

        // Generate PDFs
        if ( in_array( 'pdf', $formats, true ) ) {
            $pdf_result = RME_EOC_PDF_Generator::generate_all( $cfg, $assignments, $tasks );

            $upload_dir = wp_upload_dir();
            $base_url   = $upload_dir['baseurl'];
            $base_path  = $upload_dir['basedir'];

            // Build download URLs via AJAX handler
            $dir_relative = str_replace( $base_path, '', $pdf_result['output_dir'] );

            $result['files']['print_all'] = array(
                'name' => basename( $pdf_result['print_all_pdf'] ),
                'url'  => admin_url( 'admin-ajax.php?action=rme_eoc_download&file=' . urlencode( basename( $pdf_result['print_all_pdf'] ) ) . '&dir=' . urlencode( $dir_relative ) . '&nonce=' . wp_create_nonce( 'rme_eoc_download' ) ),
            );

            $result['files']['facilitator'] = array(
                'name' => basename( $pdf_result['facilitator_pdf'] ),
                'url'  => admin_url( 'admin-ajax.php?action=rme_eoc_download&file=' . urlencode( basename( $pdf_result['facilitator_pdf'] ) ) . '&dir=' . urlencode( $dir_relative ) . '&nonce=' . wp_create_nonce( 'rme_eoc_download' ) ),
            );

            $result['files']['students'] = array();
            foreach ( $pdf_result['student_pdfs'] as $path ) {
                $result['files']['students'][] = array(
                    'name' => basename( $path ),
                    'url'  => admin_url( 'admin-ajax.php?action=rme_eoc_download&file=' . urlencode( basename( $path ) ) . '&dir=' . urlencode( $dir_relative ) . '&nonce=' . wp_create_nonce( 'rme_eoc_download' ) ),
                );
            }
        }

        // Generate TXT
        if ( in_array( 'txt', $formats, true ) ) {
            $output_dir = isset( $pdf_result ) ? $pdf_result['output_dir'] : self::create_output_dir();
            $txt_path   = RME_EOC_TXT_Generator::generate( $cfg, $assignments, $output_dir );

            $upload_dir   = wp_upload_dir();
            $dir_relative = str_replace( $upload_dir['basedir'], '', $output_dir );

            $result['files']['txt'] = array(
                'name' => basename( $txt_path ),
                'url'  => admin_url( 'admin-ajax.php?action=rme_eoc_download&file=' . urlencode( basename( $txt_path ) ) . '&dir=' . urlencode( $dir_relative ) . '&nonce=' . wp_create_nonce( 'rme_eoc_download' ) ),
            );
        }

        $result['summary'] = sprintf(
            'Generated scenario for %s: %d students, %d tasks distributed.',
            $cfg['location'], $class_size, array_sum( array_map( 'count', $assignments ) )
        );

        wp_send_json_success( $result );
    }

    /**
     * Serve generated files for download.
     */
    public function handle_download() {
        if ( ! wp_verify_nonce( $_GET['nonce'] ?? '', 'rme_eoc_download' ) ) {
            wp_die( 'Invalid download link.' );
        }

        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( 'Unauthorized.' );
        }

        $file = sanitize_file_name( $_GET['file'] ?? '' );
        $dir  = sanitize_text_field( $_GET['dir'] ?? '' );

        $upload_dir = wp_upload_dir();
        $full_path  = $upload_dir['basedir'] . $dir . '/' . $file;

        // Security: ensure file is within uploads/rme-eoc-scenario/
        $real_path = realpath( $full_path );
        $safe_dir  = realpath( $upload_dir['basedir'] . '/rme-eoc-scenario' );

        if ( ! $real_path || ! $safe_dir || strpos( $real_path, $safe_dir ) !== 0 ) {
            wp_die( 'Invalid file path.' );
        }

        if ( ! file_exists( $real_path ) ) {
            wp_die( 'File not found.' );
        }

        $ext = pathinfo( $file, PATHINFO_EXTENSION );
        $content_type = $ext === 'pdf' ? 'application/pdf' : 'text/plain';

        header( 'Content-Type: ' . $content_type );
        header( 'Content-Disposition: attachment; filename="' . $file . '"' );
        header( 'Content-Length: ' . filesize( $real_path ) );
        readfile( $real_path );
        exit;
    }

    private static function create_output_dir() {
        $upload_dir = wp_upload_dir();
        $dir        = $upload_dir['basedir'] . '/rme-eoc-scenario/' . time();
        wp_mkdir_p( $dir );
        return $dir;
    }
}
