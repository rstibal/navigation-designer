import { addFilter } from '@wordpress/hooks';
import { defaultInstance } from '../schema';

function addNavDesignerAttributes( settings, name ) {
	if ( name !== 'core/navigation' ) {
		return settings;
	}

	settings.attributes = {
		...settings.attributes,
		navDesigner: {
			type: 'object',
			default: defaultInstance(),
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
