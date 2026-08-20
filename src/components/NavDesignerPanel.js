import { __ } from '@wordpress/i18n';
import { useState } from '@wordpress/element';
import { InspectorControls } from '@wordpress/block-editor';
// Deliberately limited to the handful of @wordpress/components exports the
// pre-rewrite version of this plugin already proved safe on every supported
// WP version (PanelBody, ToggleControl, TextControl, InspectorControls).
// Selects and the breakpoint switcher use plain native elements instead of
// SelectControl/ButtonGroup/Button — those newer additions were the
// suspected cause of a "React error #130 (element type is invalid)" crash
// on at least one real install, and a native <select>/<button> can never be
// undefined the way a package export can be on a mismatched WP version.
import { PanelBody, ToggleControl, TextControl } from '@wordpress/components';
import { generateId } from '../utils/generate-id';
import { defaultInstance, normalizeInstance } from '../schema';

// Free-text CSS-value fields, consistent with the plugin's existing
// philosophy (e.g. today's item-padding fields): no color picker or unit
// widgets, just a labeled text field accepting any valid CSS value
// (hex/named/rgb()/var(...) for color, any CSS length for spacing, etc.).
// Keeps the control surface small and avoids depending on experimental
// block-editor color/border components that shift across WP versions.
const FIELD_META = {
	borderWidth: { label: __( 'Border width', 'navigation-designer' ), placeholder: 'e.g. 1px' },
	borderStyle: {
		label: __( 'Border style', 'navigation-designer' ),
		type: 'select',
		options: [
			{ label: __( 'Default', 'navigation-designer' ), value: '' },
			{ label: __( 'Solid', 'navigation-designer' ), value: 'solid' },
			{ label: __( 'Dashed', 'navigation-designer' ), value: 'dashed' },
			{ label: __( 'Dotted', 'navigation-designer' ), value: 'dotted' },
			{ label: __( 'Double', 'navigation-designer' ), value: 'double' },
			{ label: __( 'None', 'navigation-designer' ), value: 'none' },
		],
	},
	borderColor: { label: __( 'Border color', 'navigation-designer' ), placeholder: '#111827' },
	radius: { label: __( 'Corner radius', 'navigation-designer' ), placeholder: 'e.g. 8px' },
	shadow: { label: __( 'Box shadow', 'navigation-designer' ), placeholder: 'e.g. 0 4px 12px rgba(0,0,0,.15)' },
	gap: { label: __( 'Gap between items', 'navigation-designer' ), placeholder: 'e.g. 1.5em' },
	itemPaddingY: { label: __( 'Item padding (top/bottom)', 'navigation-designer' ), placeholder: 'e.g. 0.5em' },
	itemPaddingX: { label: __( 'Item padding (left/right)', 'navigation-designer' ), placeholder: 'e.g. 1em' },
	itemHoverColor: { label: __( 'Hover text color', 'navigation-designer' ), placeholder: '#111827' },
	itemHoverBackground: { label: __( 'Hover background', 'navigation-designer' ), placeholder: '#f5f5f5' },
	itemFocusColor: { label: __( 'Focus text color', 'navigation-designer' ), placeholder: '#111827' },
	itemFocusBackground: { label: __( 'Focus background', 'navigation-designer' ), placeholder: '#f5f5f5' },
	background: { label: __( 'Background color', 'navigation-designer' ), placeholder: '#ffffff' },
	text: { label: __( 'Text color', 'navigation-designer' ), placeholder: '#111827' },
	paddingY: { label: __( 'Padding (top/bottom)', 'navigation-designer' ), placeholder: 'e.g. 0.75em' },
	paddingX: { label: __( 'Padding (left/right)', 'navigation-designer' ), placeholder: 'e.g. 1em' },
	offset: { label: __( 'Offset from parent item', 'navigation-designer' ), placeholder: 'e.g. 0.5em' },
	hoverText: { label: __( 'Hover text color', 'navigation-designer' ), placeholder: '#111827' },
	hoverBackground: { label: __( 'Hover background', 'navigation-designer' ), placeholder: '#f5f5f5' },
	focusText: { label: __( 'Focus text color', 'navigation-designer' ), placeholder: '#111827' },
	focusBackground: { label: __( 'Focus background', 'navigation-designer' ), placeholder: '#f5f5f5' },
	fontSize: { label: __( 'Font size', 'navigation-designer' ), placeholder: 'e.g. 0.9rem' },
	fontWeight: {
		label: __( 'Font weight', 'navigation-designer' ),
		type: 'select',
		options: [
			{ label: __( 'Default', 'navigation-designer' ), value: '' },
			{ label: '300', value: '300' },
			{ label: '400', value: '400' },
			{ label: '500', value: '500' },
			{ label: '600', value: '600' },
			{ label: '700', value: '700' },
			{ label: '800', value: '800' },
		],
	},
	lineHeight: { label: __( 'Line height', 'navigation-designer' ), placeholder: 'e.g. 1.4' },
	letterSpacing: { label: __( 'Letter spacing', 'navigation-designer' ), placeholder: 'e.g. 0.02em' },
	textTransform: {
		label: __( 'Text transform', 'navigation-designer' ),
		type: 'select',
		options: [
			{ label: __( 'Default', 'navigation-designer' ), value: '' },
			{ label: __( 'None', 'navigation-designer' ), value: 'none' },
			{ label: __( 'Uppercase', 'navigation-designer' ), value: 'uppercase' },
			{ label: __( 'Lowercase', 'navigation-designer' ), value: 'lowercase' },
			{ label: __( 'Capitalize', 'navigation-designer' ), value: 'capitalize' },
		],
	},
};

