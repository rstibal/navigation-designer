const EMPTY_PADDING = {
	itemPaddingY: '',
	itemPaddingX: '',
};

/**
 * Mirrors NSC_Schema::merge_padding() in PHP. No site-wide fallback: if the
 * override toggle is off, a field is simply unset.
 */
export function mergePadding( navDesigner ) {
	const enabled = !! ( navDesigner && navDesigner.enabled );
	const desktop = ( enabled && navDesigner.desktop ) || {};

	const merged = {};
	Object.keys( EMPTY_PADDING ).forEach( ( key ) => {
		merged[ key ] = desktop[ key ] || '';
	} );
	return merged;
}
