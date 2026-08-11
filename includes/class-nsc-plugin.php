<?php
/**
 * Coordinator: wires up all the plugin's pieces and the block-editor asset.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NSC_Plugin {

	private $css_pipeline;

	public function __construct() {
		new NSC_Block_Attrs();
		new NSC_Render();
		$this->css_pipeline = new NSC_CSS_Pipeline();

		add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
		add_action( 'nsc_activate', array( $this->css_pipeline, 'regenerate' ) );
	}

	public function enqueue_editor_assets() {
		$asset_file = NSC_PLUGIN_DIR . 'build/index.asset.php';
		if ( ! file_exists( $asset_file ) ) {
			return;
		}
		$asset = require $asset_file;

		wp_enqueue_script(
			'navigation-designer-editor',
			NSC_PLUGIN_URL . 'build/index.js',
			$asset['dependencies'],
			$asset['version'],
			true
		);
	}
}
