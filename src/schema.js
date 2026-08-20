/**
 * JS mirror of includes/class-nsc-schema.php + class-nsc-css-generator.php.
 * Keep field lists, field maps, and selector logic in sync with those files.
 */

export const BREAKPOINTS = [ 'desktop', 'mobile' ];
export const GROUPS = [ 'nav', 'submenu', 'submenuItem' ];
export const MOBILE_BREAKPOINT = '782px';

const NAV_FIELDS = [
	'borderWidth',
	'borderStyle',
	'borderColor',
	'radius',
	'shadow',
	'gap',
	'itemPaddingY',
	'itemPaddingX',
	'itemHoverColor',
	'itemHoverBackground',
	'itemFocusColor',
	'itemFocusBackground',
	'fontSize',
	'fontWeight',
	'lineHeight',
	'letterSpacing',
	'textTransform',
];

const SUBMENU_FIELDS = [
	'background',
	'text',
	'borderWidth',
	'borderStyle',
	'borderColor',
	'radius',
	'shadow',
	'paddingY',
	'paddingX',
	'offset',
	'fontSize',
	'fontWeight',
	'lineHeight',
	'letterSpacing',
	'textTransform',
];

const SUBMENU_ITEM_FIELDS = [
	'text',
	'hoverText',
	'hoverBackground',
	'focusText',
	'focusBackground',
	'paddingY',
	'paddingX',
	'fontSize',
	'fontWeight',
	'lineHeight',
	'letterSpacing',
	'textTransform',
];

export function fieldsForGroup( group ) {
	switch ( group ) {
		case 'nav':
			return NAV_FIELDS;
		case 'submenu':
			return SUBMENU_FIELDS;
		case 'submenuItem':
			return SUBMENU_ITEM_FIELDS;
		default:
			return [];
	}
}

function emptyFields( fields ) {
	return fields.reduce( ( acc, key ) => {
		acc[ key ] = '';
		return acc;
	}, {} );
}

export function defaultInstance() {
	const instance = { enabled: false };
	GROUPS.forEach( ( group ) => {
		const fields = fieldsForGroup( group );
		instance[ group ] = {
			desktop: emptyFields( fields ),
			mobile: emptyFields( fields ),
		};
	} );
	return instance;
}

/**
 * Mirrors NSC_Schema::normalize_instance(): accepts the current shape, the
 * legacy pre-tiered shape, or anything with missing/unknown keys, and
 * returns the full current shape with every field present. Doesn't gate on
 * `enabled` — the panel needs stored values back even while the toggle is
 * off; only CSS generation (buildInstanceCss) gates on that.
 */
export function normalizeInstance( raw ) {
	let source = raw && typeof raw === 'object' ? raw : {};

	if ( source.desktop && ! source.nav ) {
		const legacy = source.desktop;
		source = {
			enabled: !! source.enabled,
			nav: {
				desktop: {
					itemPaddingY: legacy.itemPaddingY || '',
					itemPaddingX: legacy.itemPaddingX || '',
				},
			},
		};
	}

	const result = defaultInstance();
	result.enabled = !! source.enabled;

	GROUPS.forEach( ( group ) => {
		BREAKPOINTS.forEach( ( breakpoint ) => {
			const values = source[ group ] && source[ group ][ breakpoint ];
			if ( ! values || typeof values !== 'object' ) {
				return;
			}
			Object.keys( values ).forEach( ( key ) => {
				if ( key in result[ group ][ breakpoint ] ) {
					const value = values[ key ];
					result[ group ][ breakpoint ][ key ] =
						value === null || value === undefined ? '' : String( value );
				}
			} );
		} );
	} );

	return result;
}
