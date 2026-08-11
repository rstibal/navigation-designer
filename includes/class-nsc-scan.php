<?php
/**
 * Walks every post type that can embed a core/navigation instance
 * (reusable wp_navigation entities, template parts, templates) and collects
 * the attributes for each instance that has a navDesignerId — i.e. every
 * instance with an active item-padding override (the editor JS only stamps
 * the id when that toggle is turned on).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class NSC_Scan {

	const SCANNED_POST_TYPES = array( 'wp_navigation', 'wp_template_part', 'wp_template' );

	/**
	 * @return array List of ['id' => string, 'attrs' => array] for every
	 *               identified instance found across the site.
	 */
	public function collect_instances() {
		$instances = array();

		$posts = get_posts(
			array(
				'post_type'      => self::SCANNED_POST_TYPES,
				'post_status'    => array( 'publish', 'draft' ),
				'numberposts'    => -1,
				'suppress_filters' => false,
			)
		);

		foreach ( $posts as $post ) {
			if ( empty( $post->post_content ) ) {
				continue;
			}
			$blocks = parse_blocks( $post->post_content );
			$this->walk_blocks( $blocks, $instances );
		}

		return array_values( $instances );
	}

	private function walk_blocks( $blocks, array &$instances ) {
		foreach ( $blocks as $block ) {
			if ( 'core/navigation' === $block['blockName'] ) {
				$attrs = isset( $block['attrs'] ) ? $block['attrs'] : array();
				$id    = isset( $attrs['navDesignerId'] ) ? trim( (string) $attrs['navDesignerId'] ) : '';

				if ( '' !== $id ) {
					$instances[ $id ] = array(
						'id'    => $id,
						'attrs' => $attrs,
					);
				}
			}

			if ( ! empty( $block['innerBlocks'] ) ) {
				$this->walk_blocks( $block['innerBlocks'], $instances );
			}
		}
	}
}
