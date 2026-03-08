<?php
/**
 * Plugin Name: RME EOC Scenario Generator
 * Description: Generate location-customized student task cards and facilitator materials for radio emergency communications (emcom) training exercises.
 * Version: 1.0.0
 * Author: Radio Made Easy
 * Text Domain: rme-eoc-scenario
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'RME_EOC_VERSION', '1.0.0' );
define( 'RME_EOC_PATH', plugin_dir_path( __FILE__ ) );
define( 'RME_EOC_URL', plugin_dir_url( __FILE__ ) );

require_once RME_EOC_PATH . 'includes/class-task-definitions.php';
require_once RME_EOC_PATH . 'includes/class-distribution.php';
require_once RME_EOC_PATH . 'includes/class-location-presets.php';
require_once RME_EOC_PATH . 'includes/class-location-lookup.php';
require_once RME_EOC_PATH . 'includes/class-pdf-generator.php';
require_once RME_EOC_PATH . 'includes/class-txt-generator.php';
require_once RME_EOC_PATH . 'includes/class-scenario-admin.php';

new RME_EOC_Scenario_Admin();
