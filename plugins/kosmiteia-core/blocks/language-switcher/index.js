/**
 * Μπλοκ "Επιλογέας γλώσσας" (δυναμικό - render από PHP).
 */
( function ( blocks, blockEditor, components, element, i18n, serverSideRender ) {
	'use strict';

	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;

	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var Disabled = components.Disabled;
	var ServerSideRender = serverSideRender;

	blocks.registerBlockType( 'kosmiteia/language-switcher', {
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
						el( SelectControl, {
							label: __( 'Ετικέτες γλωσσών', 'kosmiteia' ),
							value: attributes.display,
							options: [
								{ label: __( 'Σύντομες (EL / EN)', 'kosmiteia' ), value: 'short' },
								{ label: __( 'Πλήρεις (Ελληνικά / English)', 'kosmiteia' ), value: 'full' }
							],
							onChange: function ( value ) {
								setAttributes( { display: value } );
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
							block: 'kosmiteia/language-switcher',
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