const GROUP_CATEGORIES = {
	nav: [
		{ title: __( 'Color (hover & focus)', 'navigation-designer' ), fields: [ 'itemHoverColor', 'itemHoverBackground', 'itemFocusColor', 'itemFocusBackground' ] },
		{ title: __( 'Border & shape', 'navigation-designer' ), fields: [ 'borderWidth', 'borderStyle', 'borderColor', 'radius', 'shadow' ] },
		{ title: __( 'Spacing', 'navigation-designer' ), fields: [ 'gap', 'itemPaddingY', 'itemPaddingX' ] },
		{ title: __( 'Typography', 'navigation-designer' ), fields: [ 'fontSize', 'fontWeight', 'lineHeight', 'letterSpacing', 'textTransform' ] },
	],
	submenu: [
		{ title: __( 'Color', 'navigation-designer' ), fields: [ 'background', 'text' ] },
		{ title: __( 'Border & shape', 'navigation-designer' ), fields: [ 'borderWidth', 'borderStyle', 'borderColor', 'radius', 'shadow' ] },
		{ title: __( 'Spacing', 'navigation-designer' ), fields: [ 'paddingY', 'paddingX', 'offset' ] },
		{ title: __( 'Typography', 'navigation-designer' ), fields: [ 'fontSize', 'fontWeight', 'lineHeight', 'letterSpacing', 'textTransform' ] },
	],
	submenuItem: [
		{ title: __( 'Color', 'navigation-designer' ), fields: [ 'text', 'hoverText', 'hoverBackground', 'focusText', 'focusBackground' ] },
		{ title: __( 'Spacing', 'navigation-designer' ), fields: [ 'paddingY', 'paddingX' ] },
		{ title: __( 'Typography', 'navigation-designer' ), fields: [ 'fontSize', 'fontWeight', 'lineHeight', 'letterSpacing', 'textTransform' ] },
	],
};

const GROUP_TITLES = {
	nav: __( 'Navigation', 'navigation-designer' ),
	submenu: __( 'Submenu', 'navigation-designer' ),
	submenuItem: __( 'Submenu items', 'navigation-designer' ),
};

function Field( { field, value, onChange } ) {
	const meta = FIELD_META[ field ];
	if ( ! meta ) {
		return null;
	}

	if ( meta.type === 'select' ) {
		return (
			<div className="components-base-control" style={ { marginBottom: '1.2em' } }>
				<label
					className="components-base-control__label"
					style={ { display: 'block', marginBottom: '0.5em' } }
				>
					{ meta.label }
				</label>
				<select
					value={ value }
					onChange={ ( event ) => onChange( event.target.value ) }
					style={ { width: '100%' } }
				>
					{ meta.options.map( ( option ) => (
						<option key={ option.value } value={ option.value }>
							{ option.label }
						</option>
					) ) }
				</select>
			</div>
		);
	}

	return (
		<TextControl
			label={ meta.label }
			placeholder={ meta.placeholder }
			value={ value }
			onChange={ onChange }
			__nextHasNoMarginBottom
		/>
	);
}

