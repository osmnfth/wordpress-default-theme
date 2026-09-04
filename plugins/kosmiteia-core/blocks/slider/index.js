/**
 * Μπλοκ "Slider (Κοσμητεία)".
 *
 * Γραμμένο χωρίς JSX ώστε το theme να μη χρειάζεται build step.
 * Κάθε διαφάνεια είναι κανονικό μπλοκ (Cover / Group / Media & Text),
 * άρα ο διαχειριστής αλλάζει φωτογραφία, βίντεο, κείμενα και κουμπιά
 * απευθείας από τον editor.
 */
( function ( blocks, blockEditor, components, element, i18n ) {
	'use strict';

	var el = element.createElement;
	var Fragment = element.Fragment;
	var __ = i18n.__;

	var useBlockProps = blockEditor.useBlockProps;
	var useInnerBlocksProps = blockEditor.useInnerBlocksProps;
	var InspectorControls = blockEditor.InspectorControls;
	var InnerBlocks = blockEditor.InnerBlocks;

	var PanelBody = components.PanelBody;
	var ToggleControl = components.ToggleControl;
	var RangeControl = components.RangeControl;
	var SelectControl = components.SelectControl;
	var TextControl = components.TextControl;

	var ALLOWED_BLOCKS = [ 'core/cover', 'core/group', 'core/media-text' ];

	var TEMPLATE = [
		[
			'core/cover',
			{
				align: 'full',
				minHeight: 70,
				minHeightUnit: 'vh',
				isDark: true,
				className: 'kosmiteia-slide',
				layout: { type: 'constrained' }
			},
			[
				[
					'core/heading',
					{
						textAlign: 'center',
						level: 2,
						placeholder: __( 'Τίτλος διαφάνειας', 'kosmiteia' ),
						fontSize: 'xx-large'
					}
				],
				[
					'core/paragraph',
					{
						align: 'center',
						placeholder: __( 'Σύντομο κείμενο για τη διαφάνεια.', 'kosmiteia' )
					}
				],
				[
					'core/buttons',
					{ layout: { type: 'flex', justifyContent: 'center' } },
					[ [ 'core/button', { text: __( 'Μάθετε περισσότερα', 'kosmiteia' ) } ] ]
				]
			]
		]
	];

	blocks.registerBlockType( 'kosmiteia/slider', {
		edit: function ( props ) {
			var attributes = props.attributes;
			var setAttributes = props.setAttributes;

			var blockProps = useBlockProps( {
				className: 'kosmiteia-slider is-editor-preview has-effect-' + attributes.effect
			} );

			var innerBlocksProps = useInnerBlocksProps(
				{ className: 'kosmiteia-slider__track' },
				{
					allowedBlocks: ALLOWED_BLOCKS,
					template: TEMPLATE,
					orientation: 'vertical',
					renderAppender: InnerBlocks.ButtonBlockAppender
				}
			);

			var controls = el(
				InspectorControls,
				{},
				el(
					PanelBody,
					{ title: __( 'Ρυθμίσεις slider', 'kosmiteia' ), initialOpen: true },
					el( ToggleControl, {
						label: __( 'Αυτόματη εναλλαγή', 'kosmiteia' ),
						help: __( 'Σταματά αυτόματα όταν ο χρήστης κάνει hover ή focus, και όταν έχει ενεργό το "μειωμένη κίνηση".', 'kosmiteia' ),
						checked: !! attributes.autoplay,
						onChange: function ( value ) {
							setAttributes( { autoplay: value } );
						}
					} ),
					attributes.autoplay
						? el( RangeControl, {
								label: __( 'Διάρκεια διαφάνειας (ms)', 'kosmiteia' ),
								value: attributes.interval,
								min: 3000,
								max: 15000,
								step: 500,
								onChange: function ( value ) {
									setAttributes( { interval: value } );
								}
						  } )
						: null,
					el( ToggleControl, {
						label: __( 'Βέλη πλοήγησης', 'kosmiteia' ),
						checked: !! attributes.showArrows,
						onChange: function ( value ) {
							setAttributes( { showArrows: value } );
						}
					} ),
					el( ToggleControl, {
						label: __( 'Κουκκίδες', 'kosmiteia' ),
						checked: !! attributes.showDots,
						onChange: function ( value ) {
							setAttributes( { showDots: value } );
						}
					} ),
					el( SelectControl, {
						label: __( 'Εφέ εναλλαγής', 'kosmiteia' ),
						value: attributes.effect,
						options: [
							{ label: __( 'Κύλιση', 'kosmiteia' ), value: 'slide' },
							{ label: __( 'Σβήσιμο (fade)', 'kosmiteia' ), value: 'fade' }
						],
						onChange: function ( value ) {
							setAttributes( { effect: value } );
						}
					} ),
					el( TextControl, {
						label: __( 'Περιγραφή για αναγνώστες οθόνης', 'kosmiteia' ),
						help: __( 'Προαιρετικό aria-label, π.χ. "Κεντρική παρουσίαση Κοσμητείας".', 'kosmiteia' ),
						value: attributes.label,
						onChange: function ( value ) {
							setAttributes( { label: value } );
						}
					} )
				)
			);

			return el(
				Fragment,
				{},
				controls,
				el(
					'div',
					blockProps,
					el( 'p', { className: 'kosmiteia-slider__editor-hint' }, __( 'Slider: κάθε μπλοκ παρακάτω είναι μία διαφάνεια. Στο front-end εμφανίζονται εναλλάξ.', 'kosmiteia' ) ),
					el( 'div', innerBlocksProps )
				)
			);
		},

		save: function ( props ) {
			var attributes = props.attributes;

			var blockProps = useBlockProps.save( {
				className: 'kosmiteia-slider has-effect-' + attributes.effect,
				'data-autoplay': attributes.autoplay ? 'true' : 'false',
				'data-interval': String( attributes.interval ),
				'data-arrows': attributes.showArrows ? 'true' : 'false',
				'data-dots': attributes.showDots ? 'true' : 'false',
				'data-effect': attributes.effect,
				'data-label': attributes.label || ''
			} );

			return el(
				'div',
				blockProps,
				el(
					'div',
					{ className: 'kosmiteia-slider__track' },
					el( InnerBlocks.Content, {} )
				)
			);
		}
	} );
} )( window.wp.blocks, window.wp.blockEditor, window.wp.components, window.wp.element, window.wp.i18n );
