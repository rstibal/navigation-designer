import { useMemo } from '@wordpress/element';
import { normalizeInstance } from '../schema';
import { buildInstanceCss } from '../utils/build-css';

/**
 * Renders a <style> tag scoped to this block's editor DOM id so nav/submenu/
 * submenu-item overrides are visible immediately, without waiting for a
 * save/regenerate round trip.
 */
export default function LivePreviewStyle( { clientId, navDesigner } ) {
	const css = useMemo( () => {
		const normalized = normalizeInstance( navDesigner );
		return buildInstanceCss( `block-${ clientId }`, normalized );
	}, [ clientId, navDesigner ] );

	if ( ! css ) {
		return null;
	}

	return <style>{ css }</style>;
}