function GroupSection( { group, values, onFieldChange } ) {
	const [ breakpoint, setBreakpoint ] = useState( 'desktop' );
	const tierValues = values[ breakpoint ];

	return (
		<PanelBody title={ GROUP_TITLES[ group ] } initialOpen={ false }>
			<div style={ { marginBottom: '1em' } }>
				<div style={ { marginBottom: '0.5em', fontWeight: 500 } }>{ __( 'Breakpoint', 'navigation-designer' ) }</div>
				<div className="components-button-group">
					<button
						type="button"
						className={ `components-button is-small ${ breakpoint === 'desktop' ? 'is-primary' : 'is-secondary' }` }
						onClick={ () => setBreakpoint( 'desktop' ) }
					>
						{ __( 'Desktop', 'navigation-designer' ) }
					</button>
					<button
						type="button"
						className={ `components-button is-small ${ breakpoint === 'mobile' ? 'is-primary' : 'is-secondary' }` }
						style={ { marginLeft: '0.5em' } }
						onClick={ () => setBreakpoint( 'mobile' ) }
					>
						{ __( 'Mobile', 'navigation-designer' ) }
					</button>
				</div>
			</div>

			{ GROUP_CATEGORIES[ group ].map( ( category ) => (
				<div key={ category.title }>
					<h3 style={ { fontSize: '11px', textTransform: 'uppercase', letterSpacing: '0.05em', opacity: 0.7, margin: '1.5em 0 0.5em' } }>
						{ category.title }
					</h3>
					{ category.fields.map( ( field ) => (
						<Field
							key={ field }
							field={ field }
							value={ tierValues[ field ] || '' }
							onChange={ ( value ) => onFieldChange( group, breakpoint, field, value ) }
						/>
					) ) }
				</div>
			) ) }
		</PanelBody>
	);
}

export default function NavDesignerPanel( { attributes, setAttributes } ) {
	const navDesigner = normalizeInstance( attributes.navDesigner );

	const ensureId = ( nextAttrs ) => {
		if ( ! attributes.navDesignerId ) {
			nextAttrs.navDesignerId = generateId();
		}
		return nextAttrs;
	};

	const updateField = ( group, breakpoint, field, value ) => {
		setAttributes(
			ensureId( {
				navDesigner: {
					...navDesigner,
					enabled: true,
					[ group ]: {
						...navDesigner[ group ],
						[ breakpoint ]: {
							...navDesigner[ group ][ breakpoint ],
							[ field ]: value,
						},
					},
				},
			} )
		);
	};

	const toggleEnabled = ( enabled ) => {
		setAttributes( ensureId( { navDesigner: { ...navDesigner, enabled } } ) );
	};

	const reset = () => {
		setAttributes( { navDesigner: defaultInstance() } );
	};

	return (
		<InspectorControls>
			<PanelBody title={ __( 'Navigation Designer', 'navigation-designer' ) } initialOpen={ false }>
				<ToggleControl
					label={ __( 'Custom styles for this navigation', 'navigation-designer' ) }
					checked={ !! navDesigner.enabled }
					onChange={ toggleEnabled }
					__nextHasNoMarginBottom
				/>
				{ navDesigner.enabled && (
					<button type="button" className="components-button is-tertiary is-destructive" onClick={ reset }>
						{ __( 'Remove all custom styles', 'navigation-designer' ) }
					</button>
				) }
			</PanelBody>

			{ navDesigner.enabled && (
				<>
					<GroupSection group="nav" values={ navDesigner.nav } onFieldChange={ updateField } />
					<GroupSection group="submenu" values={ navDesigner.submenu } onFieldChange={ updateField } />
					<GroupSection group="submenuItem" values={ navDesigner.submenuItem } onFieldChange={ updateField } />
				</>
			) }
		</InspectorControls>
	);
}
