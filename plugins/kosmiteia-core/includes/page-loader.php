<?php
/**
 * Οθόνη φόρτωσης (loader) για αργές συνδέσεις.
 *
 * Λευκή οθόνη σε όλο το παράθυρο, με το λογότυπο στο κέντρο και διακριτική
 * κίνηση, όσο φορτώνει η σελίδα. Εμφανίζεται μόνο όταν η φόρτωση αργεί
 * περισσότερο από το όριο των Ρυθμίσεων (προεπιλογή 350 ms), ώστε σε γρήγορη
 * σύνδεση να μη «αναβοσβήνει», και - στην προεπιλογή - μόνο σε κινητά.
 *
 * Το CSS και το JavaScript μπαίνουν inline: σε αργή σύνδεση ένα επιπλέον
 * αρχείο θα έφτανε αργότερα από τη σελίδα που θέλει να καλύψει.
 *
 * Χωρίς JavaScript δεν εμφανίζεται τίποτα - η οθόνη ξεκινά κρυφή και μόνο το
 * script τη δείχνει, οπότε δεν υπάρχει περίπτωση να «κολλήσει» πάνω από το
 * περιεχόμενο.
 *
 * @package Kosmiteia_Core
 */

defined( 'ABSPATH' ) || exit;

/**
 * Τα στοιχεία της οθόνης φόρτωσης, ή κενός πίνακας όταν δεν πρέπει να μπει.
 *
 * @return array
 */
function kosmiteia_page_loader() {
	static $config = null;

	if ( null !== $config ) {
		return $config;
	}

	$config = array();

	if ( is_admin() || ! kosmiteia_option( 'loader_enable', 1 ) ) {
		return $config;
	}

	if ( is_embed() || is_feed() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return $config;
	}

	$image = (int) kosmiteia_option( 'loader_image', 0 );

	if ( ! $image || ! wp_attachment_is_image( $image ) ) {
		$image = (int) get_theme_mod( 'custom_logo' );
	}

	$config = array(
		'image' => $image && wp_attachment_is_image( $image ) ? $image : 0,
		'scope' => 'all' === kosmiteia_option( 'loader_scope', 'mobile' ) ? 'all' : 'mobile',
		'delay' => min( 3000, max( 0, (int) kosmiteia_option( 'loader_delay', 350 ) ) ),
	);

	/**
	 * Φίλτρο για αλλαγή ή απενεργοποίηση της οθόνης φόρτωσης από κώδικα.
	 *
	 * Επιστρέφοντας κενό πίνακα, η οθόνη δεν εμφανίζεται.
	 *
	 * @param array $config Τα στοιχεία της οθόνης.
	 */
	$config = (array) apply_filters( 'kosmiteia_page_loader', $config );

	return $config;
}

/**
 * Το στυλ της οθόνης, inline στο <head>.
 */
