<?php
/**
 * Injects a stable data-nsc-id attribute into the rendered wrapper of any
 * core/navigation instance that has an active item-padding override, so the
 * CSS generator can target it with a scoped selector. Additive only — no
 * markup restructuring.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NSC_Render {

	public function __construct() {
		add_filter( 'render_block', array( $this, 'inject_instance_id' ), 10, 2 );
	}

	public function inject_instance_id( $block_content, $block ) {
		if ( empty( $block['blockName'] ) || 'core/navigation' !== $block['blockName'] ) {
			return $block_content;
		}

		$attrs  = isset( $block['attrs'] ) ? $block['attrs'] : array();
		$nav_id = isset( $attrs['navDesignerId'] ) ? trim( (string) $attrs['navDesignerId'] ) : '';

		if ( '' === $nav_id ) {
			return $block_content;
		}

		if ( ! class_exists( 'WP_HTML_Tag_Processor' ) ) {
			return $block_content;
		}

		$processor = new WP_HTML_Tag_Processor( $block_content );
		if ( $processor->next_tag() ) {
			$processor->set_attribute( 'data-nsc-id', $nav_id );
			return $processor->get_updated_html();
		}

		return $block_content;
	}
}
