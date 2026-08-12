<?php
/**
 * Plugin Name: Navigation Designer
 * Description: Adds a per-instance item-padding override for the Navigation block, applied via a generated CSS file (not inline styles, not theme.json). Color is handled entirely by the block's own native color controls.
 * Version: 2.0.1
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: injurylawyers.com
 * Text Domain: navigation-designer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'NSC_PLUGIN_FILE', __FILE__ );
define( 'NSC_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'NSC_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once NSC_PLUGIN_DIR . 'includes/class-nsc-schema.php';
require_once NSC_PLUGIN_DIR . 'includes/class-nsc-block-attrs.php';
require_once NSC_PLUGIN_DIR . 'includes/class-nsc-render.php';
require_once NSC_PLUGIN_DIR . 'includes/class-nsc-scan.php';
require_once NSC_PLUGIN_DIR . 'includes/class-nsc-css-generator.php';
require_once NSC_PLUGIN_DIR . 'includes/class-nsc-css-pipeline.php';
require_once NSC_PLUGIN_DIR . 'includes/class-nsc-plugin.php';

function nsc_activate() {
	do_action( 'nsc_activate' );
}
register_activation_hook( __FILE__, 'nsc_activate' );

new NSC_Plugin();