function kosmiteia_page_loader_styles() {
	$config = kosmiteia_page_loader();

	if ( ! $config ) {
		return;
	}

	$css = '
.kosmiteia-loader{position:fixed;inset:0;z-index:2147483000;display:flex;align-items:center;justify-content:center;background:#fff;opacity:0;visibility:hidden;transition:opacity 200ms ease,visibility 200ms ease}
.kosmiteia-loader.is-visible{opacity:1;visibility:visible}
.kosmiteia-loader__inner{display:flex;flex-direction:column;align-items:center;gap:1.5rem;padding:1.5rem;max-width:80vw}
.kosmiteia-loader__logo{display:block;width:auto;height:auto;max-width:min(260px,60vw);max-height:120px;animation:kosmiteia-loader-pulse 1.4s ease-in-out infinite}
.kosmiteia-loader__mark{color:#0b3d91;animation:kosmiteia-loader-pulse 1.4s ease-in-out infinite}
.kosmiteia-loader__bar{position:relative;overflow:hidden;width:140px;height:3px;border-radius:999px;background:rgba(14,26,43,.12)}
.kosmiteia-loader__bar::after{content:"";position:absolute;inset:0;width:40%;border-radius:999px;background:#0b3d91;animation:kosmiteia-loader-slide 1.2s ease-in-out infinite}
@keyframes kosmiteia-loader-pulse{0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.06);opacity:.75}}
@keyframes kosmiteia-loader-slide{0%{transform:translateX(-100%)}100%{transform:translateX(350%)}}
@media (prefers-reduced-motion:reduce){.kosmiteia-loader__logo,.kosmiteia-loader__mark,.kosmiteia-loader__bar::after{animation:none}.kosmiteia-loader__bar::after{width:100%}}
@media print{.kosmiteia-loader{display:none}}';

	if ( 'mobile' === $config['scope'] ) {
		$css .= '
@media (min-width:782px){.kosmiteia-loader{display:none}}';
	}

	printf( "<style id=\"kosmiteia-loader-css\">%s</style>\n", $css ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Σταθερό CSS, χωρίς δεδομένα χρήστη.
}
add_action( 'wp_head', 'kosmiteia_page_loader_styles' );

/**
 * Η οθόνη και το script της, αμέσως μετά το άνοιγμα του <body>.
 *
 * Το markup μπαίνει πρώτο ώστε να καλύπτει τη σελίδα από την αρχή της
 * φόρτωσης· το script είναι σύγχρονο (χωρίς defer) για τον ίδιο λόγο.
 */
function kosmiteia_page_loader_render() {
	$config = kosmiteia_page_loader();

	if ( ! $config ) {
		return;
	}

	$logo = $config['image']
		? wp_get_attachment_image(
			$config['image'],
			'medium',
			false,
			array(
				'alt'           => '',
				'class'         => 'kosmiteia-loader__logo',
				'decoding'      => 'sync',
				'fetchpriority' => 'high',
			)
		)
		: '';

	?>
	<div class="kosmiteia-loader" id="kosmiteia-loader" aria-hidden="true">
		<div class="kosmiteia-loader__inner">
			<?php if ( $logo ) : ?>
				<?php echo $logo; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- έξοδος του wp_get_attachment_image(). ?>
			<?php else : ?>
				<span class="kosmiteia-loader__mark">
					<svg viewBox="0 0 24 24" width="56" height="56" focusable="false" aria-hidden="true"><path fill="currentColor" d="M12 3 2 8l10 5 8-4v6h2V8L12 3ZM6 13.2V17c0 1.7 2.7 3 6 3s6-1.3 6-3v-3.8l-6 3-6-3Z"/></svg>
				</span>
			<?php endif; ?>

			<span class="kosmiteia-loader__bar"></span>
		</div>
	</div>
	<script id="kosmiteia-loader-js">
	( function () {
		var el = document.getElementById( 'kosmiteia-loader' );

		if ( ! el || ! el.classList ) {
			return;
		}

		var delay = <?php echo (int) $config['delay']; ?>;
		var maxWait = 10000;
		var timer = null;
		var safety = null;

		function show() {
			el.classList.add( 'is-visible' );

			// Ασφαλιστική δικλίδα: ό,τι κι αν συμβεί, η οθόνη φεύγει.
			window.clearTimeout( safety );
			safety = window.setTimeout( hide, maxWait );
		}

		function hide() {
			window.clearTimeout( timer );
			window.clearTimeout( safety );
			el.classList.remove( 'is-visible' );
		}

		function arm() {
			window.clearTimeout( timer );
			timer = window.setTimeout( show, delay );
		}

		arm();

		// Μόλις το HTML της νέας σελίδας είναι έτοιμο, η οθόνη φεύγει - δεν
		// περιμένουμε τις εικόνες, το περιεχόμενο φαίνεται ήδη.
		if ( 'loading' === document.readyState ) {
			document.addEventListener( 'DOMContentLoaded', hide );
		} else {
			hide();
		}

		window.addEventListener( 'load', hide );

		// Επιστροφή με το «πίσω» (bfcache): η σελίδα είναι ήδη εκεί.
		window.addEventListener( 'pageshow', function ( event ) {
			if ( event.persisted ) {
				hide();
			}
		} );

		// Φόρτωση επόμενης σελίδας: σύνδεσμοι και φόρμες του ίδιου ιστότοπου.
		document.addEventListener( 'click', function ( event ) {
			if ( event.defaultPrevented || 0 !== event.button || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey ) {
				return;
			}

			var link = event.target && event.target.closest ? event.target.closest( 'a[href]' ) : null;

			if ( ! link || link.hasAttribute( 'download' ) || ( link.target && '_self' !== link.target ) ) {
				return;
			}

			var href = link.getAttribute( 'href' ) || '';

			if ( ! href || '#' === href.charAt( 0 ) || /^(mailto|tel|javascript):/i.test( href ) ) {
				return;
			}

			if ( link.origin && link.origin !== window.location.origin ) {
				return;
			}

			// Σύνδεσμος προς την ίδια σελίδα (π.χ. μόνο με #anchor).
			if ( link.href.split( '#' )[ 0 ] === window.location.href.split( '#' )[ 0 ] ) {
				return;
			}

			arm();
		}, true );

		document.addEventListener( 'submit', function ( event ) {
			if ( ! event.defaultPrevented ) {
				arm();
			}
		}, true );
	} )();
	</script>
	<?php
}
add_action( 'wp_body_open', 'kosmiteia_page_loader_render' );
