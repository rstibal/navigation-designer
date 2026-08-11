<?php
/**
 * Builds the per-instance item-padding override CSS. Color is entirely
 * handled by core/navigation's own native color support — this plugin
 * doesn't touch color at all (see class-nsc-schema.php docblock for why).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NSC_CSS_Generator {

	/**
	 * One instance's scoped item-padding override.
	 */
	public function build_instance_css( $id, array $padding_values ) {
		$py = ! empty( $padding_values['itemPaddingY'] ) ? $padding_values['itemPaddingY'] : '';
		$px = ! empty( $padding_values['itemPaddingX'] ) ? $padding_values['itemPaddingX'] : '';

		if ( ! $py && ! $px ) {
			return '';
		}

		$item_selector = '[data-nsc-id="' . esc_attr( $id ) . '"] .wp-block-navigation-item__content';
		$py            = $py ? $py : '0.5em';
		$px            = $px ? $px : '1em';

		return "{$item_selector} {\n\tpadding: {$py} {$px} !important;\n}\n";
	}
}
