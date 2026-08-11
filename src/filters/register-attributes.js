import { addFilter } from '@wordpress/hooks';

// Background/text color are intentionally not part of this shape — see
// NavDesignerPanel.js for why. Padding has no native per-item equivalent on
// core/navigation, so it keeps its own override tier.
const DEFAULT_INSTANCE = {
	enabled: false,
	desktop: {
		itemPaddingY: '',
		itemPaddingX: '',
	},
};

function addNavDesignerAttributes( settings, name ) {
	if ( name !== 'core/navigation' ) {
		return settings;
	}

	settings.attributes = {
		...settings.attributes,
		navDesigner: {
			type: 'object',
			default: DEFAULT_INSTANCE,
		},
		navDesignerId: {
			type: 'string',
			default: '',
		},
	};

	return settings;
}

addFilter(
	'blocks.registerBlockType',
	'navigation-designer/add-attributes',
	addNavDesignerAttributes
);
