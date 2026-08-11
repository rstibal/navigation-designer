import { useMemo } from '@wordpress/element';
import { mergePadding } from '../utils/merge-padding';
import { buildInstanceCss } from '../utils/build-css';

/**
 * Renders a <style> tag scoped to this block's editor DOM id so the item
 * padding override is visible immediately, without waiting for a
 * save/regenerate round trip.
 */
export default function LivePreviewStyle( { clientId, navDesigner } ) {
	const css = useMemo( () => {
		const padding = mergePadding( navDesigner );
		return buildInstanceCss( `block-${ clientId }`, padding );
	}, [ clientId, navDesigner ] );

	if ( ! css ) {
		return null;
	}

	return <style>{ css }</style>;
}
