/* Block editor UI: a material picker in the sidebar and a server-rendered preview. No build step needed. */
( function ( wp ) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var ServerSideRender = wp.serverSideRender;

	wp.blocks.registerBlockType( 'pilequote-material-calculator/calculator', {
		edit: function ( props ) {
			var options = [
				{ label: __( 'All enabled materials (visitor picks)', 'pilequote-material-calculator' ), value: 'all' },
				{ label: __( 'Mulch', 'pilequote-material-calculator' ), value: 'mulch' },
				{ label: __( 'Topsoil', 'pilequote-material-calculator' ), value: 'topsoil' },
				{ label: __( 'Compost', 'pilequote-material-calculator' ), value: 'compost' },
				{ label: __( 'Gravel / crushed stone', 'pilequote-material-calculator' ), value: 'gravel' },
				{ label: __( 'Sand', 'pilequote-material-calculator' ), value: 'sand' },
				{ label: __( 'Concrete', 'pilequote-material-calculator' ), value: 'concrete' }
			];
			return el( 'div', useBlockProps(),
				el( InspectorControls, {},
					el( PanelBody, { title: __( 'Calculator', 'pilequote-material-calculator' ) },
						el( SelectControl, {
							label: __( 'Material', 'pilequote-material-calculator' ),
							value: props.attributes.material,
							options: options,
							onChange: function ( v ) { props.setAttributes( { material: v } ); }
						} )
					)
				),
				el( ServerSideRender, { block: 'pilequote-material-calculator/calculator', attributes: props.attributes } )
			);
		},
		save: function () { return null; }
	} );
} )( window.wp );
