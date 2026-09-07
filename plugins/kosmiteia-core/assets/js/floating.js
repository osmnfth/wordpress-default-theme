/**
 * Kosmiteia - πλωτό κουμπί με παράθυρο μηνύματος.
 *
 * Εμφάνιση: το κουμπί φαίνεται όσο ο επισκέπτης είναι στην κορυφή της σελίδας,
 * κρύβεται μόλις αρχίσει το σκρολάρισμα και ξαναεμφανίζεται όταν φτάσει στο
 * τέλος. Σε σελίδες που δεν κυλούν μένει μόνιμα ορατό.
 *
 * Παράθυρο: ανοίγει με κλικ, κλείνει με Esc, με το «×» ή με κλικ έξω από το
 * πλαίσιο. Όσο είναι ανοιχτό το focus παραμένει μέσα του και επιστρέφει στο
 * κουμπί με το κλείσιμο.
 */
( function () {
	'use strict';

	var TOP_OFFSET = 40;
	var BOTTOM_OFFSET = 80;
	var FOCUSABLE = 'a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])';

	var button = document.querySelector( '[data-kosmiteia-fab]' );
	var modal = document.querySelector( '[data-kosmiteia-fab-modal]' );

	if ( ! button || ! modal ) {
		return;
	}

	var panel = modal.querySelector( '.kosmiteia-fab-modal__panel' );
	var lastFocused = null;
	var ticking = false;

	/* ---------------------------------------------------------------------
	 * Εμφάνιση / απόκρυψη με το σκρολάρισμα
	 * ------------------------------------------------------------------ */

	function isVisible() {
		var doc = document.documentElement;
		var scrolled = window.scrollY || doc.scrollTop || 0;
		var height = doc.scrollHeight;

		// Σελίδα που δεν κυλάει: το κουμπί μένει ορατό.
		if ( height - window.innerHeight <= TOP_OFFSET + BOTTOM_OFFSET ) {
			return true;
		}

		if ( scrolled <= TOP_OFFSET ) {
			return true;
		}

		return scrolled + window.innerHeight >= height - BOTTOM_OFFSET;
	}

	function update() {
		button.classList.toggle( 'is-visible', isVisible() || ! modal.hidden );
		ticking = false;
	}

	function onScroll() {
		if ( ! ticking ) {
			window.requestAnimationFrame( update );
			ticking = true;
		}
	}

	/* ---------------------------------------------------------------------
	 * Το παράθυρο
	 * ------------------------------------------------------------------ */

	function focusable() {
		return Array.prototype.filter.call(
			panel.querySelectorAll( FOCUSABLE ),
			function ( element ) {
				return null !== element.offsetParent;
			}
		);
	}

	function open() {
		lastFocused = document.activeElement;

		modal.hidden = false;
		button.setAttribute( 'aria-expanded', 'true' );
		document.documentElement.classList.add( 'kosmiteia-has-modal' );

		var targets = focusable();

		if ( targets.length ) {
			targets[ 0 ].focus();
		} else {
			panel.setAttribute( 'tabindex', '-1' );
			panel.focus();
		}

		update();
	}

	function close() {
		modal.hidden = true;
		button.setAttribute( 'aria-expanded', 'false' );
		document.documentElement.classList.remove( 'kosmiteia-has-modal' );

		// Το focus γυρίζει εκεί που ήταν - συνήθως στο ίδιο το κουμπί.
		var back = ( lastFocused && document.body !== lastFocused && 'function' === typeof lastFocused.focus )
			? lastFocused
			: button;

		back.focus();

		update();
	}

	function trapFocus( event ) {
		var targets = focusable();

		if ( ! targets.length ) {
			return;
		}

		var first = targets[ 0 ];
		var last = targets[ targets.length - 1 ];

		if ( event.shiftKey && document.activeElement === first ) {
			event.preventDefault();
			last.focus();
		} else if ( ! event.shiftKey && document.activeElement === last ) {
			event.preventDefault();
			first.focus();
		}
	}

	/* ---------------------------------------------------------------------
	 * Σύνδεση
	 * ------------------------------------------------------------------ */

	button.classList.add( 'is-ready' );
	button.addEventListener( 'click', open );

	Array.prototype.forEach.call(
		modal.querySelectorAll( '[data-kosmiteia-fab-close]' ),
		function ( element ) {
			element.addEventListener( 'click', close );
		}
	);

	modal.addEventListener( 'keydown', function ( event ) {
		if ( 'Escape' === event.key || 'Esc' === event.key ) {
			close();
		} else if ( 'Tab' === event.key ) {
			trapFocus( event );
		}
	} );

	window.addEventListener( 'scroll', onScroll, { passive: true } );
	window.addEventListener( 'resize', onScroll, { passive: true } );

	// Το ύψος της σελίδας αλλάζει και χωρίς σκρολάρισμα (εικόνες που φορτώνουν
	// αργότερα, ενσωματώσεις): αλλιώς το κουμπί θα έμενε κρυμμένο στο τέλος.
	if ( 'ResizeObserver' in window ) {
		new ResizeObserver( onScroll ).observe( document.documentElement );
	}

	update();
} )();
