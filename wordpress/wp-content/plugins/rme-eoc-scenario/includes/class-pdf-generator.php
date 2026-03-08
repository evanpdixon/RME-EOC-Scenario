<?php
/**
 * PDF generation using TCPDF.
 * Ports the ReportLab rendering from zth_generator.py.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class RME_EOC_PDF_Generator {

    private static $copyright = "\xC2\xA9 Radio Made Easy. Proprietary training material. Unauthorized reproduction, distribution, or use outside of licensed Radio Made Easy courses is strictly prohibited.";

    /**
     * Generate all PDFs for a scenario.
     *
     * @param array $cfg         Config array.
     * @param array $assignments Distribution assignments.
     * @param array $tasks       All tasks.
     * @return array { student_pdfs, facilitator_pdf, print_all_pdf }
     */
    public static function generate_all( $cfg, $assignments, $tasks ) {
        self::load_tcpdf();

        $output_dir = self::get_output_dir();
        $location   = $cfg['location'];
        $loc_slug   = str_replace( array( ', ', ' ' ), '_', $location );

        $contradictions = RME_EOC_Task_Definitions::build_contradictions( $cfg );
        $cross_refs     = RME_EOC_Task_Definitions::build_cross_refs( $cfg );

        // Generate print-all PDF (facilitator + all students in one document)
        $print_all_path = $output_dir . '/PrintAll_' . $loc_slug . '.pdf';
        $print_all_pdf  = self::create_pdf( 0.75 );

        // Facilitator reference pages
        self::add_facilitator_pages( $print_all_pdf, $assignments, $cfg, $contradictions, $cross_refs );

        // Student card pages
        $student_pdfs = array();
        foreach ( $assignments as $i => $student_tasks ) {
            $num = $i + 1;

            // Individual student PDF
            $student_path = $output_dir . '/Student_' . sprintf( '%02d', $num ) . '.pdf';
            $student_pdf  = self::create_pdf( 0.85 );
            foreach ( $student_tasks as $idx => $task ) {
                if ( $idx > 0 ) {
                    $student_pdf->AddPage();
                }
                self::render_task_card( $student_pdf, $task, $num, $cfg );
            }
            $student_pdf->Output( $student_path, 'F' );
            $student_pdfs[] = $student_path;

            // Also add to print-all
            foreach ( $student_tasks as $task ) {
                $print_all_pdf->AddPage();
                self::render_task_card( $print_all_pdf, $task, $num, $cfg );
            }
        }

        // Facilitator-only PDF
        $fac_path = $output_dir . '/Facilitator_Reference.pdf';
        $fac_pdf  = self::create_pdf( 0.75 );
        self::add_facilitator_pages( $fac_pdf, $assignments, $cfg, $contradictions, $cross_refs );
        $fac_pdf->Output( $fac_path, 'F' );

        $print_all_pdf->Output( $print_all_path, 'F' );

        return array(
            'student_pdfs'    => $student_pdfs,
            'facilitator_pdf' => $fac_path,
            'print_all_pdf'   => $print_all_path,
            'output_dir'      => $output_dir,
        );
    }

    private static function load_tcpdf() {
        $autoload = RME_EOC_PATH . 'vendor/autoload.php';
        if ( file_exists( $autoload ) ) {
            require_once $autoload;
        }
    }

    private static function get_output_dir() {
        $upload_dir = wp_upload_dir();
        $dir        = $upload_dir['basedir'] . '/rme-eoc-scenario/' . time();
        wp_mkdir_p( $dir );
        return $dir;
    }

    /**
     * Create a TCPDF instance with common settings.
     */
    private static function create_pdf( $margin_inches = 0.85 ) {
        $margin_mm = $margin_inches * 25.4;
        $pdf = new RME_EOC_TCPDF( 'P', 'mm', 'LETTER', true, 'UTF-8', false );
        $pdf->SetCreator( 'RME EOC Scenario Generator' );
        $pdf->SetAuthor( 'Radio Made Easy' );
        $pdf->setPrintHeader( false );
        $pdf->setPrintFooter( true );
        $pdf->SetMargins( $margin_mm, $margin_mm, $margin_mm );
        $pdf->SetAutoPageBreak( true, $margin_mm + 5 );
        $pdf->SetFont( 'helvetica', '', 10 );
        $pdf->AddPage();
        return $pdf;
    }

    /**
     * Render a single task card onto the PDF.
     */
    private static function render_task_card( $pdf, $task, $student_num, $cfg ) {
        // Logo at top
        $logo_path = RME_EOC_PATH . 'assets/images/logo.png';
        if ( file_exists( $logo_path ) ) {
            $pdf->Image( $logo_path, '', '', 40, 0, '', '', 'T', false, 300, 'C' );
            $pdf->Ln( 4 );
        }

        $html = '';

        // Priority banner
        if ( $task['priority'] ) {
            $html .= '<table cellpadding="6" style="width:100%;">
                <tr><td style="background-color:#FF0000;color:#FFFFFF;font-weight:bold;font-size:11px;text-align:center;">
                &#9888; HIGH PRIORITY -- REPORT IMMEDIATELY TO NET CONTROL &#9888;
                </td></tr></table><br/>';
        }

        // Title
        $color = $task['priority'] ? 'color:#FF0000;' : '';
        $html .= sprintf(
            '<h1 style="text-align:center;font-size:20px;%s">Task %s (%s)</h1>',
            $color, esc_html( $task['letter'] ), esc_html( $task['phonetic'] )
        );

        // Subtitle
        $html .= sprintf(
            '<p style="text-align:center;font-size:11px;color:#888888;">%s</p>',
            esc_html( $cfg['location'] )
        );

        // Intro
        $html .= '<p style="font-size:10px;">You are within the disaster zone without cell phone, internet access, or electricity.</p>';

        // Student notice
        $html .= '<table cellpadding="4" style="width:100%;"><tr><td style="background-color:#FFF3CD;border:1px solid #FFCD39;font-size:8.5px;font-style:italic;color:#664D03;">'
            . 'IMPORTANT: Use ONLY the information provided on this card. Do not add to, embellish, invent, or make any other changes to this information when communicating with the EOC or other survivors.'
            . '</td></tr></table><br/>';

        // Tasks
        $html .= '<p style="font-size:10px;font-weight:bold;">Tasks to complete:</p><ul>';
        foreach ( $task['student_tasks'] as $item ) {
            $html .= '<li style="font-size:10px;">' . esc_html( $item ) . '</li>';
        }
        $html .= '</ul>';

        // Info
        if ( ! empty( $task['info'] ) ) {
            $html .= '<br/><p style="font-size:10px;font-weight:bold;">Information you can provide:</p><ul>';
            foreach ( $task['info'] as $item ) {
                $html .= '<li style="font-size:10px;">' . esc_html( $item ) . '</li>';
            }
            $html .= '</ul>';
        }

        // Student reference number
        $html .= sprintf(
            '<br/><p style="text-align:right;font-size:7px;color:#CCCCCC;">[S-%02d]</p>',
            $student_num
        );

        $pdf->writeHTML( $html, true, false, true, false, '' );
    }

    /**
     * Add facilitator reference pages to a PDF.
     */
    private static function add_facilitator_pages( $pdf, $assignments, $cfg, $contradictions, $cross_refs ) {
        $html = '<h2 style="font-size:13px;">Facilitator Reference</h2>';
        $html .= sprintf(
            '<p style="font-size:9px;">%s &nbsp;|&nbsp; Class size: %d &nbsp;|&nbsp; Tasks distributed: %d</p>',
            esc_html( $cfg['location'] ),
            count( $assignments ),
            array_sum( array_map( 'count', $assignments ) )
        );

        // Priority alert
        $html .= '<table cellpadding="5" style="width:100%;">
            <tr><td style="background-color:#FF0000;color:#FFFFFF;font-weight:bold;font-size:10px;text-align:center;">
            &#9888; HIGH PRIORITY TASK: N (November) -- Medical Emergency
            </td></tr></table>';
        $html .= sprintf(
            '<p style="font-size:9px;font-style:italic;color:#B85C00;">Watch whether Net Control recognizes and prioritizes this call. EOC must route around %s / %s downed lines (Task K) to reach the hospital. The correct answer requires synthesizing intel from K and J.</p>',
            esc_html( $cfg['blocked_road'] ), esc_html( $cfg['main_road'] )
        );

        // Contradictions
        $html .= '<h2 style="font-size:13px;">Built-in Contradictions</h2>';
        foreach ( $contradictions as $c ) {
            $html .= sprintf( '<p style="font-size:9px;"><b>%s -- %s</b></p>', esc_html( $c['pair'] ), esc_html( $c['topic'] ) );
            foreach ( explode( "\n", $c['detail'] ) as $line ) {
                $html .= sprintf( '<p style="font-size:9px;font-style:italic;color:#B85C00;">%s</p>', esc_html( $line ) );
            }
        }

        // Cross-references
        $html .= '<h2 style="font-size:13px;">Cross-References</h2>';
        foreach ( $cross_refs as $cr ) {
            $html .= sprintf( '<p style="font-size:9px;"><b>%s</b> -- %s</p>', esc_html( $cr['ref'] ), esc_html( $cr['desc'] ) );
        }

        // Assignment table
        $html .= '<h2 style="font-size:13px;">Student Assignments</h2>';
        $html .= '<table border="0.5" cellpadding="3" style="font-size:7.5px;">
            <tr style="background-color:#222222;color:#FFFFFF;font-weight:bold;">
                <td width="5%">#</td>
                <td width="8%">Code</td>
                <td width="30%">Task(s)</td>
                <td width="57%">Notes</td>
            </tr>';

        foreach ( $assignments as $i => $student_tasks ) {
            $num  = $i + 1;
            $code = sprintf( 'S-%02d', $num );
            $bg   = ( $i % 2 === 0 ) ? '#FFFFFF' : '#F4F4F4';

            $task_str = implode( ', ', array_map( function( $t ) {
                $flag = $t['priority'] ? " \xE2\x9A\xA0" : '';
                return sprintf( '%s (%s)%s', $t['letter'], $t['phonetic'], $flag );
            }, $student_tasks ) );

            $notes = implode( ' | ', array_filter( array_map( function( $t ) {
                return $t['fac_notes'];
            }, $student_tasks ) ) );

            $html .= sprintf(
                '<tr style="background-color:%s;">
                    <td width="5%%">%d</td>
                    <td width="8%%">%s</td>
                    <td width="30%%">%s</td>
                    <td width="57%%" style="font-size:6.5px;font-style:italic;color:#555555;">%s</td>
                </tr>',
                $bg, $num, $code, esc_html( $task_str ), esc_html( $notes )
            );
        }
        $html .= '</table>';

        $pdf->writeHTML( $html, true, false, true, false, '' );
    }
}

/**
 * Custom TCPDF subclass for the copyright footer.
 */
class RME_EOC_TCPDF extends TCPDF {

    public function Footer() {
        $this->SetY( -12 );
        $this->SetFont( 'helvetica', '', 6.5 );
        $this->SetTextColor( 136, 136, 136 );
        $copyright = "\xC2\xA9 Radio Made Easy. Proprietary training material. Unauthorized reproduction, distribution, or use outside of licensed Radio Made Easy courses is strictly prohibited.";
        $this->Cell( 0, 10, $copyright, 0, false, 'C', 0, '', 0, false, 'T', 'M' );
    }
}
