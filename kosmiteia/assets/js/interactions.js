/**
 * Kosmiteia - small interface improvements.
 *
 * - sticky header that "shrinks" on scroll
 * - reveal sections with IntersectionObserver (reveal on scroll)
 * - smooth scrolling with proper focus management for keyboard/screen readers
 *
 * Everything respects prefers-reduced-motion and does not affect the page's
 * functionality if the script is not executed.
 */
( function () {
	'use strict';

	var root = document.documentElement;
	var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

	// Flag indicating that JS is running - the CSS animations depend on this.
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
	 * Smooth scrolling + focus management (e.g., "scroll" button in the hero)
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

			// The focus follows the scroll, so that keyboard/screen reader navigation
			// continues from the correct position.
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
	 * Filters for announcements
	 *
	 * Without JavaScript, the form works normally with the "Filter" button.
	 * With JavaScript: empty fields are not included in the URL (keeping it short and
	 * shareable) and changing a <select> immediately submits the form.
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

				// Reset the form fields once the submission is done, so the form is
				// functional if the visitor goes back (bfcache).
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
	 * Overlay submenus on mobile: toggle icons that open/close with click or tap
	 *
	 * Inside the overlay, the core displays all submenus open. Here we
	 * close them and open them with the toggle icon - the parent link
	 * remains a link, as in the desktop interface.
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
				// In the desktop interface, the core handles the submenu.
				if ( ! item.closest( '.is-menu-open' ) ) {
					return;
				}

				var open = item.classList.toggle( 'is-kosmiteia-open' );
				toggle.setAttribute( 'aria-expanded', open ? 'true' : 'false' );
			} );
		} );

		// With the menu closing, all submenus return to their closed state.
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
