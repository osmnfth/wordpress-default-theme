( function ( $ ) {
	'use strict';

	var l10n = window.kosmiteiaGalleryFieldL10n || {};

	function text( key, fallback ) {
		return l10n[ key ] ? l10n[ key ] : fallback;
	}

	$( function () {
		$( '[data-kosmiteia-gallery-field]' ).each( function () {
			var field = $( this );
			var list = field.find( '[data-list]' );
			var input = field.find( '[data-input]' );
			var empty = field.find( '[data-empty]' );
			var clear = field.find( '[data-action="clear"]' );
			var frame = null;

			function ids() {
				return list
					.find( '.kosmiteia-gallery-field__item' )
					.map( function () {
						return parseInt( $( this ).attr( 'data-id' ), 10 );
					} )
					.get();
			}

			function sync() {
				var current = ids();

				input.val( current.join( ',' ) );
				empty.prop( 'hidden', current.length > 0 );
				clear.prop( 'disabled', 0 === current.length );
			}

			function item( attachment ) {
				var sizes = attachment.sizes || {};
				var preview = sizes.thumbnail || sizes.medium || sizes.full || attachment;

				var node = $( '<li class="kosmiteia-gallery-field__item"></li>' ).attr(
					'data-id',
					attachment.id
				);

				$( '<img class="kosmiteia-gallery-field__image">' )
					.attr( 'src', preview.url )
					.attr( 'alt', attachment.alt || '' )
					.appendTo( node );

				var buttons = $( '<span class="kosmiteia-gallery-field__buttons"></span>' );

				$( '<button type="button" class="button-link" data-action="move-up">◀</button>' )
					.attr( 'aria-label', text( 'moveUp', 'Μετακίνηση πιο μπροστά' ) )
					.appendTo( buttons );

				$( '<button type="button" class="button-link" data-action="move-down">▶</button>' )
					.attr( 'aria-label', text( 'moveDown', 'Μετακίνηση πιο πίσω' ) )
					.appendTo( buttons );

				$(
					'<button type="button" class="button-link kosmiteia-gallery-field__remove" data-action="remove">×</button>'
				)
					.attr( 'aria-label', text( 'remove', 'Αφαίρεση φωτογραφίας' ) )
					.appendTo( buttons );

				return node.append( buttons );
			}

			field.on( 'click', '[data-action="select"]', function ( event ) {
				event.preventDefault();

				if ( ! frame ) {
					frame = window.wp.media( {
						title: text( 'title', 'Φωτογραφίες (γκαλερί)' ),
						button: { text: text( 'button', 'Χρήση αυτών των φωτογραφιών' ) },
						library: { type: 'image' },
						multiple: 'add'
					} );

					frame.on( 'select', function () {
						var selection = frame.state().get( 'selection' );

						list.empty();

						selection.each( function ( attachment ) {
							list.append( item( attachment.toJSON() ) );
						} );

						sync();
					} );
				}

				frame.on( 'open', function () {
					var selection = frame.state().get( 'selection' );

					selection.reset(
						ids().map( function ( id ) {
							return window.wp.media.attachment( id );
						} )
					);
				} );

				frame.open();
			} );

			field.on( 'click', '[data-action="remove"]', function ( event ) {
				event.preventDefault();
				$( this ).closest( '.kosmiteia-gallery-field__item' ).remove();
				sync();
			} );

			field.on( 'click', '[data-action="move-up"], [data-action="move-down"]', function ( event ) {
				event.preventDefault();

				var button = $( this );
				var node = button.closest( '.kosmiteia-gallery-field__item' );
				var up = 'move-up' === button.attr( 'data-action' );
				var sibling = up ? node.prev() : node.next();

				if ( ! sibling.length ) {
					return;
				}

				if ( up ) {
					sibling.before( node );
				} else {
					sibling.after( node );
				}

				button.trigger( 'focus' );
				sync();
			} );

			field.on( 'click', '[data-action="clear"]', function ( event ) {
				event.preventDefault();

				if ( ! window.confirm( text( 'confirmAll', 'Να αφαιρεθούν όλες οι φωτογραφίες;' ) ) ) {
					return;
				}

				list.empty();
				sync();
			} );

			sync();
		} );
	} );
} )( window.jQuery );
