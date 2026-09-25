( function ( $ ) {
	'use strict';

	var l10n = window.kosmiteiaMediaFieldL10n || {};

	$( function () {
		$( '[data-kosmiteia-image-field]' ).each( function () {
			var field = $( this );
			var preview = field.find( '[data-preview]' );
			var input = field.find( '[data-input]' );
			var remove = field.find( '[data-action="remove"]' );
			var frame = null;

			function render( url ) {
				preview.empty();

				if ( url ) {
					preview.append(
						$( '<img>', { src: url, alt: '', style: 'max-width:180px;height:auto' } )
					);
				}

				remove.prop( 'hidden', ! url );
			}

			field.on( 'click', '[data-action="select"]', function ( event ) {
				event.preventDefault();

				if ( ! frame ) {
					frame = wp.media( {
						title: l10n.title || '',
						button: { text: l10n.button || '' },
						library: { type: 'image' },
						multiple: false
					} );

					frame.on( 'select', function () {
						var attachment = frame.state().get( 'selection' ).first().toJSON();
						var size = attachment.sizes && attachment.sizes.medium
							? attachment.sizes.medium.url
							: attachment.url;

						input.val( attachment.id );
						render( size );
					} );
				}

				frame.open();
			} );

			field.on( 'click', '[data-action="remove"]', function ( event ) {
				event.preventDefault();

				input.val( 0 );
				render( '' );
			} );

			input.on( 'change', function () {
				if ( ! parseInt( input.val(), 10 ) ) {
					render( '' );
				}
			} );
		} );
	} );
} )( jQuery );
