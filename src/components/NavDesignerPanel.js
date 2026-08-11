import { __ } from '@wordpress/i18n';
import { InspectorControls } from '@wordpress/block-editor';
import { PanelBody, ToggleControl, TextControl } from '@wordpress/components';
import { generateId } from '../utils/generate-id';

// Background/text color live on the block's own native color attributes
// (Styles > Color) — core/navigation already has that control, so this panel
// doesn't duplicate it and there's no site-wide color default either. Only
// item padding, which has no native per-item equivalent, gets a field here.
const EMPTY_DESKTOP = {
	itemPaddingY: '',
	itemPaddingX: '',
};

export default function NavDesignerPanel( { attributes, setAttributes } ) {
	const navDesigner = attributes.navDesigner || { enabled: false, desktop: EMPTY_DESKTOP };
	const desktop = navDesigner.desktop || EMPTY_DESKTOP;

	const updateDesktop = ( key, value ) => {
		const nextAttrs = {
			navDesigner: {
				...navDesigner,
				enabled: true,
				desktop: { ...desktop, [ key ]: value },
			},
		};
		if ( ! attributes.navDesignerId ) {
			nextAttrs.navDesignerId = generateId();
		}
		setAttributes( nextAttrs );
	};

	const toggleEnabled = ( enabled ) => {
		const nextAttrs = { navDesigner: { ...navDesigner, enabled } };
		if ( enabled && ! attributes.navDesignerId ) {
			nextAttrs.navDesignerId = generateId();
		}
		setAttributes( nextAttrs );
	};

	const reset = () => {
		setAttributes( { navDesigner: { enabled: false, desktop: EMPTY_DESKTOP } } );
	};

	return (
		<InspectorControls>
			<PanelBody title={ __( 'Navigation Designer', 'navigation-designer' ) } initialOpen={ false }>
				<ToggleControl
					label={ __( 'Custom item padding for this navigation', 'navigation-designer' ) }
					checked={ !! navDesigner.enabled }
					onChange={ toggleEnabled }
				/>

				{ navDesigner.enabled && (
					<>
						<TextControl
							label={ __( 'Item padding (top/bottom)', 'navigation-designer' ) }
							placeholder={ __( 'e.g. 0.5em', 'navigation-designer' ) }
							value={ desktop.itemPaddingY }
							onChange={ ( value ) => updateDesktop( 'itemPaddingY', value ) }
						/>

						<TextControl
							label={ __( 'Item padding (left/right)', 'navigation-designer' ) }
							placeholder={ __( 'e.g. 1em', 'navigation-designer' ) }
							value={ desktop.itemPaddingX }
							onChange={ ( value ) => updateDesktop( 'itemPaddingX', value ) }
						/>

						<button type="button" className="components-button is-tertiary is-destructive" onClick={ reset }>
							{ __( 'Remove custom padding', 'navigation-designer' ) }
						</button>
					</>
				) }
			</PanelBody>
		</InspectorControls>
	);
}
