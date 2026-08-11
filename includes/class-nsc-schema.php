<?php
/**
 * Single source of truth for the Navigation Designer field schema, defaults,
 * and native-color extraction.
 *
 * Phase 1 scope: desktop-tier fields only on core/navigation. Tablet/mobile
 * tiers and core/navigation-submenu are added in Phase 2 without changing
 * this shape's contract.
 *
 * Background/text color are intentionally NOT part of this schema at all:
 * core/navigation already has native per-instance color controls
 * (Styles > Color), so a second custom control for the same value would just
 * be a competing, duplicate UI. There is also no site-wide color default —
 * that turned out to be an invisible, hard-to-debug override sitting behind
 * every nav with no clear way to reconcile it against a block's own native
 * color choice. Color is entirely native-attribute-driven; see
 * extract_native_color(). Item padding has no native per-item equivalent, so
 * it keeps its own instance-level override field with no site-wide fallback.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NSC_Schema {

	/** @var array Per-instance override field key => sanitize type. Padding only; see class docblock. */
	public static function instance_fields() {
		return array(
			'itemPaddingY' => 'spacing',
			'itemPaddingX' => 'spacing',
		);
	}

	public static function default_instance() {
		$tier = array();
		foreach ( self::instance_fields() as $key => $type ) {
			$tier[ $key ] = '';
		}
		return array(
			'enabled' => false,
			'desktop' => $tier,
		);
	}

	/**
	 * Resolves an instance's item-padding override. No site-wide fallback:
	 * if the toggle is off or a field is blank, that field is simply unset.
	 *
	 * @return array Padding values, possibly containing '' for unset fields.
	 */
	public static function merge_padding( $instance_navdesigner ) {
		$enabled = ! empty( $instance_navdesigner['enabled'] );
		$desktop = ( $enabled && isset( $instance_navdesigner['desktop'] ) ) ? $instance_navdesigner['desktop'] : array();

		$merged = array();
		foreach ( self::instance_fields() as $key => $type ) {
			$merged[ $key ] = isset( $desktop[ $key ] ) ? $desktop[ $key ] : '';
		}
		return $merged;
	}

	/**
	 * Reads whatever background/text color core/navigation's own native color
	 * support already put on the block — a custom color lives at
	 * style.color.{background,text}; a theme palette pick lives in the
	 * backgroundColor/textColor slug attributes instead, which resolves to
	 * the same CSS custom property WordPress's own preset classes use.
	 * Returns '' for a field the block hasn't set.
	 */
	public static function extract_native_color( array $attrs ) {
		$background = '';
		if ( ! empty( $attrs['style']['color']['background'] ) ) {
			$background = self::sanitize_css_value( $attrs['style']['color']['background'] );
		} elseif ( ! empty( $attrs['backgroundColor'] ) ) {
			$background = 'var(--wp--preset--color--' . sanitize_html_class( $attrs['backgroundColor'] ) . ')';
		}

		$text = '';
		if ( ! empty( $attrs['style']['color']['text'] ) ) {
			$text = self::sanitize_css_value( $attrs['style']['color']['text'] );
		} elseif ( ! empty( $attrs['textColor'] ) ) {
			$text = 'var(--wp--preset--color--' . sanitize_html_class( $attrs['textColor'] ) . ')';
		}

		return array(
			'background' => $background,
			'text'       => $text,
		);
	}

	private static function sanitize_css_value( $value ) {
		return preg_replace( '/[^#a-zA-Z0-9(),.%\s-]/', '', (string) $value );
	}
}
