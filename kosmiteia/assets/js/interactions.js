/**
 * Kosmiteia - μικρές βελτιώσεις διεπαφής.
 *
 * - sticky header που "μαζεύεται" στο scroll
 * - εμφάνιση ενοτήτων με IntersectionObserver (reveal on scroll)
 * - ομαλή κύλιση με σωστή μεταφορά focus για πληκτρολόγιο/screen readers
 *
 * Όλα σέβονται το prefers-reduced-motion και δεν επηρεάζουν τη λειτουργία
 * της σελίδας αν δεν εκτελεστεί το script.
 */
( function () {
	'use strict';

	var root = document.documentElement;
	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// Σημαία ότι τρέχει JS - το CSS των animations κρέμεται από αυτήν.
	root.classList.add( 'js-kosmiteia' );

	/* ---------------------------------------------------------------------
	 * Sticky header
	 * ------------------------------------------------------------------ */
	function initHeader() {
		var header = document.querySelector( '.kosmiteia-header' );

		if ( ! header ) {
			return;
		}

		var ticking = false;

		function update() {
			header.classList.toggle( 'is-scrolled', window.scrollY > 24 );
			ticking = false;
		}

		window.addEventListener(
			'scroll',
			function () {
				if ( ! ticking ) {
					window.requestAnimationFrame( update );
					ticking = true;
				}
			},
			{ passive: true }
		);

		update();
	}

	/* ---------------------------------------------------------------------
	 * Reveal on scroll
	 * ------------------------------------------------------------------ */
	function initReveal() {
		var targets = document.querySelectorAll( '.kosmiteia-reveal' );

		if ( ! targets.length ) {
			return;
		}

		if ( reduceMotion || ! ( 'IntersectionObserver' in window ) ) {
			targets.forEach( function ( target ) {
				target.classList.add( 'is-revealed' );
			} );

			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						entry.target.classList.add( 'is-revealed' );
						observer.unobserve( entry.target );
					}
				} );
			},
			{ rootMargin: '0px 0px -10% 0px', threshold: 0.1 }
		);

		targets.forEach( function ( target ) {
			observer.observe( target );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Ομαλή κύλιση + μεταφορά focus (π.χ. κουμπί "scroll" στο hero)
	 * ------------------------------------------------------------------ */
	function initSmoothScroll() {
		document.addEventListener( 'click', function ( event ) {
			var link = event.target.closest( 'a[href^="#"]' );

			if ( ! link ) {
				return;
			}

			var hash = link.getAttribute( 'href' );

			if ( ! hash || hash === '#' ) {
				return;
			}

			var target = document.getElementById( hash.slice( 1 ) );

			if ( ! target ) {
				return;
			}

			event.preventDefault();

			target.scrollIntoView( {
				behavior: reduceMotion ? 'auto' : 'smooth',
				block: 'start'
			} );

			// Το focus ακολουθεί την κύλιση, ώστε η πλοήγηση με πληκτρολόγιο
			// να συνεχίζει από τη σωστή θέση.
			if ( ! target.hasAttribute( 'tabindex' ) ) {
				target.setAttribute( 'tabindex', '-1' );
			}

			target.focus( { preventScroll: true } );

			if ( history.pushState ) {
				history.pushState( null, '', hash );
			}
		} );
	}

	/* ---------------------------------------------------------------------
	 * Φίλτρα ανακοινώσεων
	 *
	 * Χωρίς JavaScript η φόρμα δουλεύει κανονικά με το κουμπί "Φιλτράρισμα".
	 * Με JavaScript: τα άδεια πεδία δεν μπαίνουν στο URL (μένει σύντομο και
	 * κοινοποιήσιμο) και η αλλαγή σε ένα <select> υποβάλλει αμέσως τη φόρμα.
	 * ------------------------------------------------------------------ */
	function initFilters() {
		var forms = document.querySelectorAll( '.kosmiteia-filters' );

		if ( ! forms.length ) {
			return;
		}

		Array.prototype.forEach.call( forms, function ( form ) {
			form.addEventListener( 'submit', function () {
				var emptied = [];

				Array.prototype.forEach.call(
					form.querySelectorAll( 'input[type="search"], select' ),
					function ( field ) {
						if ( '' === field.value ) {
							field.disabled = true;
							emptied.push( field );
						}
					}
				);

				// Επαναφορά μόλις φύγει η υποβολή, ώστε η φόρμα να είναι
				// λειτουργική αν ο επισκέπτης γυρίσει πίσω (bfcache).
				window.setTimeout( function () {
					emptied.forEach( function ( field ) {
						field.disabled = false;
					} );
				}, 100 );
			} );

			Array.prototype.forEach.call( form.querySelectorAll( 'select' ), function ( select ) {
				select.addEventListener( 'change', function () {
					if ( 'function' === typeof form.requestSubmit ) {
						form.requestSubmit();
					} else {
						form.submit();
					}
				} );
			} );
		} );
	}

	/* ---------------------------------------------------------------------
	 * Υπομενού στο κινητό: βελάκι που ανοίγει/κλείνει με κλικ ή tap
	 *
	 * Μέσα στο overlay ο πυρήνας δείχνει όλα τα υπομενού ανοιχτά. Εδώ τα
	 * κλείνουμε και τα ανοίγουμε με το βελάκι - ο σύνδεσμος του γονέα
	 * παραμένει σύνδεσμος, όπως στην επιφάνεια εργασίας.
	 * ------------------------------------------------------------------ */
	function initOverlaySubmenus() {
		var parents = document.querySelectorAll(
			'.wp-block-navigation__responsive-container .wp-block-navigation-item.has-child'
		);

		if ( ! parents.length ) {
			return;
		}

		Array.prototype.forEach.call( parents, function ( item ) {
			var toggle = item.querySelector( ':scope > .wp-block-navigation__submenu-icon' );
			var submenu = item.querySelector( ':scope > .wp-block-navigation__submenu-container' );

			if ( ! toggle || ! submenu || toggle.dataset.kosmiteiaBound ) {
				return;
			}

			toggle.dataset.kosmiteiaBound = '1';
			toggle.setAttribute( 'aria-expanded', 'false' );

			toggle.addEventListener( 'click', function () {
				// Στην επιφάνεια εργασίας το υπομενού το χειρίζεται ο πυρήνας.
				if ( ! item.closest( '.is-menu-open' ) ) {
					return;
				}

				var open = item.classList.toggle( 'is-kosmiteia-open' );
				toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
		} );

		// Με το κλείσιμο του μενού όλα επιστρέφουν κλειστά.
		document.addEventListener( 'click', function ( event ) {
			if ( ! event.target.closest( '.wp-block-navigation__responsive-container-close' ) ) {
				return;
			}

			Array.prototype.forEach.call( parents, function ( item ) {
				item.classList.remove( 'is-kosmiteia-open' );

				var toggle = item.querySelector( ':scope > .wp-block-navigation__submenu-icon' );

				if ( toggle ) {
					toggle.setAttribute( 'aria-expanded', 'false' );
				}
			} );
		} );
	}

	function init() {
		initHeader();
		initReveal();
		initSmoothScroll();
		initFilters();
		initOverlaySubmenus();
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
} )();
