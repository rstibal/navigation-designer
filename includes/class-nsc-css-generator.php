<?php
/**
 * Builds each instance's scoped nav/submenu/submenuItem style-override CSS
 * from a normalized NSC_Schema instance. Table-driven: each field maps to a
 * selector key and one or more CSS properties; selectors are resolved once
 * per instance ID and reused across fields that target the same element.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NSC_CSS_Generator {

	const MOBILE_BREAKPOINT = '782px';

	/** field => [ selectorKey, cssProperty|[cssProperties] ] */
	private static function field_map( $group ) {
		switch ( $group ) {
			case 'nav':
				return array(
					'borderWidth'         => array( 'wrapper', 'border-width' ),
					'borderStyle'         => array( 'wrapper', 'border-style' ),
					'borderColor'         => array( 'wrapper', 'border-color' ),
					'radius'              => array( 'wrapper', 'border-radius' ),
					'shadow'              => array( 'wrapper', 'box-shadow' ),
					'gap'                 => array( 'container', 'gap' ),
					'itemPaddingY'        => array( 'item', array( 'padding-top', 'padding-bottom' ) ),
					'itemPaddingX'        => array( 'item', array( 'padding-left', 'padding-right' ) ),
					'itemHoverColor'      => array( 'itemHover', 'color' ),
					'itemHoverBackground' => array( 'itemHover', 'background-color' ),
					'itemFocusColor'      => array( 'itemFocus', 'color' ),
					'itemFocusBackground' => array( 'itemFocus', 'background-color' ),
					'fontSize'            => array( 'item', 'font-size' ),
					'fontWeight'          => array( 'item', 'font-weight' ),
					'lineHeight'          => array( 'item', 'line-height' ),
					'letterSpacing'       => array( 'item', 'letter-spacing' ),
					'textTransform'       => array( 'item', 'text-transform' ),
				);
			case 'submenu':
				return array(
					'background'    => array( 'submenu', 'background-color' ),
					'text'          => array( 'submenu', 'color' ),
					'borderWidth'   => array( 'submenu', 'border-width' ),
					'borderStyle'   => array( 'submenu', 'border-style' ),
					'borderColor'   => array( 'submenu', 'border-color' ),
					'radius'        => array( 'submenu', 'border-radius' ),
					'shadow'        => array( 'submenu', 'box-shadow' ),
					'paddingY'      => array( 'submenu', array( 'padding-top', 'padding-bottom' ) ),
					'paddingX'      => array( 'submenu', array( 'padding-left', 'padding-right' ) ),
					'offset'        => array( 'submenu', 'margin-top' ),
					'fontSize'      => array( 'submenu', 'font-size' ),
					'fontWeight'    => array( 'submenu', 'font-weight' ),
					'lineHeight'    => array( 'submenu', 'line-height' ),
					'letterSpacing' => array( 'submenu', 'letter-spacing' ),
					'textTransform' => array( 'submenu', 'text-transform' ),
				);
			case 'submenuItem':
				return array(
					'text'             => array( 'subItem', 'color' ),
					'hoverText'        => array( 'subItemHover', 'color' ),
					'hoverBackground'  => array( 'subItemHover', 'background-color' ),
					'focusText'        => array( 'subItemFocus', 'color' ),
					'focusBackground'  => array( 'subItemFocus', 'background-color' ),
					'paddingY'         => array( 'subItem', array( 'padding-top', 'padding-bottom' ) ),
					'paddingX'         => array( 'subItem', array( 'padding-left', 'padding-right' ) ),
					'fontSize'         => array( 'subItem', 'font-size' ),
					'fontWeight'       => array( 'subItem', 'font-weight' ),
					'lineHeight'       => array( 'subItem', 'line-height' ),
					'letterSpacing'    => array( 'subItem', 'letter-spacing' ),
					'textTransform'    => array( 'subItem', 'text-transform' ),
				);
			default:
				return array();
		}
	}

	/**
	 * Resolves every selector key used by field_map() against one instance's
	 * root selector.
	 */
	private static function selectors( $base ) {
		$item    = "{$base} > .wp-block-navigation__container > .wp-block-navigation-item > .wp-block-navigation-item__content";
		$subitem = "{$base} .wp-block-navigation__submenu-container .wp-block-navigation-item__content";

		return array(
			'wrapper'      => $base,
			'container'    => "{$base} > .wp-block-navigation__container",
			'item'         => $item,
			'itemHover'    => "{$item}:hover",
			'itemFocus'    => "{$item}:focus",
			'submenu'      => "{$base} .wp-block-navigation__submenu-container",
			'subItem'      => $subitem,
			'subItemHover' => "{$subitem}:hover",
			'subItemFocus' => "{$subitem}:focus",
		);
	}

	/**
	 * Builds the rule block for one group at one breakpoint, grouping
	 * declarations by selector so each selector emits a single rule.
	 */
	private static function build_group_rules( array $selectors, $group, array $values ) {
		$by_selector = array();

		foreach ( self::field_map( $group ) as $field => $mapping ) {
			list( $selector_key, $props ) = $mapping;

			$value = isset( $values[ $field ] ) ? trim( (string) $values[ $field ] ) : '';
			if ( '' === $value ) {
				continue;
			}
			$value = NSC_Schema::sanitize_css_value( $value );
			if ( '' === $value ) {
				continue;
			}

			foreach ( (array) $props as $prop ) {
				$by_selector[ $selector_key ][ $prop ] = $value;
			}
		}

		$css = '';
		foreach ( $by_selector as $selector_key => $declarations ) {
			$css .= "{$selectors[ $selector_key ]} {\n";
			foreach ( $declarations as $prop => $value ) {
				$css .= "\t{$prop}: {$value} !important;\n";
			}
			$css .= "}\n";
		}

		return $css;
	}

	private static function indent( $css ) {
		$lines = explode( "\n", rtrim( $css, "\n" ) );
		$out   = array_map(
			function ( $line ) {
				return '' === $line ? $line : "\t{$line}";
			},
			$lines
		);
		return implode( "\n", $out ) . "\n";
	}

	/**
	 * One instance's full scoped CSS across all groups and both breakpoints.
	 * Returns '' if the instance's override isn't enabled.
	 *
	 * @param string $id         The instance's data-nsc-id value.
	 * @param array  $normalized An NSC_Schema::normalize_instance() result.
	 */
	public function build_instance_css( $id, array $normalized ) {
		if ( empty( $normalized['enabled'] ) ) {
			return '';
		}

		$base      = '[data-nsc-id="' . esc_attr( $id ) . '"]';
		$selectors = self::selectors( $base );
		$css       = '';

		foreach ( NSC_Schema::BREAKPOINTS as $breakpoint ) {
			$rules  = self::build_group_rules( $selectors, 'nav', $normalized['nav'][ $breakpoint ] );
			$rules .= self::build_group_rules( $selectors, 'submenu', $normalized['submenu'][ $breakpoint ] );
			$rules .= self::build_group_rules( $selectors, 'submenuItem', $normalized['submenuItem'][ $breakpoint ] );

			if ( '' === $rules ) {
				continue;
			}

			if ( 'mobile' === $breakpoint ) {
				$css .= '@media (max-width: ' . self::MOBILE_BREAKPOINT . ") {\n" . self::indent( $rules ) . "}\n";
			} else {
				$css .= $rules;
			}
		}

		return $css;
	}
}
