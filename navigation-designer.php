<?php
/**
 * Plugin Name: Navigation Designer
 * Description: Admin controls for spacing and color on the navigation and navigation-submenu blocks, for both desktop and mobile/responsive views. Generates a CSS override that takes precedence over the theme's core/navigation styles.
 * Version: 1.0.1
 * Author: injurylawyers.com
 * Text Domain: navigation-designer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Navigation_Designer {

	const OPTION_KEY = 'nsc_settings';
	const PAGE_SLUG  = 'navigation-designer';

	/** @var array Field key => sanitize type ('color', 'spacing', 'breakpoint'). */
	private $fields = array(
		// Desktop
		'nav_bg_color'          => 'color',
		'nav_text_color'        => 'color',
		'nav_link_hover_color'  => 'color',
		'nav_item_padding_y'    => 'spacing',
		'nav_item_padding_x'    => 'spacing',
		'nav_block_gap'         => 'spacing',
		'submenu_bg_color'      => 'color',
		'submenu_text_color'    => 'color',
		'submenu_hover_color'   => 'color',
		'submenu_item_padding_y' => 'spacing',
		'submenu_item_padding_x' => 'spacing',
		// Mobile / responsive
		'mobile_breakpoint'        => 'breakpoint',
		'mobile_overlay_bg_color'  => 'color',
		'mobile_overlay_text_color' => 'color',
		'mobile_toggle_bg_color'   => 'color',
		'mobile_toggle_text_color' => 'color',
		'mobile_item_padding_y'    => 'spacing',
		'mobile_item_padding_x'    => 'spacing',
		'mobile_submenu_indent'    => 'spacing',
		'mobile_submenu_bullet_color' => 'color',
	);

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_head', array( $this, 'output_css' ), 100 );
	}

	public function add_settings_page() {
		add_theme_page(
			__( 'Navigation Designer', 'navigation-designer' ),
			__( 'Navigation Designer', 'navigation-designer' ),
			'edit_theme_options',
			self::PAGE_SLUG,
			array( $this, 'render_settings_page' )
		);
	}

	public function register_settings() {
		register_setting( self::PAGE_SLUG, self::OPTION_KEY, array( $this, 'sanitize_settings' ) );
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'appearance_page_' . self::PAGE_SLUG !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wp-color-picker' );
		wp_enqueue_script( 'wp-color-picker' );
		wp_add_inline_script(
			'wp-color-picker',
			'jQuery(function($){ $(".nsc-color-field").wpColorPicker(); });'
		);
	}

	private function get_settings() {
		$defaults = array(
			'nav_bg_color'             => '',
			'nav_text_color'           => '',
			'nav_link_hover_color'     => '',
			'nav_item_padding_y'       => '',
			'nav_item_padding_x'       => '',
			'nav_block_gap'            => '',
			'submenu_bg_color'         => '',
			'submenu_text_color'       => '',
			'submenu_hover_color'      => '',
			'submenu_item_padding_y'   => '',
			'submenu_item_padding_x'   => '',
			'mobile_breakpoint'        => '782',
			'mobile_overlay_bg_color'  => '',
			'mobile_overlay_text_color' => '',
			'mobile_toggle_bg_color'   => '',
			'mobile_toggle_text_color' => '',
			'mobile_item_padding_y'    => '',
			'mobile_item_padding_x'    => '',
			'mobile_submenu_indent'    => '',
			'mobile_submenu_bullet_color' => '',
		);
		return wp_parse_args( get_option( self::OPTION_KEY, array() ), $defaults );
	}

	public function sanitize_settings( $input ) {
		$output = array();
		foreach ( $this->fields as $key => $type ) {
			$raw = isset( $input[ $key ] ) ? trim( wp_unslash( $input[ $key ] ) ) : '';

			if ( '' === $raw ) {
				$output[ $key ] = '';
				continue;
			}

			switch ( $type ) {
				case 'color':
					$sanitized = sanitize_hex_color( $raw );
					$output[ $key ] = $sanitized ? $sanitized : '';
					break;

				case 'spacing':
					// Allow values like 1em, 0.5rem, 16px, -2rem.
					$output[ $key ] = preg_match( '/^-?\d+(\.\d+)?(px|em|rem|%)$/', $raw ) ? $raw : '';
					break;

				case 'breakpoint':
					$output[ $key ] = preg_match( '/^\d+$/', $raw ) ? $raw : '782';
					break;

				default:
					$output[ $key ] = sanitize_text_field( $raw );
			}
		}
		return $output;
	}

	public function render_settings_page() {
		if ( ! current_user_can( 'edit_theme_options' ) ) {
			return;
		}
		$s = $this->get_settings();
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Navigation Designer', 'navigation-designer' ); ?></h1>
			<p><?php esc_html_e( 'Override spacing and colors for the navigation and navigation-submenu blocks. Leave a field blank to fall back to the theme default.', 'navigation-designer' ); ?></p>

			<form method="post" action="options.php">
				<?php settings_fields( self::PAGE_SLUG ); ?>

				<h2><?php esc_html_e( 'Desktop', 'navigation-designer' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php
					$this->color_row( 'nav_bg_color', __( 'Navigation background color', 'navigation-designer' ), $s );
					$this->color_row( 'nav_text_color', __( 'Navigation text color', 'navigation-designer' ), $s );
					$this->color_row( 'nav_link_hover_color', __( 'Link hover/focus color', 'navigation-designer' ), $s );
					$this->spacing_row( 'nav_item_padding_y', __( 'Nav item padding (top/bottom)', 'navigation-designer' ), $s );
					$this->spacing_row( 'nav_item_padding_x', __( 'Nav item padding (left/right)', 'navigation-designer' ), $s );
					$this->spacing_row( 'nav_block_gap', __( 'Block gap between nav items', 'navigation-designer' ), $s );
					$this->color_row( 'submenu_bg_color', __( 'Submenu background color', 'navigation-designer' ), $s );
					$this->color_row( 'submenu_text_color', __( 'Submenu text color', 'navigation-designer' ), $s );
					$this->color_row( 'submenu_hover_color', __( 'Submenu link hover/focus color', 'navigation-designer' ), $s );
					$this->spacing_row( 'submenu_item_padding_y', __( 'Submenu item padding (top/bottom)', 'navigation-designer' ), $s );
					$this->spacing_row( 'submenu_item_padding_x', __( 'Submenu item padding (left/right)', 'navigation-designer' ), $s );
					?>
				</table>

				<h2><?php esc_html_e( 'Mobile / Responsive', 'navigation-designer' ); ?></h2>
				<table class="form-table" role="presentation">
					<?php $this->text_row( 'mobile_breakpoint', __( 'Breakpoint (px, max-width)', 'navigation-designer' ), $s ); ?>
					<?php
					$this->color_row( 'mobile_overlay_bg_color', __( 'Mobile menu overlay background', 'navigation-designer' ), $s );
					$this->color_row( 'mobile_overlay_text_color', __( 'Mobile menu overlay text color', 'navigation-designer' ), $s );
					$this->color_row( 'mobile_toggle_bg_color', __( 'Menu toggle button background', 'navigation-designer' ), $s );
					$this->color_row( 'mobile_toggle_text_color', __( 'Menu toggle button text/icon color', 'navigation-designer' ), $s );
					$this->spacing_row( 'mobile_item_padding_y', __( 'Mobile nav item padding (top/bottom)', 'navigation-designer' ), $s );
					$this->spacing_row( 'mobile_item_padding_x', __( 'Mobile nav item padding (left/right)', 'navigation-designer' ), $s );
					$this->spacing_row( 'mobile_submenu_indent', __( 'Mobile submenu indent (margin-left)', 'navigation-designer' ), $s );
					$this->color_row( 'mobile_submenu_bullet_color', __( 'Mobile submenu bullet color', 'navigation-designer' ), $s );
					?>
				</table>

				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	private function color_row( $key, $label, $s ) {
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input
					type="text"
					id="<?php echo esc_attr( $key ); ?>"
					name="<?php echo esc_attr( self::OPTION_KEY . '[' . $key . ']' ); ?>"
					value="<?php echo esc_attr( $s[ $key ] ); ?>"
					class="nsc-color-field"
					data-default-color=""
				/>
			</td>
		</tr>
		<?php
	}

	private function spacing_row( $key, $label, $s ) {
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input
					type="text"
					id="<?php echo esc_attr( $key ); ?>"
					name="<?php echo esc_attr( self::OPTION_KEY . '[' . $key . ']' ); ?>"
					value="<?php echo esc_attr( $s[ $key ] ); ?>"
					class="regular-text"
					placeholder="<?php esc_attr_e( 'e.g. 1em, 16px, 0.5rem', 'navigation-designer' ); ?>"
				/>
			</td>
		</tr>
		<?php
	}

	private function text_row( $key, $label, $s ) {
		?>
		<tr>
			<th scope="row"><label for="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $label ); ?></label></th>
			<td>
				<input
					type="text"
					id="<?php echo esc_attr( $key ); ?>"
					name="<?php echo esc_attr( self::OPTION_KEY . '[' . $key . ']' ); ?>"
					value="<?php echo esc_attr( $s[ $key ] ); ?>"
					class="small-text"
				/>
			</td>
		</tr>
		<?php
	}

	/**
	 * Build the override CSS from saved settings and print it in <head>,
	 * after the theme's own styles so these rules win the cascade.
	 */
	public function output_css() {
		$css = $this->build_css();
		if ( '' === trim( $css ) ) {
			return;
		}
		echo "\n<style id=\"nsc-nav-style-overrides\">\n" . $css . "\n</style>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}

	private function build_css() {
		$s   = $this->get_settings();
		$css = '';

		// --- Desktop: core/navigation ---
		$nav_rules = array();
		if ( $s['nav_bg_color'] ) {
			$nav_rules[] = "background-color: {$s['nav_bg_color']} !important;";
		}
		if ( $s['nav_text_color'] ) {
			$nav_rules[] = "color: {$s['nav_text_color']} !important;";
		}
		if ( $s['nav_block_gap'] ) {
			$nav_rules[] = "gap: {$s['nav_block_gap']} !important;";
		}
		if ( $nav_rules ) {
			$css .= ".wp-block-navigation {\n\t" . implode( "\n\t", $nav_rules ) . "\n}\n";
		}

		$item_rules = array();
		if ( $s['nav_text_color'] ) {
			$item_rules[] = "color: {$s['nav_text_color']} !important;";
		}
		if ( $s['nav_item_padding_y'] || $s['nav_item_padding_x'] ) {
			$py = $s['nav_item_padding_y'] ? $s['nav_item_padding_y'] : '0.5em';
			$px = $s['nav_item_padding_x'] ? $s['nav_item_padding_x'] : '1em';
			$item_rules[] = "padding: {$py} {$px} !important;";
		}
		if ( $item_rules ) {
			$css .= ".wp-block-navigation-item__content {\n\t" . implode( "\n\t", $item_rules ) . "\n}\n";
		}

		if ( $s['nav_link_hover_color'] ) {
			$css .= ".wp-block-navigation-item__content:hover,\n.wp-block-navigation-item__content:focus {\n\tcolor: {$s['nav_link_hover_color']} !important;\n}\n";
		}

		// --- Desktop: core/navigation-submenu ---
		$submenu_container_rules = array();
		if ( $s['submenu_bg_color'] ) {
			$submenu_container_rules[] = "background-color: {$s['submenu_bg_color']} !important;";
		}
		if ( $submenu_container_rules ) {
			$css .= ".wp-block-navigation__submenu-container {\n\t" . implode( "\n\t", $submenu_container_rules ) . "\n}\n";
		}

		$submenu_rules = array();
		if ( $s['submenu_bg_color'] ) {
			$submenu_rules[] = "background-color: {$s['submenu_bg_color']} !important;";
		}
		if ( $s['submenu_text_color'] ) {
			$submenu_rules[] = "color: {$s['submenu_text_color']} !important;";
		}
		if ( $submenu_rules ) {
			$css .= ".wp-block-navigation-submenu {\n\t" . implode( "\n\t", $submenu_rules ) . "\n}\n";
		}

		$submenu_item_rules = array();
		if ( $s['submenu_text_color'] ) {
			$submenu_item_rules[] = "color: {$s['submenu_text_color']} !important;";
		}
		if ( $s['submenu_item_padding_y'] || $s['submenu_item_padding_x'] ) {
			$py = $s['submenu_item_padding_y'] ? $s['submenu_item_padding_y'] : '0.5em';
			$px = $s['submenu_item_padding_x'] ? $s['submenu_item_padding_x'] : '1em';
			$submenu_item_rules[] = "padding: {$py} {$px} !important;";
		}
		if ( $submenu_item_rules ) {
			$css .= ".wp-block-navigation__submenu-container .wp-block-navigation-item__content {\n\t" . implode( "\n\t", $submenu_item_rules ) . "\n}\n";
		}

		if ( $s['submenu_hover_color'] ) {
			$css .= ".wp-block-navigation__submenu-container .wp-block-navigation-item__content:hover,\n.wp-block-navigation__submenu-container .wp-block-navigation-item__content:focus {\n\tcolor: {$s['submenu_hover_color']} !important;\n}\n";
		}

		// --- Mobile / responsive ---
		$breakpoint = $s['mobile_breakpoint'] ? $s['mobile_breakpoint'] : '782';
		$mobile_css = '';

		// Overlay menu toggle button.
		$toggle_rules = array();
		if ( $s['mobile_toggle_bg_color'] ) {
			$toggle_rules[] = "background-color: {$s['mobile_toggle_bg_color']} !important;";
		}
		if ( $s['mobile_toggle_text_color'] ) {
			$toggle_rules[] = "color: {$s['mobile_toggle_text_color']} !important;";
		}
		if ( $toggle_rules ) {
			$mobile_css .= ".wp-block-navigation__responsive-container-open {\n\t" . implode( "\n\t", $toggle_rules ) . "\n}\n";
		}

		// Open overlay container.
		$overlay_rules = array();
		if ( $s['mobile_overlay_bg_color'] ) {
			$overlay_rules[] = "background-color: {$s['mobile_overlay_bg_color']} !important;";
		}
		if ( $overlay_rules ) {
			$mobile_css .= ".wp-block-navigation__responsive-container.is-menu-open {\n\t" . implode( "\n\t", $overlay_rules ) . "\n}\n";
		}

		if ( $s['mobile_toggle_bg_color'] || $s['mobile_toggle_text_color'] ) {
			$close_rules = array();
			if ( $s['mobile_toggle_bg_color'] ) {
				$close_rules[] = "background-color: {$s['mobile_toggle_bg_color']} !important;";
			}
			if ( $s['mobile_toggle_text_color'] ) {
				$close_rules[] = "color: {$s['mobile_toggle_text_color']} !important;";
			}
			$mobile_css .= ".wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation__responsive-container-close {\n\t" . implode( "\n\t", $close_rules ) . "\n}\n";
		}

		$overlay_item_rules = array();
		if ( $s['mobile_overlay_text_color'] ) {
			$overlay_item_rules[] = "color: {$s['mobile_overlay_text_color']} !important;";
		}
		if ( $s['mobile_item_padding_y'] || $s['mobile_item_padding_x'] ) {
			$py = $s['mobile_item_padding_y'] ? $s['mobile_item_padding_y'] : '0.5em';
			$px = $s['mobile_item_padding_x'] ? $s['mobile_item_padding_x'] : '1em';
			$overlay_item_rules[] = "padding: {$py} {$px} !important;";
		}
		if ( $overlay_item_rules ) {
			$mobile_css .= ".wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation-item__content {\n\t" . implode( "\n\t", $overlay_item_rules ) . "\n}\n";
		}

		if ( $s['mobile_submenu_indent'] ) {
			$mobile_css .= ".wp-block-navigation__responsive-container.is-menu-open .has-child .wp-block-navigation__submenu-container {\n\tmargin-left: {$s['mobile_submenu_indent']} !important;\n}\n";
		}

		if ( $s['mobile_submenu_bullet_color'] ) {
			$mobile_css .= ".wp-block-navigation__responsive-container.is-menu-open .wp-block-navigation-submenu .wp-block-navigation-item__content::before {\n\tcolor: {$s['mobile_submenu_bullet_color']} !important;\n}\n";
		}

		if ( $mobile_css ) {
			$css .= "@media (max-width: {$breakpoint}px) {\n" . $mobile_css . "}\n";
		}

		return $css;
	}
}

new Navigation_Designer();
