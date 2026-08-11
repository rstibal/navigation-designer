import { addFilter } from '@wordpress/hooks';
import { createHigherOrderComponent } from '@wordpress/compose';
import NavDesignerPanel from '../components/NavDesignerPanel';
import LivePreviewStyle from '../components/LivePreviewStyle';

const TARGET_BLOCKS = [ 'core/navigation' ];

const withNavDesignerControls = createHigherOrderComponent( ( BlockEdit ) => {
	return ( props ) => {
		if ( ! TARGET_BLOCKS.includes( props.name ) ) {
			return <BlockEdit { ...props } />;
		}

		return (
			<>
				<BlockEdit { ...props } />
				<LivePreviewStyle
					clientId={ props.clientId }
					navDesigner={ props.attributes.navDesigner }
				/>
				<NavDesignerPanel
					attributes={ props.attributes }
					setAttributes={ props.setAttributes }
				/>
			</>
		);
	};
}, 'withNavDesignerControls' );

addFilter(
	'editor.BlockEdit',
	'navigation-designer/with-inspector-controls',
	withNavDesignerControls
);
