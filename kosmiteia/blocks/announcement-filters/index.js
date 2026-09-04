/**
 * Μπλοκ «Φίλτρα ανακοινώσεων» (δυναμικό - render από PHP).
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

	var FIELDS = [
		{ key: 'showSearch', label: __( 'Πεδίο αναζήτησης', 'kosmiteia' ) },
		{ key: 'showCategory', label: __( 'Φίλτρο κατηγορίας', 'kosmiteia' ) },
		{ key: 'showFaculty', label: __( 'Φίλτρο Σχολής', 'kosmiteia' ) },
		{ key: 'showYear', label: __( 'Φίλτρο έτους', 'kosmiteia' ) },
		{ key: 'showCount', label: __( 'Αριθμός αποτελεσμάτων', 'kosmiteia' ) }
	];

	blocks.registerBlockType( 'kosmiteia/announcement-filters', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps();

			var toggles = FIELDS.map( function ( field ) {
				return el( ToggleControl, {
					key: field.key,
					label: field.label,
					checked: !! attributes[ field.key ],
					onChange: function ( value ) {
						var update = {};
						update[ field.key ] = value;
						setAttributes( update );
					}
				} );
			} );

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Πεδία φίλτρων', 'kosmiteia' ), initialOpen: true },
						toggles
					)
				),
				el(
					'div',
					blockProps,
					el(
						Disabled,
						{},
						el( ServerSideRender, {
							block: 'kosmiteia/announcement-filters',
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
