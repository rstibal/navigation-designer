/**
 * Mirrors NSC_CSS_Generator::build_instance_css() in PHP, scoped by the
 * block's own editor DOM id (`block-<clientId>`) instead of `data-nsc-id`.
 */
export function buildInstanceCss( selectorId, paddingValues ) {
	const py = paddingValues.itemPaddingY || '';
	const px = paddingValues.itemPaddingX || '';

	if ( ! py && ! px ) {
		return '';
	}

	const itemSelector = `#${ selectorId } .wp-block-navigation-item__content`;
	return `${ itemSelector } {\n\tpadding: ${ py || '0.5em' } ${ px || '1em' } !important;\n}\n`;
}
