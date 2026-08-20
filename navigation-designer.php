<?php
/**
 * Plugin Name: Navigation Designer
 * Description: Per-instance style controls for the Navigation block — separate color, border, spacing, and typography settings for the nav bar, its submenus, and submenu items, at both desktop and mobile breakpoints, compiled to a generated CSS file rather than inline styles or theme.json.
 * Version: 2.1.1
 * Requires at least: 6.5
 * Requires PHP: 7.4
 * Author: Rob Stibal
 * Author URI: https://robstibal.com
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
