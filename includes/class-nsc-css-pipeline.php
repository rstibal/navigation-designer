<?php
/**
 * Orchestrates CSS regeneration: scans the site for active nav/submenu/
 * submenu-item style overrides, builds each instance's scoped rules, writes
 * them to a physical file in uploads, and enqueues that file after the
 * theme's global styles.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NSC_CSS_Pipeline {

	const REBUILD_FLAG_OPTION = 'nsc_needs_rebuild';

	private $generator;
	private $scan;

	public function __construct() {
		$this->generator = new NSC_CSS_Generator();
		$this->scan      = new NSC_Scan();

		add_action( 'save_post_wp_navigation', array( $this, 'regenerate' ) );
		add_action( 'save_post_wp_template_part', array( $this, 'regenerate' ) );
		add_action( 'save_post_wp_template', array( $this, 'regenerate' ) );

		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue' ), 20 );
		add_action( 'admin_init', array( $this, 'maybe_rebuild_flagged' ) );
	}

	private function upload_dir() {
		$upload = wp_upload_dir();
		return array(
			'path' => trailingslashit( $upload['basedir'] ) . 'navigation-designer',
			'url'  => trailingslashit( $upload['baseurl'] ) . 'navigation-designer',
		);
	}

	public function css_file_path() {
		return $this->upload_dir()['path'] . '/nav-designer.css';
	}

	private function meta_file_path() {
		return $this->upload_dir()['path'] . '/nav-designer.meta.json';
	}

	public function regenerate() {
		$dirs = $this->upload_dir();
		wp_mkdir_p( $dirs['path'] );

		$css = '';
		foreach ( $this->scan->collect_instances() as $instance ) {
			$attrs       = $instance['attrs'];
			$navdesigner = isset( $attrs['navDesigner'] ) ? $attrs['navDesigner'] : array();
			$normalized  = NSC_Schema::normalize_instance( $navdesigner );
			$css        .= $this->generator->build_instance_css( $instance['id'], $normalized );
		}

		global $wp_filesystem;
		if ( ! function_exists( 'WP_Filesystem' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
		}
		WP_Filesystem();

		$written = false;
		if ( $wp_filesystem ) {
			$written = $wp_filesystem->put_contents( $this->css_file_path(), $css, FS_CHMOD_FILE );
		}
		if ( ! $written ) {
			$written = (bool) file_put_contents( $this->css_file_path(), $css ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}

		$meta = array(
			'hash'         => md5( $css ),
			'generated_at' => time(),
		);
		if ( $wp_filesystem ) {
			$wp_filesystem->put_contents( $this->meta_file_path(), wp_json_encode( $meta ), FS_CHMOD_FILE );
		} else {
			file_put_contents( $this->meta_file_path(), wp_json_encode( $meta ) ); // phpcs:ignore WordPress.WP.AlternativeFunctions
		}

		delete_option( self::REBUILD_FLAG_OPTION );

		return $written;
	}

	public function maybe_rebuild_flagged() {
		if ( get_option( self::REBUILD_FLAG_OPTION ) ) {
			$this->regenerate();
		}
	}

	public function flag_rebuild_needed() {
		update_option( self::REBUILD_FLAG_OPTION, 1 );
	}

	public function enqueue() {
		$css_path = $this->css_file_path();
		if ( ! file_exists( $css_path ) ) {
			$this->flag_rebuild_needed();
			return;
		}

		$version = filemtime( $css_path );
		$meta_path = $this->meta_file_path();
		if ( file_exists( $meta_path ) ) {
			$meta = json_decode( (string) file_get_contents( $meta_path ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
			if ( ! empty( $meta['hash'] ) ) {
				$version = $meta['hash'];
			}
		}

		wp_enqueue_style(
			'nsc-nav-designer',
			$this->upload_dir()['url'] . '/nav-designer.css',
			array( 'global-styles' ),
			$version
		);
	}
}
