/**
 * Mirrors includes/class-nsc-css-generator.php. Scoped by the block's own
 * editor DOM id (`block-<clientId>`) instead of `data-nsc-id`.
 */
import { BREAKPOINTS, MOBILE_BREAKPOINT } from '../schema';

// Injection-safe but permissive: allows what real CSS values need (hex
// colors, units, decimals, percentages, function calls like rgba()/var(),
// box-shadow lists) while stripping ";", "{", "}".
function sanitizeCssValue( value ) {
	return String( value ).replace( /[^#a-zA-Z0-9(),.%\s-]/g, '' );
}

/** field => [ selectorKey, cssProperty|[cssProperties] ] */
function fieldMap( group ) {
	switch ( group ) {
		case 'nav':
			return {
				borderWidth: [ 'wrapper', 'border-width' ],
				borderStyle: [ 'wrapper', 'border-style' ],
				borderColor: [ 'wrapper', 'border-color' ],
				radius: [ 'wrapper', 'border-radius' ],
				shadow: [ 'wrapper', 'box-shadow' ],
				gap: [ 'container', 'gap' ],
				itemPaddingY: [ 'item', [ 'padding-top', 'padding-bottom' ] ],
				itemPaddingX: [ 'item', [ 'padding-left', 'padding-right' ] ],
				itemHoverColor: [ 'itemHover', 'color' ],
				itemHoverBackground: [ 'itemHover', 'background-color' ],
				itemFocusColor: [ 'itemFocus', 'color' ],
				itemFocusBackground: [ 'itemFocus', 'background-color' ],
				fontSize: [ 'item', 'font-size' ],
				fontWeight: [ 'item', 'font-weight' ],
				lineHeight: [ 'item', 'line-height' ],
				letterSpacing: [ 'item', 'letter-spacing' ],
				textTransform: [ 'item', 'text-transform' ],
			};
		case 'submenu':
			return {
				background: [ 'submenu', 'background-color' ],
				text: [ 'submenu', 'color' ],
				borderWidth: [ 'submenu', 'border-width' ],
				borderStyle: [ 'submenu', 'border-style' ],
				borderColor: [ 'submenu', 'border-color' ],
				radius: [ 'submenu', 'border-radius' ],
				shadow: [ 'submenu', 'box-shadow' ],
				paddingY: [ 'submenu', [ 'padding-top', 'padding-bottom' ] ],
				paddingX: [ 'submenu', [ 'padding-left', 'padding-right' ] ],
				offset: [ 'submenu', 'margin-top' ],
				fontSize: [ 'submenu', 'font-size' ],
				fontWeight: [ 'submenu', 'font-weight' ],
				lineHeight: [ 'submenu', 'line-height' ],
				letterSpacing: [ 'submenu', 'letter-spacing' ],
				textTransform: [ 'submenu', 'text-transform' ],
			};
		case 'submenuItem':
			return {
				text: [ 'subItem', 'color' ],
				hoverText: [ 'subItemHover', 'color' ],
				hoverBackground: [ 'subItemHover', 'background-color' ],
				focusText: [ 'subItemFocus', 'color' ],
				focusBackground: [ 'subItemFocus', 'background-color' ],
				paddingY: [ 'subItem', [ 'padding-top', 'padding-bottom' ] ],
				paddingX: [ 'subItem', [ 'padding-left', 'padding-right' ] ],
				fontSize: [ 'subItem', 'font-size' ],
				fontWeight: [ 'subItem', 'font-weight' ],
				lineHeight: [ 'subItem', 'line-height' ],
				letterSpacing: [ 'subItem', 'letter-spacing' ],
				textTransform: [ 'subItem', 'text-transform' ],
			};
		default:
			return {};
	}
}

function selectors( base ) {
	const item = `${ base } > .wp-block-navigation__container > .wp-block-navigation-item > .wp-block-navigation-item__content`;
	const subItem = `${ base } .wp-block-navigation__submenu-container .wp-block-navigation-item__content`;

	return {
		wrapper: base,
		container: `${ base } > .wp-block-navigation__container`,
		item,
		itemHover: `${ item }:hover`,
		itemFocus: `${ item }:focus`,
		submenu: `${ base } .wp-block-navigation__submenu-container`,
		subItem,
		subItemHover: `${ subItem }:hover`,
		subItemFocus: `${ subItem }:focus`,
	};
}

function buildGroupRules( selectorMap, group, values ) {
	const bySelector = {};

	Object.entries( fieldMap( group ) ).forEach( ( [ field, [ selectorKey, props ] ] ) => {
		const raw = values && values[ field ] ? String( values[ field ] ).trim() : '';
		if ( ! raw ) {
			return;
		}
		const value = sanitizeCssValue( raw );
		if ( ! value ) {
			return;
		}

		if ( ! bySelector[ selectorKey ] ) {
			bySelector[ selectorKey ] = {};
		}
		( Array.isArray( props ) ? props : [ props ] ).forEach( ( prop ) => {
			bySelector[ selectorKey ][ prop ] = value;
		} );
	} );

	let css = '';
	Object.entries( bySelector ).forEach( ( [ selectorKey, declarations ] ) => {
		css += `${ selectorMap[ selectorKey ] } {\n`;
		Object.entries( declarations ).forEach( ( [ prop, value ] ) => {
			css += `\t${ prop }: ${ value } !important;\n`;
		} );
		css += '}\n';
	} );

	return css;
}

function indent( css ) {
	return css
		.replace( /\n$/, '' )
		.split( '\n' )
		.map( ( line ) => ( line ? `\t${ line }` : line ) )
		.join( '\n' ) + '\n';
}

/**
 * One instance's full scoped CSS across all groups and both breakpoints.
 * Returns '' if the instance's override isn't enabled.
 *
 * @param {string} selectorId  DOM id to scope to (no leading '#').
 * @param {Object} normalized  A normalizeInstance() result.
 */
export function buildInstanceCss( selectorId, normalized ) {
	if ( ! normalized || ! normalized.enabled ) {
		return '';
	}

	const base = `#${ selectorId }`;
	const selectorMap = selectors( base );
	let css = '';

	BREAKPOINTS.forEach( ( breakpoint ) => {
		let rules = buildGroupRules( selectorMap, 'nav', normalized.nav[ breakpoint ] );
		rules += buildGroupRules( selectorMap, 'submenu', normalized.submenu[ breakpoint ] );
		rules += buildGroupRules( selectorMap, 'submenuItem', normalized.submenuItem[ breakpoint ] );

		if ( ! rules ) {
			return;
		}

		if ( breakpoint === 'mobile' ) {
			css += `@media (max-width: ${ MOBILE_BREAKPOINT }) {\n${ indent( rules ) }}\n`;
		} else {
			css += rules;
		}
	} );

	return css;
}
