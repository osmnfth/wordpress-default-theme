/**
 * Χάρτης Leaflet για το μπλοκ kosmiteia/map.
 *
 * - Ο χάρτης στήνεται μόλις πλησιάσει στο viewport, ώστε να μη ζητούνται
 *   πλακίδια από το OpenStreetMap αν ο επισκέπτης δεν φτάσει ποτέ σε αυτόν.
 * - Αν λείπει η Leaflet ή το JavaScript, μένει ορατή η εναλλακτική εμφάνιση
 *   (διεύθυνση + σύνδεσμος προς τον χάρτη) που έχει ήδη τυπώσει η PHP.
 */
( function () {
	'use strict';

	var settings = window.kosmiteiaMapL10n || {};

	/**
	 * Το περιεχόμενο της πινέζας ως DOM (χωρίς innerHTML).
	 *
	 * @param {string} title   Τίτλος.
	 * @param {string} address Διεύθυνση.
	 * @return {HTMLElement} Στοιχείο για το popup.
	 */
	function popupContent( title, address ) {
		var wrapper = document.createElement( 'div' );
		wrapper.className = 'kosmiteia-map__popup';

		if ( title ) {
			var strong = document.createElement( 'strong' );
			strong.textContent = title;
			wrapper.appendChild( strong );
		}

		if ( address ) {
			var span = document.createElement( 'span' );
			span.textContent = address;
			wrapper.appendChild( span );
		}

		return wrapper;
	}

	/**
	 * Στήσιμο ενός χάρτη.
	 *
	 * @param {HTMLElement} node Το wrapper του μπλοκ.
	 */
	function init( node ) {
		if ( node.dataset.kosmiteiaReady || ! window.L ) {
			return;
		}

		var canvas = node.querySelector( '.kosmiteia-map__canvas' );
		var lat = parseFloat( node.dataset.lat );
		var lng = parseFloat( node.dataset.lng );
		var zoom = parseInt( node.dataset.zoom, 10 );

		if ( ! canvas || isNaN( lat ) || isNaN( lng ) ) {
			return;
		}

		node.dataset.kosmiteiaReady = '1';

		if ( settings.imagePath ) {
			window.L.Icon.Default.imagePath = settings.imagePath;
		}

		// Πρώτα εμφανίζεται ο καμβάς και μετά στήνεται ο χάρτης: η Leaflet
		// χρειάζεται δοχείο με πραγματικές διαστάσεις τη στιγμή της αρχικοποίησης.
		node.classList.add( 'is-ready' );

		var map = window.L.map( canvas, {
			scrollWheelZoom: 'true' === node.dataset.scrollWheelZoom,
			zoomControl: true
		} );

		map.setView( [ lat, lng ], isNaN( zoom ) ? 16 : zoom );

		window.L.tileLayer( settings.tileUrl || 'https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
			maxZoom: 19,
			attribution: settings.attribution || ''
		} ).addTo( map );

		if ( 'true' === node.dataset.marker ) {
			var title = node.dataset.markerTitle || '';
			var address = node.dataset.markerAddress || '';
			var marker = window.L.marker( [ lat, lng ], {
				title: title,
				alt: title || settings.markerAlt || ''
			} ).addTo( map );

			if ( title || address ) {
				marker.bindPopup( popupContent( title, address ) );
			}
		}

		if ( canvas.getAttribute( 'aria-label' ) ) {
			canvas.setAttribute( 'role', 'region' );
		}

		// Ο χάρτης μπορεί να στηθεί ενώ το δοχείο αλλάζει μέγεθος (π.χ. σε στήλες).
		window.setTimeout( function () {
			map.invalidateSize();
		}, 200 );
	}

	/**
	 * Παρακολούθηση όλων των χαρτών της σελίδας.
	 */
	function boot() {
		var maps = document.querySelectorAll( '.kosmiteia-map' );

		if ( ! maps.length ) {
			return;
		}

		if ( ! ( 'IntersectionObserver' in window ) ) {
			Array.prototype.forEach.call( maps, init );
			return;
		}

		var observer = new IntersectionObserver(
			function ( entries ) {
				entries.forEach( function ( entry ) {
					if ( entry.isIntersecting ) {
						init( entry.target );
						observer.unobserve( entry.target );
					}
				} );
			},
			{ rootMargin: '200px' }
		);

		Array.prototype.forEach.call( maps, function ( node ) {
			observer.observe( node );
		} );
	}

	if ( 'loading' === document.readyState ) {
		document.addEventListener( 'DOMContentLoaded', boot );
	} else {
		boot();
	}
} )();
