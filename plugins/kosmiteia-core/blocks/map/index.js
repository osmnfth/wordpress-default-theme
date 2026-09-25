( function ( blocks, blockEditor, components, element, i18n ) {
	'use strict';

	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;

	var useBlockProps = blockEditor.useBlockProps;
	var InspectorControls = blockEditor.InspectorControls;
	var PanelBody = components.PanelBody;
	var TextControl = components.TextControl;
	var RangeControl = components.RangeControl;
	var ToggleControl = components.ToggleControl;
	var Placeholder = components.Placeholder;
	var ExternalLink = components.ExternalLink;

	function toNumber( value, fallback ) {
		var parsed = parseFloat( String( value ).replace( ',', '.' ) );

		return isNaN( parsed ) ? fallback : parsed;
	}

	blocks.registerBlockType( 'kosmiteia/map', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;
			var blockProps = useBlockProps( { className: 'kosmiteia-map-editor' } );

			var osmUrl =
				'https://www.openstreetmap.org/?mlat=' +
				attributes.lat +
				'&mlon=' +
				attributes.lng +
				'#map=' +
				attributes.zoom +
				'/' +
				attributes.lat +
				'/' +
				attributes.lng;

			return el(
				Fragment,
				{},
				el(
					InspectorControls,
					{},
					el(
						PanelBody,
						{ title: __( 'Θέση στον χάρτη', 'kosmiteia' ), initialOpen: true },
						el( TextControl, {
							label: __( 'Γεωγραφικό πλάτος (latitude)', 'kosmiteia' ),
							help: __( 'Δεξί κλικ σε σημείο του openstreetmap.org και «Show address» δίνει τις συντεταγμένες.', 'kosmiteia' ),
							value: String( attributes.lat ),
							onChange: function ( value ) {
								setAttributes( { lat: toNumber( value, attributes.lat ) } );
							}
						} ),
						el( TextControl, {
							label: __( 'Γεωγραφικό μήκος (longitude)', 'kosmiteia' ),
							value: String( attributes.lng ),
							onChange: function ( value ) {
								setAttributes( { lng: toNumber( value, attributes.lng ) } );
							}
						} ),
						el( RangeControl, {
							label: __( 'Ζουμ', 'kosmiteia' ),
							value: attributes.zoom,
							min: 3,
							max: 19,
							onChange: function ( value ) {
								setAttributes( { zoom: value } );
							}
						} ),
						el( RangeControl, {
							label: __( 'Ύψος χάρτη (px)', 'kosmiteia' ),
							value: attributes.height,
							min: 200,
							max: 900,
							step: 20,
							onChange: function ( value ) {
								setAttributes( { height: value } );
							}
						} )
					),
					el(
						PanelBody,
						{ title: __( 'Πινέζα και συμπεριφορά', 'kosmiteia' ), initialOpen: false },
						el( ToggleControl, {
							label: __( 'Εμφάνιση πινέζας', 'kosmiteia' ),
							checked: !! attributes.showMarker,
							onChange: function ( value ) {
								setAttributes( { showMarker: value } );
							}
						} ),
						el( TextControl, {
							label: __( 'Τίτλος πινέζας', 'kosmiteia' ),
							value: attributes.markerTitle,
							onChange: function ( value ) {
								setAttributes( { markerTitle: value } );
							}
						} ),
						el( TextControl, {
							label: __( 'Διεύθυνση', 'kosmiteia' ),
							help: __( 'Εμφανίζεται στην πινέζα και ως εναλλακτικό κείμενο χωρίς JavaScript.', 'kosmiteia' ),
							value: attributes.markerAddress,
							onChange: function ( value ) {
								setAttributes( { markerAddress: value } );
							}
						} ),
						el( ToggleControl, {
							label: __( 'Ζουμ με τη ρόδα του ποντικιού', 'kosmiteia' ),
							help: __( 'Απενεργοποιημένο ώστε η κύλιση της σελίδας να μη «κολλάει» στον χάρτη.', 'kosmiteia' ),
							checked: !! attributes.scrollWheelZoom,
							onChange: function ( value ) {
								setAttributes( { scrollWheelZoom: value } );
							}
						} ),
						el( ToggleControl, {
							label: __( 'Σύνδεσμος για οδηγίες πρόσβασης', 'kosmiteia' ),
							checked: !! attributes.showDirections,
							onChange: function ( value ) {
								setAttributes( { showDirections: value } );
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
							icon: 'location-alt',
							label: __( 'Χάρτης (Leaflet)', 'kosmiteia' ),
							instructions: __( 'Ο διαδραστικός χάρτης εμφανίζεται στο front-end. Ρυθμίστε τη θέση από τις ρυθμίσεις του μπλοκ.', 'kosmiteia' )
						},
						el(
							'p',
							{ className: 'kosmiteia-map-editor__summary' },
							( attributes.markerTitle || __( 'Χωρίς τίτλο πινέζας', 'kosmiteia' ) ) +
								( attributes.markerAddress ? ' — ' + attributes.markerAddress : '' )
						),
						el(
							'div',
							{ className: 'kosmiteia-map-editor__coords' },
							el( TextControl, {
								label: __( 'Γεωγραφικό πλάτος (latitude)', 'kosmiteia' ),
								value: String( attributes.lat ),
								onChange: function ( value ) {
									setAttributes( { lat: toNumber( value, attributes.lat ) } );
								}
							} ),
							el( TextControl, {
								label: __( 'Γεωγραφικό μήκος (longitude)', 'kosmiteia' ),
								value: String( attributes.lng ),
								onChange: function ( value ) {
									setAttributes( { lng: toNumber( value, attributes.lng ) } );
								}
							} ),
							el( TextControl, {
								label: __( 'Τίτλος πινέζας', 'kosmiteia' ),
								value: attributes.markerTitle,
								onChange: function ( value ) {
									setAttributes( { markerTitle: value } );
								}
							} ),
							el( TextControl, {
								label: __( 'Διεύθυνση', 'kosmiteia' ),
								value: attributes.markerAddress,
								onChange: function ( value ) {
									setAttributes( { markerAddress: value } );
								}
							} )
						),
						el( ExternalLink, { href: osmUrl }, __( 'Έλεγχος θέσης στο OpenStreetMap', 'kosmiteia' ) )
					)
				)
			);
		},

		save: function () {
			return null;
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n );
