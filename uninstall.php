<?php
/**
 * Fires on plugin deletion (not deactivation). Removes the stored settings
 * and the generated CSS directory.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

delete_option( 'nsc_settings' );
delete_option( 'nsc_needs_rebuild' );

$upload = wp_upload_dir();
$dir    = trailingslashit( $upload['basedir'] ) . 'navigation-designer';

if ( is_dir( $dir ) ) {
	require_once ABSPATH . 'wp-admin/includes/file.php';
	WP_Filesystem();
	global $wp_filesystem;
	if ( $wp_filesystem ) {
		$wp_filesystem->delete( $dir, true );
	}
}
