( function () {
	'use strict';

	var l10n = window.kosmiteiaSliderL10n || {};

	function t( key, fallback ) {
		return l10n[ key ] || fallback;
	}

	function sprintf( template, values ) {
		return String( template )
			.replace( '%1$s', values[ 0 ] )
			.replace( '%2$s', values[ 1 ] )
			.replace( '%s', values[ 0 ] );
	}

	var ICONS = {
		prev: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M15.4 7.4 14 6l-6 6 6 6 1.4-1.4-4.6-4.6z"/></svg>',
		next: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M8.6 16.6 10 18l6-6-6-6-1.4 1.4 4.6 4.6z"/></svg>',
		pause: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M6 5h4v14H6zM14 5h4v14h-4z"/></svg>',
		play: '<svg viewBox="0 0 24 24" aria-hidden="true" focusable="false"><path d="M8 5v14l11-7z"/></svg>'
	};

	function Slider( root ) {
		var track = root.querySelector( '.kosmiteia-slider__track' );

		if ( ! track ) {
			return;
		}

		var slides = Array.prototype.filter.call( track.children, function ( node ) {
			return node.nodeType === 1;
		} );

		if ( slides.length < 2 ) {
			return;
		}

		var reduceMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;
		var autoplay = root.getAttribute( 'data-autoplay' ) === 'true' && ! reduceMotion;
		var interval = parseInt( root.getAttribute( 'data-interval' ), 10 ) || 6000;
		var showArrows = root.getAttribute( 'data-arrows' ) !== 'false';
		var showDots = root.getAttribute( 'data-dots' ) !== 'false';
		var label = root.getAttribute( 'data-label' ) || t( 'carousel', 'Carousel' );

		var index = 0;
		var timer = null;
		var isPlaying = autoplay;

		root.classList.add( 'is-initialized' );
		root.setAttribute( 'role', 'region' );
		root.setAttribute( 'aria-roledescription', 'carousel' );
		root.setAttribute( 'aria-label', label );

		track.setAttribute( 'aria-live', autoplay ? 'off' : 'polite' );

		slides.forEach( function ( slide, i ) {
			slide.classList.add( 'kosmiteia-slider__slide' );
			slide.setAttribute( 'role', 'group' );
			slide.setAttribute( 'aria-roledescription', 'slide' );
			slide.setAttribute(
				'aria-label',
				sprintf( t( 'slideLabel', 'Slide %1$s of %2$s' ), [ i + 1, slides.length ] )
			);
		} );

		var controls = document.createElement( 'div' );
		controls.className = 'kosmiteia-slider__controls';

		var dots = [];

		if ( showDots ) {
			var dotList = document.createElement( 'ul' );
			dotList.className = 'kosmiteia-slider__dots';

			slides.forEach( function ( slide, i ) {
				var item = document.createElement( 'li' );
				var dot = document.createElement( 'button' );

				dot.type = 'button';
				dot.className = 'kosmiteia-slider__dot';
				dot.setAttribute(
					'aria-label',
					sprintf( t( 'goToSlide', 'Go to slide %s' ), [ i + 1 ] )
				);
				dot.addEventListener( 'click', function () {
					stop();
					goTo( i, true );
				} );

				item.appendChild( dot );
				dotList.appendChild( item );
				dots.push( dot );
			} );

			controls.appendChild( dotList );
		}

		var toggle = null;

		if ( autoplay ) {
			toggle = document.createElement( 'button' );
			toggle.type = 'button';
			toggle.className = 'kosmiteia-slider__toggle';
			toggle.innerHTML = ICONS.pause;
			toggle.setAttribute( 'aria-label', t( 'pause', 'Pause' ) );
			toggle.addEventListener( 'click', function () {
				if ( isPlaying ) {
					stop();
					toggle.innerHTML = ICONS.play;
					toggle.setAttribute( 'aria-label', t( 'play', 'Play' ) );
					track.setAttribute( 'aria-live', 'polite' );
				} else {
					start();
					toggle.innerHTML = ICONS.pause;
					toggle.setAttribute( 'aria-label', t( 'pause', 'Pause' ) );
					track.setAttribute( 'aria-live', 'off' );
				}
			} );

			/* controls.appendChild( toggle ); */
		}

		if ( controls.children.length ) {
			root.appendChild( controls );
		}

		if ( showArrows ) {
			[ 'prev', 'next' ].forEach( function ( direction ) {
				var button = document.createElement( 'button' );

				button.type = 'button';
				button.className = 'kosmiteia-slider__arrow kosmiteia-slider__arrow--' + direction;
				button.innerHTML = ICONS[ direction ];
				button.setAttribute(
					'aria-label',
					direction === 'prev' ? t( 'previous', 'Previous slide' ) : t( 'next', 'Next slide' )
				);
				button.addEventListener( 'click', function () {
					stop();
					goTo( direction === 'prev' ? index - 1 : index + 1, true );
				} );

				root.appendChild( button );
			} );
		}

		function goTo( next, focusSlide ) {
			var total = slides.length;

			index = ( ( next % total ) + total ) % total;

			slides.forEach( function ( slide, i ) {
				var isActive = i === index;

				slide.classList.toggle( 'is-active', isActive );
				slide.setAttribute( 'aria-hidden', isActive ? 'false' : 'true' );

				if ( 'inert' in HTMLElement.prototype ) {
					slide.inert = ! isActive;
				}
			} );

			if ( root.getAttribute( 'data-effect' ) !== 'fade' ) {
				track.style.transform = 'translateX(-' + index * 100 + '%)';
			}

			dots.forEach( function ( dot, i ) {
				if ( i === index ) {
					dot.setAttribute( 'aria-current', 'true' );
				} else {
					dot.removeAttribute( 'aria-current' );
				}
			} );

			if ( focusSlide ) {
				slides[ index ].setAttribute( 'tabindex', '-1' );
			}
		}

		function start() {
			if ( timer || slides.length < 2 ) {
				return;
			}

			isPlaying = true;
			timer = window.setInterval( function () {
				goTo( index + 1 );
			}, interval );
		}

		function stop() {
			isPlaying = false;
			window.clearInterval( timer );
			timer = null;
		}

		root.addEventListener( 'keydown', function ( event ) {
			if ( event.key === 'ArrowLeft' ) {
				stop();
				goTo( index - 1, true );
			} else if ( event.key === 'ArrowRight' ) {
				stop();
				goTo( index + 1, true );
			}
		} );

		if ( autoplay ) {
			root.addEventListener( 'mouseenter', stop );
			root.addEventListener( 'mouseleave', function () {
				if ( ! toggle || toggle.getAttribute( 'aria-label' ) === t( 'pause', 'Pause' ) ) {
					start();
				}
			} );
			root.addEventListener( 'focusin', stop );

			document.addEventListener( 'visibilitychange', function () {
				if ( document.hidden ) {
					stop();
				}
			} );
		}

		var startX = null;

		root.addEventListener( 'touchstart', function ( event ) {
			startX = event.changedTouches[ 0 ].clientX;
		}, { passive: true } );

		root.addEventListener( 'touchend', function ( event ) {
			if ( startX === null ) {
				return;
			}

			var delta = event.changedTouches[ 0 ].clientX - startX;

			if ( Math.abs( delta ) > 50 ) {
				stop();
				goTo( delta > 0 ? index - 1 : index + 1 );
			}

			startX = null;
		}, { passive: true } );

		goTo( 0 );

		if ( autoplay ) {
			start();
		}
	}

	function init() {
		document.querySelectorAll( '.kosmiteia-slider' ).forEach( function ( root ) {
			if ( ! root.classList.contains( 'is-editor-preview' ) ) {
				Slider( root );
			}
		} );
	}

	if ( document.readyState !== 'loading' ) {
		init();
	} else {
		document.addEventListener( 'DOMContentLoaded', init );
	}
} )();
