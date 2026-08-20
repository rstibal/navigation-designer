<?php
/**
 * Single source of truth for the Navigation Designer field schema, defaults,
 * legacy-shape migration, and native-color extraction.
 *
 * Three style groups, each with its own desktop/mobile field set:
 *  - nav:         the wrapper + top-level items (border/radius/shadow/gap on
 *                  the wrapper; hover/focus color, spacing, typography on
 *                  top-level item links). Base background/text color stays
 *                  native (Styles > Color) — core already has that control.
 *  - submenu:      the dropdown panel. Core has no native styling surface
 *                  here at all, so this group owns base background/text too.
 *  - submenuItem:  the links inside the dropdown panel.
 *
 * This is the JS-mirrored PHP half of the pattern (see src/schema.js) — keep
 * both in sync when the field lists change.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NSC_Schema {

	const BREAKPOINTS = array( 'desktop', 'mobile' );

	const GROUPS = array( 'nav', 'submenu', 'submenuItem' );

	/** @var array Field keys for the nav group (wrapper + top-level items). */
	public static function nav_fields() {
		return array(
			'borderWidth',
			'borderStyle',
			'borderColor',
			'radius',
			'shadow',
			'gap',
			'itemPaddingY',
			'itemPaddingX',
			'itemHoverColor',
			'itemHoverBackground',
			'itemFocusColor',
			'itemFocusBackground',
			'fontSize',
			'fontWeight',
			'lineHeight',
			'letterSpacing',
			'textTransform',
		);
	}

	/** @var array Field keys for the submenu (dropdown panel) group. */
	public static function submenu_fields() {
		return array(
			'background',
			'text',
			'borderWidth',
			'borderStyle',
			'borderColor',
			'radius',
			'shadow',
			'paddingY',
			'paddingX',
			'offset',
			'fontSize',
			'fontWeight',
			'lineHeight',
			'letterSpacing',
			'textTransform',
		);
	}

	/** @var array Field keys for the submenuItem (dropdown link) group. */
	public static function submenu_item_fields() {
		return array(
			'text',
			'hoverText',
			'hoverBackground',
			'focusText',
			'focusBackground',
			'paddingY',
			'paddingX',
			'fontSize',
			'fontWeight',
			'lineHeight',
			'letterSpacing',
			'textTransform',
		);
	}

	public static function fields_for_group( $group ) {
		switch ( $group ) {
			case 'nav':
				return self::nav_fields();
			case 'submenu':
				return self::submenu_fields();
			case 'submenuItem':
				return self::submenu_item_fields();
			default:
				return array();
		}
	}

	private static function empty_fields( array $fields ) {
		return array_fill_keys( $fields, '' );
	}

	public static function default_instance() {
		$instance = array( 'enabled' => false );
		foreach ( self::GROUPS as $group ) {
			$fields = self::fields_for_group( $group );
			$instance[ $group ] = array(
				'desktop' => self::empty_fields( $fields ),
				'mobile'  => self::empty_fields( $fields ),
			);
		}
		return $instance;
	}

	/**
	 * Accepts a raw navDesigner attribute value — current shape, legacy
	 * (pre-tiered) shape, or anything with missing/unknown keys — and returns
	 * the full current shape with every field present. Unknown keys are
	 * dropped; missing fields default to ''. Non-destructive: doesn't gate on
	 * `enabled`, since the editor UI needs stored values back even while the
	 * toggle is off (only CSS generation gates on `enabled`).
	 */
	public static function normalize_instance( $raw ) {
		$raw = is_array( $raw ) ? $raw : array();

		// Legacy shape: { enabled, desktop: { itemPaddingY, itemPaddingX } }
		// with no `nav` key. Migrate the two fields it had into the new
		// nav.desktop slot; everything else starts empty.
		if ( isset( $raw['desktop'] ) && ! isset( $raw['nav'] ) && is_array( $raw['desktop'] ) ) {
			$legacy = $raw['desktop'];
			$raw    = array(
				'enabled' => ! empty( $raw['enabled'] ),
				'nav'     => array(
					'desktop' => array(
						'itemPaddingY' => isset( $legacy['itemPaddingY'] ) ? $legacy['itemPaddingY'] : '',
						'itemPaddingX' => isset( $legacy['itemPaddingX'] ) ? $legacy['itemPaddingX'] : '',
					),
				),
			);
		}

		$result            = self::default_instance();
		$result['enabled'] = ! empty( $raw['enabled'] );

		foreach ( self::GROUPS as $group ) {
			foreach ( self::BREAKPOINTS as $breakpoint ) {
				if ( empty( $raw[ $group ][ $breakpoint ] ) || ! is_array( $raw[ $group ][ $breakpoint ] ) ) {
					continue;
				}
				foreach ( $raw[ $group ][ $breakpoint ] as $key => $value ) {
					if ( array_key_exists( $key, $result[ $group ][ $breakpoint ] ) ) {
						$result[ $group ][ $breakpoint ][ $key ] = is_scalar( $value ) ? (string) $value : '';
					}
				}
			}
		}

		return $result;
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

	/**
	 * Permissive but injection-safe: allows the characters real CSS values
	 * need (hex colors, units, decimals, percentages, function calls like
	 * rgba()/var(), multi-value shorthand and box-shadow lists) while
	 * stripping anything that could break out of a declaration (";", "{", "}").
	 */
	public static function sanitize_css_value( $value ) {
		return preg_replace( '/[^#a-zA-Z0-9(),.%\s-]/', '', (string) $value );
	}
}
