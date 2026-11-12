/* Block editor UI: a material picker in the sidebar and a server-rendered preview. No build step needed. */
( function ( wp ) {
	var el = wp.element.createElement;
	var __ = wp.i18n.__;
	var InspectorControls = wp.blockEditor.InspectorControls;
	var useBlockProps = wp.blockEditor.useBlockProps;
	var PanelBody = wp.components.PanelBody;
	var SelectControl = wp.components.SelectControl;
	var ServerSideRender = wp.serverSideRender;

	wp.blocks.registerBlockType( 'landscape-material-calculator/calculator', {
		edit: function ( props ) {
			var options = [
				{ label: __( 'All enabled materials (visitor picks)', 'landscape-material-calculator' ), value: 'all' },
				{ label: __( 'Mulch', 'landscape-material-calculator' ), value: 'mulch' },
				{ label: __( 'Topsoil', 'landscape-material-calculator' ), value: 'topsoil' },
				{ label: __( 'Compost', 'landscape-material-calculator' ), value: 'compost' },
				{ label: __( 'Gravel / crushed stone', 'landscape-material-calculator' ), value: 'gravel' },
				{ label: __( 'Sand', 'landscape-material-calculator' ), value: 'sand' },
				{ label: __( 'Concrete', 'landscape-material-calculator' ), value: 'concrete' }
			];
			return el( 'div', useBlockProps(),
				el( InspectorControls, {},
					el( PanelBody, { title: __( 'Calculator', 'landscape-material-calculator' ) },
						el( SelectControl, {
							label: __( 'Material', 'landscape-material-calculator' ),
							value: props.attributes.material,
							options: options,
							onChange: function ( v ) { props.setAttributes( { material: v } ); }
						} )
					)
				),
				el( ServerSideRender, { block: 'landscape-material-calculator/calculator', attributes: props.attributes } )
			);
		},
		save: function () { return null; }
	} );
} )( window.wp );
