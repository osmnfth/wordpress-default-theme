<?php
/**
 * Title: Μήνυμα Κοσμήτορα (κάρτα με φωτογραφία)
 * Slug: kosmiteia/dean-message
 * Categories: kosmiteia, about
 * Description: Κάρτα πλήρους πλάτους με τη φωτογραφία του Κοσμήτορα, σύντομο χαιρετισμό, υπογραφή και κουμπί «Διαβάστε περισσότερα».
 * Keywords: dean, message, welcome
 *
 * @package Kosmiteia
 */

?>
<!-- wp:group {"tagName":"section","anchor":"minyma-kosmitora","className":"kosmiteia-reveal","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"}}},"backgroundColor":"accent-4","layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull has-accent-4-background-color has-background" id="minyma-kosmitora" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">
	<!-- wp:group {"align":"wide","className":"kosmiteia-dean is-style-kosmiteia-card","style":{"spacing":{"blockGap":"0"}},"layout":{"type":"default"}} -->
	<div class="wp-block-group alignwide kosmiteia-dean is-style-kosmiteia-card">
		<!-- wp:columns {"isStackedOnMobile":true,"style":{"spacing":{"blockGap":{"top":"0","left":"0"}}}} -->
		<div class="wp-block-columns">
			<!-- wp:column {"width":"34%"} -->
			<div class="wp-block-column" style="flex-basis:34%">
				<!-- wp:cover {"overlayColor":"accent-1","dimRatio":100,"isUserOverlayColor":true,"minHeight":360,"minHeightUnit":"px","className":"kosmiteia-dean__photo","layout":{"type":"constrained"}} -->
				<div class="wp-block-cover kosmiteia-dean__photo" style="min-height:360px"><span aria-hidden="true" class="wp-block-cover__background has-accent-1-background-color has-background-dim-100 has-background-dim"></span><div class="wp-block-cover__inner-container"></div></div>
				<!-- /wp:cover -->
			</div>
			<!-- /wp:column -->

			<!-- wp:column {"width":"66%"} -->
			<div class="wp-block-column" style="flex-basis:66%">
				<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|60","right":"var:preset|spacing|60","bottom":"var:preset|spacing|60","left":"var:preset|spacing|60"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained"}} -->
				<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--60);padding-right:var(--wp--preset--spacing--60);padding-bottom:var(--wp--preset--spacing--60);padding-left:var(--wp--preset--spacing--60)">
					<!-- wp:paragraph {"className":"kosmiteia-eyebrow","textColor":"accent-3","fontSize":"small"} -->
					<p class="kosmiteia-eyebrow has-accent-3-color has-text-color has-small-font-size"><?php esc_html_e( 'Μήνυμα Κοσμήτορα', 'kosmiteia' ); ?></p>
					<!-- /wp:paragraph -->

					<!-- wp:heading {"level":2,"className":"is-style-kosmiteia-underline","fontSize":"x-large"} -->
					<h2 class="wp-block-heading is-style-kosmiteia-underline has-x-large-font-size"><?php esc_html_e( 'Καλώς ήρθατε στην Κοσμητεία', 'kosmiteia' ); ?></h2>
					<!-- /wp:heading -->

					<!-- wp:paragraph -->
					<p><?php esc_html_e( 'Σύντομο απόσπασμα από τον χαιρετισμό του Κοσμήτορα προς τη φοιτητική και την ακαδημαϊκή κοινότητα. Το πλήρες κείμενο βρίσκεται στη σελίδα «Μήνυμα Κοσμήτορα».', 'kosmiteia' ); ?></p>
					<!-- /wp:paragraph -->

					<!-- wp:group {"className":"kosmiteia-dean__signature","style":{"spacing":{"blockGap":"0","margin":{"top":"var:preset|spacing|30"}}},"layout":{"type":"constrained"}} -->
					<div class="wp-block-group kosmiteia-dean__signature" style="margin-top:var(--wp--preset--spacing--30)">
						<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"kosmiteia/option","args":{"key":"dean_name","fallback":"<?php esc_attr_e( 'Ονοματεπώνυμο Κοσμήτορα', 'kosmiteia' ); ?>"}}}},"className":"kosmiteia-dean__name"} -->
						<p class="kosmiteia-dean__name"><?php esc_html_e( 'Ονοματεπώνυμο Κοσμήτορα', 'kosmiteia' ); ?></p>
						<!-- /wp:paragraph -->

						<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"kosmiteia/option","args":{"key":"dean_title","fallback":"<?php esc_attr_e( 'Κοσμήτορας', 'kosmiteia' ); ?>"}}}},"fontSize":"small"} -->
						<p class="has-small-font-size"><?php esc_html_e( 'Κοσμήτορας', 'kosmiteia' ); ?></p>
						<!-- /wp:paragraph -->
					</div>
					<!-- /wp:group -->

					<!-- wp:buttons {"style":{"spacing":{"margin":{"top":"var:preset|spacing|40"}}},"layout":{"type":"flex"}} -->
					<div class="wp-block-buttons" style="margin-top:var(--wp--preset--spacing--40)">
						<!-- wp:button {"backgroundColor":"accent-1","textColor":"base"} -->
						<div class="wp-block-button"><a class="wp-block-button__link has-base-color has-accent-1-background-color has-text-color has-background wp-element-button" href="{{url_dean}}"><?php esc_html_e( 'Διαβάστε περισσότερα', 'kosmiteia' ); ?></a></div>
						<!-- /wp:button -->
					</div>
					<!-- /wp:buttons -->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:column -->
		</div>
		<!-- /wp:columns -->
	</div>
	<!-- /wp:group -->
</section>
<!-- /wp:group -->
