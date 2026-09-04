/**
 * Kosmiteia - lightbox για τις μικρογραφίες της γκαλερί.
 *
 * Λειτουργεί σαν το γνωστό Colorbox, αλλά χωρίς jQuery και χωρίς εξωτερικές
 * βιβλιοθήκες: ένα μόνο overlay φτιάχνεται με το πρώτο άνοιγμα και
 * επαναχρησιμοποιείται.
 *
 * Προσβασιμότητα:
 *   - role="dialog" aria-modal, focus παγιδευμένο μέσα στο παράθυρο
 *   - Escape κλείνει, βελάκια αλλάζουν εικόνα, το focus επιστρέφει στη
 *     μικρογραφία από την οποία ξεκίνησε
 *   - χωρίς JavaScript οι μικρογραφίες παραμένουν απλοί σύνδεσμοι προς το
 *     αρχείο της εικόνας
 */
( function () {
	'use strict';

	var SELECTOR = '[data-kosmiteia-lightbox]';
	var l10n = window.kosmiteiaLightboxL10n || {};

	var overlay = null;
	var dialog = null;
	var figure = null;
	var image = null;
	var caption = null;
	var counter = null;
	var prevButton = null;
	var nextButton = null;
	var closeButton = null;

	var items = [];
	var index = 0;
	var lastFocused = null;

	/**
	 * Μετάφραση με fallback στο ελληνικό κείμενο.
	 *
	 * @param {string} key      Κλειδί του localize.
	 * @param {string} fallback Κείμενο αν λείπει το κλειδί.
	 * @return {string} Το κείμενο.
	 */
	function text( key, fallback ) {
		return l10n[ key ] ? l10n[ key ] : fallback;
	}

	/**
	 * Δημιουργεί στοιχείο με κλάση και προαιρετικά attributes.
	 *
	 * @param {string} tag        Ετικέτα HTML.
	 * @param {string} className  Κλάση.
	 * @param {Object} attributes Ζεύγη attribute/τιμής.
	 * @return {HTMLElement} Το στοιχείο.
	 */
	function create( tag, className, attributes ) {
		var node = document.createElement( tag );

		if ( className ) {
			node.className = className;
		}

		if ( attributes ) {
			Object.keys( attributes ).forEach( function ( name ) {
				node.setAttribute( name, attributes[ name ] );
			} );
		}

		return node;
	}

	/**
	 * Εικονίδιο (βελάκι ή «Χ») ως inline SVG.
	 *
	 * @param {string} path Το path του σχήματος.
	 * @return {SVGElement} Το εικονίδιο.
	 */
	function icon( path ) {
		var svg = document.createElementNS( 'http://www.w3.org/2000/svg', 'svg' );
		var shape = document.createElementNS( 'http://www.w3.org/2000/svg', 'path' );

		svg.setAttribute( 'viewBox', '0 0 24 24' );
		svg.setAttribute( 'aria-hidden', 'true' );
		svg.setAttribute( 'focusable', 'false' );
		shape.setAttribute( 'd', path );
		svg.appendChild( shape );

		return svg;
	}

	/**
	 * Φτιάχνει το overlay την πρώτη φορά που χρειάζεται.
	 */
	function build() {
		if ( overlay ) {
			return;
		}

		overlay = create( 'div', 'kosmiteia-lightbox', { hidden: 'hidden' } );

		dialog = create( 'div', 'kosmiteia-lightbox__dialog', {
			role: 'dialog',
			'aria-modal': 'true',
			'aria-label': text( 'dialog', 'Προβολή φωτογραφίας' ),
			tabindex: '-1'
		} );

		figure = create( 'figure', 'kosmiteia-lightbox__figure' );
		image = create( 'img', 'kosmiteia-lightbox__image', { alt: '' } );
		caption = create( 'figcaption', 'kosmiteia-lightbox__caption' );

		figure.appendChild( image );
		figure.appendChild( caption );

		counter = create( 'p', 'kosmiteia-lightbox__counter', { 'aria-live': 'polite' } );

		prevButton = create( 'button', 'kosmiteia-lightbox__nav kosmiteia-lightbox__nav--prev', {
			type: 'button',
			'aria-label': text( 'previous', 'Προηγούμενη φωτογραφία' )
		} );
		prevButton.appendChild( icon( 'M15.4 4.6 8 12l7.4 7.4 1.4-1.4-6-6 6-6z' ) );

		nextButton = create( 'button', 'kosmiteia-lightbox__nav kosmiteia-lightbox__nav--next', {
			type: 'button',
			'aria-label': text( 'next', 'Επόμενη φωτογραφία' )
		} );
		nextButton.appendChild( icon( 'M8.6 4.6 16 12l-7.4 7.4L7.2 18l6-6-6-6z' ) );

		closeButton = create( 'button', 'kosmiteia-lightbox__close', {
			type: 'button',
			'aria-label': text( 'close', 'Κλείσιμο' )
		} );
		closeButton.appendChild( icon( 'M18.3 5.7 12 12l6.3 6.3-1.4 1.4L12 13.4l-6.3 6.3-1.4-1.4L10.6 12 4.3 5.7l1.4-1.4L12 10.6l6.3-6.3z' ) );

		dialog.appendChild( closeButton );
		dialog.appendChild( prevButton );
		dialog.appendChild( figure );
		dialog.appendChild( nextButton );
		dialog.appendChild( counter );
		overlay.appendChild( dialog );
		document.body.appendChild( overlay );

		closeButton.addEventListener( 'click', close );
		prevButton.addEventListener( 'click', function () {
			show( index - 1 );
		} );
		nextButton.addEventListener( 'click', function () {
			show( index + 1 );
		} );

		// Κλικ έξω από την εικόνα κλείνει, όπως στο Colorbox.
		overlay.addEventListener( 'click', function ( event ) {
			if ( event.target === overlay || event.target === dialog || event.target === figure ) {
				close();
			}
		} );

		bindSwipe();
	}

	/**
	 * Σύρσιμο με το δάχτυλο: αριστερά/δεξιά αλλάζει φωτογραφία.
	 */
	function bindSwipe() {
		var startX = null;
		var startY = null;

		overlay.addEventListener(
			'touchstart',
			function ( event ) {
				if ( 1 !== event.touches.length ) {
					startX = null;
					return;
				}

				startX = event.touches[ 0 ].clientX;
				startY = event.touches[ 0 ].clientY;
			},
			{ passive: true }
		);

		overlay.addEventListener(
			'touchend',
			function ( event ) {
				if ( null === startX || ! event.changedTouches.length ) {
					return;
				}

				var deltaX = event.changedTouches[ 0 ].clientX - startX;
				var deltaY = event.changedTouches[ 0 ].clientY - startY;

				startX = null;

				if ( Math.abs( deltaX ) > 50 && Math.abs( deltaX ) > Math.abs( deltaY ) ) {
					show( deltaX < 0 ? index + 1 : index - 1 );
				}
			},
			{ passive: true }
		);
	}

	/**
	 * Οι σύνδεσμοι της ίδιας ομάδας, με τη σειρά που εμφανίζονται στη σελίδα.
	 *
	 * @param {string} name Η τιμή του data-kosmiteia-lightbox.
	 * @return {Array} Οι σύνδεσμοι.
	 */
	function group( name ) {
		var selector = '[data-kosmiteia-lightbox="' + String( name ).replace( /"/g, '\\"' ) + '"]';

		return Array.prototype.slice.call( document.querySelectorAll( selector ) );
	}

	/**
	 * Φορτώνει εκ των προτέρων μια εικόνα, ώστε η επόμενη/προηγούμενη να
	 * εμφανίζεται ακαριαία.
	 *
	 * @param {number} position Θέση στη λίστα.
	 */
	function preload( position ) {
		var link = items[ position ];

		if ( ! link ) {
			return;
		}

		var preloader = new window.Image();
		preloader.src = link.getAttribute( 'href' );
	}

	/**
	 * Δείχνει τη φωτογραφία μιας θέσης (κυκλικά).
	 *
	 * @param {number} position Θέση στη λίστα.
	 */
	function show( position ) {
		if ( ! items.length ) {
			return;
		}

		index = ( position + items.length ) % items.length;

		var link = items[ index ];
		var label = link.getAttribute( 'data-caption' ) || '';
		var alt = link.getAttribute( 'data-alt' ) || '';

		overlay.classList.add( 'is-loading' );

		image.onload = function () {
			overlay.classList.remove( 'is-loading' );
		};
		image.onerror = function () {
			overlay.classList.remove( 'is-loading' );
		};

		image.src = link.getAttribute( 'href' );
		image.alt = alt;

		var width = parseInt( link.getAttribute( 'data-width' ), 10 );
		var height = parseInt( link.getAttribute( 'data-height' ), 10 );

		if ( width && height ) {
			image.setAttribute( 'width', width );
			image.setAttribute( 'height', height );
		} else {
			image.removeAttribute( 'width' );
			image.removeAttribute( 'height' );
		}

		caption.textContent = label;
		caption.hidden = ! label;

		counter.textContent =
			items.length > 1
				? text( 'counter', '%1$s από %2$s' )
					.replace( '%1$s', index + 1 )
					.replace( '%2$s', items.length )
				: '';

		var multiple = items.length > 1;
		prevButton.hidden = ! multiple;
		nextButton.hidden = ! multiple;

		preload( index + 1 < items.length ? index + 1 : 0 );
		preload( index - 1 >= 0 ? index - 1 : items.length - 1 );
	}

	/**
	 * Ανοίγει το lightbox από μια μικρογραφία.
	 *
	 * @param {HTMLElement} link Ο σύνδεσμος που πατήθηκε.
	 */
	function open( link ) {
		build();

		items = group( link.getAttribute( 'data-kosmiteia-lightbox' ) );

		if ( ! items.length ) {
			items = [ link ];
		}

		lastFocused = link;

		overlay.hidden = false;
		document.documentElement.classList.add( 'is-kosmiteia-lightbox-open' );
		document.addEventListener( 'keydown', onKeydown, true );

		show( items.indexOf( link ) );

		// Το παράθυρο παίρνει το focus, ώστε τα βελάκια και το Escape να
		// δουλεύουν αμέσως και για τους αναγνώστες οθόνης.
		dialog.focus();
	}

	/**
	 * Κλείνει το lightbox και επιστρέφει το focus.
	 */
	function close() {
		if ( ! overlay || overlay.hidden ) {
			return;
		}

		overlay.hidden = true;
		overlay.classList.remove( 'is-loading' );
		image.removeAttribute( 'src' );
		document.documentElement.classList.remove( 'is-kosmiteia-lightbox-open' );
		document.removeEventListener( 'keydown', onKeydown, true );

		if ( lastFocused && document.contains( lastFocused ) ) {
			lastFocused.focus();
		}

		lastFocused = null;
	}

	/**
	 * Πληκτρολόγιο: Escape, βελάκια, παγίδευση του Tab μέσα στο παράθυρο.
	 *
	 * @param {KeyboardEvent} event Το συμβάν.
	 */
	function onKeydown( event ) {
		if ( ! overlay || overlay.hidden ) {
			return;
		}

		if ( 'Escape' === event.key || 'Esc' === event.key ) {
			event.preventDefault();
			close();
			return;
		}

		if ( 'ArrowLeft' === event.key ) {
			event.preventDefault();
			show( index - 1 );
			return;
		}

		if ( 'ArrowRight' === event.key ) {
			event.preventDefault();
			show( index + 1 );
			return;
		}

		if ( 'Tab' !== event.key ) {
			return;
		}

		var focusable = Array.prototype.filter.call(
			dialog.querySelectorAll( 'button' ),
			function ( button ) {
				return ! button.hidden;
			}
		);

		if ( ! focusable.length ) {
			return;
		}

		var first = focusable[ 0 ];
		var last = focusable[ focusable.length - 1 ];
		var active = document.activeElement;

		if ( event.shiftKey && ( active === first || active === dialog ) ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && active === last ) {
			event.preventDefault();
			first.focus();
		} else if ( ! dialog.contains( active ) ) {
			event.preventDefault();
			first.focus();
		}
	}

	// Ένας μόνο listener για όλη τη σελίδα: δουλεύει και για γκαλερί που
	// προστίθενται δυναμικά (π.χ. μετά από φίλτρα).
	document.addEventListener( 'click', function ( event ) {
		if ( event.button || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
			return;
		}

		var link = event.target.closest ? event.target.closest( SELECTOR ) : null;

		if ( ! link || ! link.getAttribute( 'href' ) ) {
			return;
		}

		event.preventDefault();
		open( link );
	} );
} )();
