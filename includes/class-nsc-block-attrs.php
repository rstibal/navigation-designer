<?php
/**
 * Registers the navDesigner/navDesignerId attributes on core/navigation so
 * per-instance overrides persist in the block's serialized comment, same as
 * any core attribute.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NSC_Block_Attrs {

	const TARGET_BLOCKS = array( 'core/navigation' );

	public function __construct() {
		add_filter( 'register_block_type_args', array( $this, 'add_attributes' ), 10, 2 );
	}

	public function add_attributes( $args, $name ) {
		if ( ! in_array( $name, self::TARGET_BLOCKS, true ) ) {
			return $args;
		}

		if ( ! isset( $args['attributes'] ) || ! is_array( $args['attributes'] ) ) {
			$args['attributes'] = array();
		}

		$args['attributes']['navDesigner'] = array(
			'type'    => 'object',
			'default' => NSC_Schema::default_instance(),
		);

		$args['attributes']['navDesignerId'] = array(
			'type'    => 'string',
			'default' => '',
		);

		return $args;
	}
}
