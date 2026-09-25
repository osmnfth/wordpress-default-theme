( function ( $ ) {
	'use strict';

	var l10n = window.kosmiteiaFileFieldL10n || {};

	$( function () {
		$( '[data-kosmiteia-file-field]' ).each( function () {
			var field = $( this );
			var input = field.find( '[data-input]' );
			var name = field.find( '[data-name]' );
			var link = field.find( '[data-link]' );
			var remove = field.find( '[data-action="remove"]' );
			var frame = null;

			function render( url, title ) {
				input.val( url );
				link.attr( 'href', url ).text( title );
				name.prop( 'hidden', ! url );
				remove.prop( 'hidden', ! url );
			}

			field.on( 'click', '[data-action="select"]', function ( event ) {
				event.preventDefault();

				if ( ! frame ) {
					frame = wp.media( {
						title: l10n.title || '',
						button: { text: l10n.button || '' },
						multiple: false
					} );

					frame.on( 'select', function () {
						var attachment = frame.state().get( 'selection' ).first().toJSON();

						render( attachment.url, attachment.filename || attachment.title );
					} );
				}

				frame.open();
			} );

			field.on( 'click', '[data-action="remove"]', function ( event ) {
				event.preventDefault();

				render( '', '' );
			} );
		} );
	} );
} )( jQuery );
