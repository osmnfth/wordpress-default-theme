/**
 * Μπλοκ «Γκαλερί εικόνων».
 *
 * Οι φωτογραφίες ΔΕΝ επιλέγονται εδώ: ορίζονται στο πεδίο «Φωτογραφίες
 * (γκαλερί)» της σελίδας (meta box, inc/gallery.php). Το μπλοκ ρυθμίζει μόνο
 * την εμφάνιση - στήλες, κενό, αναλογίες, λεζάντες - και τυπώνει τις
 * φωτογραφίες της σελίδας στην οποία εμφανίζεται.
 */
( function ( blocks, blockEditor, components, element, i18n ) {
	'use strict';

	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;
	var sprintf = i18n.sprintf;

	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;

	var PanelBody = components.PanelBody;
	var SelectControl = components.SelectControl;
	var RangeControl = components.RangeControl;
	var ToggleControl = components.ToggleControl;
	var TextControl = components.TextControl;
	var Placeholder = components.Placeholder;

	var RATIOS = [
		{ label: __( 'Τετράγωνο (1:1)', 'kosmiteia' ), value: '1/1' },
		{ label: __( 'Οριζόντιο (4:3)', 'kosmiteia' ), value: '4/3' },
		{ label: __( 'Οριζόντιο (3:2)', 'kosmiteia' ), value: '3/2' },
		{ label: __( 'Πανοραμικό (16:9)', 'kosmiteia' ), value: '16/9' },
		{ label: __( 'Κατακόρυφο (3:4)', 'kosmiteia' ), value: '3/4' },
		{ label: __( 'Φυσικές αναλογίες', 'kosmiteia' ), value: 'auto' }
	];

	blocks.registerBlockType( 'kosmiteia/gallery', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			var blockProps = useBlockProps( {
				className: 'kosmiteia-gallery is-editor-preview'
			} );

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Ενότητα', 'kosmiteia' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Τίτλος ενότητας', 'kosmiteia' ),
							help: __( 'Προαιρετικός· εμφανίζεται μόνο όταν υπάρχουν εικόνες.', 'kosmiteia' ),
							value: attributes.heading,
							onChange: function ( value ) {
								setAttributes( { heading: value } );
							}
						} ),
						el( RangeControl, {
							label: __( 'Μέγιστο πλήθος εικόνων', 'kosmiteia' ),
							help: __( '0 = χωρίς όριο.', 'kosmiteia' ),
							value: attributes.limit,
							min: 0,
							max: 60,
							onChange: function ( value ) {
								setAttributes( { limit: value || 0 } );
							}
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Διάταξη', 'kosmiteia' ), initialOpen: false },
						el( RangeControl, {
							label: __( 'Στήλες σε μεγάλες οθόνες', 'kosmiteia' ),
							value: attributes.columns,
							min: 2,
							max: 8,
							onChange: function ( value ) {
								setAttributes( { columns: value } );
							}
						} ),
						el( RangeControl, {
							label: __( 'Κενό ανάμεσα στις μικρογραφίες (px)', 'kosmiteia' ),
							value: attributes.gap,
							min: 0,
							max: 48,
							step: 2,
							onChange: function ( value ) {
								setAttributes( { gap: value } );
							}
						} ),
						el( RangeControl, {
							label: __( 'Ελάχιστο πλάτος μικρογραφίας (px)', 'kosmiteia' ),
							help: __( 'Καθορίζει πόσες μικρογραφίες χωρούν σε μικρές οθόνες.', 'kosmiteia' ),
							value: attributes.minWidth,
							min: 100,
							max: 320,
							step: 10,
							onChange: function ( value ) {
								setAttributes( { minWidth: value } );
							}
						} ),
						el( SelectControl, {
							label: __( 'Αναλογίες μικρογραφίας', 'kosmiteia' ),
							value: attributes.aspectRatio,
							options: RATIOS,
							onChange: function ( value ) {
								setAttributes( { aspectRatio: value } );
							}
						} ),
						el( ToggleControl, {
							label: __( 'Λεζάντες κάτω από τις μικρογραφίες', 'kosmiteia' ),
							help: __( 'Οι λεζάντες εμφανίζονται πάντα μέσα στο lightbox.', 'kosmiteia' ),
							checked: !! attributes.showCaptions,
							onChange: function ( value ) {
								setAttributes( { showCaptions: value } );
							}
						} )
					)
				),
				el(
					'div',
					blockProps,
					el(
						Placeholder,
						{
							icon: 'format-gallery',
							label: __( 'Γκαλερί εικόνων', 'kosmiteia' ),
							instructions: __( 'Οι φωτογραφίες επιλέγονται στο πεδίο «Φωτογραφίες (γκαλερί)», κάτω από το κείμενο της σελίδας. Εδώ ρυθμίζεται μόνο η εμφάνιση.', 'kosmiteia' )
						},
						el(
							'p',
							{ className: 'kosmiteia-gallery__editor-hint' },
							sprintf(
								/* translators: 1: πλήθος στηλών, 2: αναλογίες μικρογραφίας. */
								__( 'Στήλες: %1$s — αναλογίες: %2$s', 'kosmiteia' ),
								attributes.columns,
								attributes.aspectRatio
							)
						)
					)
				)
			);
		},

		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n );
