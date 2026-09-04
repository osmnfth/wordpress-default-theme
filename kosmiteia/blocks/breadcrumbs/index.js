/**
 * Μπλοκ «Διαδρομή πλοήγησης» (δυναμικό - render από PHP).
 */
( function ( blocks, blockEditor, components, element, i18n, serverSideRender ) {
	'use strict';

	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;

	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var ToggleControl = components.ToggleControl;
	var Disabled = components.Disabled;
	var ServerSideRender = serverSideRender;

	blocks.registerBlockType( 'kosmiteia/breadcrumbs', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Εμφάνιση', 'kosmiteia' ), initialOpen: true },
						el( ToggleControl, {
							label: __( 'Σύνδεσμος Αρχικής', 'kosmiteia' ),
							checked: !! attributes.showHome,
							onChange: function ( value ) {
								setAttributes( { showHome: value } );
							}
						} ),
						el( ToggleControl, {
							label: __( 'Τρέχουσα σελίδα', 'kosmiteia' ),
							checked: !! attributes.showCurrent,
							onChange: function ( value ) {
								setAttributes( { showCurrent: value } );
							}
						} )
					)
				),
				el(
					'div',
					blockProps,
					el(
						Disabled,
						{},
						el( ServerSideRender, {
							block: 'kosmiteia/breadcrumbs',
							attributes: attributes
						} )
					)
				)
			);
		},

		save: function () {
			return null;
		}
	} );
} )(
	window.wp.blocks,
	window.wp.blockEditor,
	window.wp.components,
	window.wp.element,
	window.wp.i18n,
	window.wp.serverSideRender
);
