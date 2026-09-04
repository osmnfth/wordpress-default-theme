<?php
/**
 * Title: Subfooter 4 στηλών
 * Slug: kosmiteia/subfooter-columns
 * Categories: kosmiteia, footer
 * Block Types: core/template-part/footer
 * Description: Υποσέλιδο με λογότυπο και μότο, δύο μενού, στοιχεία επικοινωνίας και κοινωνικά δίκτυα.
 * Keywords: footer, columns, contact
 *
 * @package Kosmiteia
 */

?>
<!-- wp:group {"className":"kosmiteia-subfooter","align":"full","backgroundColor":"contrast","textColor":"base","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|50"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained"}} -->
<div class="wp-block-group alignfull kosmiteia-subfooter has-base-color has-contrast-background-color has-text-color has-background" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--50)">
	<!-- wp:columns {"align":"wide","className":"is-style-kosmiteia-subfooter"} -->
	<div class="wp-block-columns alignwide is-style-kosmiteia-subfooter">
		<!-- wp:column {"width":"32%"} -->
		<div class="wp-block-column" style="flex-basis:32%">
			<!-- wp:site-logo {"width":200} /-->

			<!-- wp:site-title {"level":2,"fontSize":"large"} /-->

			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"kosmiteia/option","args":{"key":"tagline"}}}},"fontSize":"small"} -->
			<p class="has-small-font-size"><?php esc_html_e( 'Παιδεία, έρευνα και κοινωνική προσφορά. Η Κοσμητεία συντονίζει τις Σχολές, τα προγράμματα σπουδών και την ακαδημαϊκή κοινότητα.', 'kosmiteia' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":2,"fontSize":"medium"} -->
			<h2 class="wp-block-heading has-medium-font-size"><?php esc_html_e( 'Πλοήγηση', 'kosmiteia' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:navigation {"overlayMenu":"never","ariaLabel":"<?php esc_attr_e( 'Μενού υποσέλιδου 1', 'kosmiteia' ); ?>","style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"fontSize":"small","layout":{"type":"flex","orientation":"vertical"}} -->
				<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Σχολές', 'kosmiteia' ); ?>","url":"{{url_schools}}"} /-->
				<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Ανακοινώσεις', 'kosmiteia' ); ?>","url":"{{url_announcements}}"} /-->
				<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Μεταπτυχιακά', 'kosmiteia' ); ?>","url":"{{url_programs}}"} /-->
				<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Η Κοσμητεία', 'kosmiteia' ); ?>","url":"#"} /-->
			<!-- /wp:navigation -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":2,"fontSize":"medium"} -->
			<h2 class="wp-block-heading has-medium-font-size"><?php esc_html_e( 'Χρήσιμα', 'kosmiteia' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:navigation {"overlayMenu":"never","ariaLabel":"<?php esc_attr_e( 'Μενού υποσέλιδου 2', 'kosmiteia' ); ?>","style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"fontSize":"small","layout":{"type":"flex","orientation":"vertical"}} -->
				<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Φοιτητική μέριμνα', 'kosmiteia' ); ?>","url":"#"} /-->
				<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Κανονισμοί σπουδών', 'kosmiteia' ); ?>","url":"#"} /-->
				<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Προσβασιμότητα', 'kosmiteia' ); ?>","url":"#"} /-->
				<!-- wp:navigation-link {"label":"<?php esc_attr_e( 'Πολιτική απορρήτου', 'kosmiteia' ); ?>","url":"#"} /-->
			<!-- /wp:navigation -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:heading {"level":2,"fontSize":"medium"} -->
			<h2 class="wp-block-heading has-medium-font-size"><?php esc_html_e( 'Επικοινωνία', 'kosmiteia' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"kosmiteia/option","args":{"key":"contact_address"}}}},"fontSize":"small"} -->
			<p class="has-small-font-size"><?php esc_html_e( 'Διεύθυνση', 'kosmiteia' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"kosmiteia/option","args":{"key":"contact_phone_label"}}}},"fontSize":"small"} -->
			<p class="has-small-font-size"><?php esc_html_e( 'Τηλέφωνο', 'kosmiteia' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"kosmiteia/option","args":{"key":"contact_email"}}}},"fontSize":"small"} -->
			<p class="has-small-font-size"><?php esc_html_e( 'Email', 'kosmiteia' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"kosmiteia/option","args":{"key":"contact_hours_label"}}}},"fontSize":"small"} -->
			<p class="has-small-font-size"><?php esc_html_e( 'Ωράριο', 'kosmiteia' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:heading {"level":2,"fontSize":"medium"} -->
			<h2 class="wp-block-heading has-medium-font-size"><?php esc_html_e( 'Ακολουθήστε μας', 'kosmiteia' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:social-links {"openInNewTab":true,"className":"is-style-logos-only","layout":{"type":"flex"}} -->
			<ul class="wp-block-social-links is-style-logos-only"><!-- wp:social-link {"url":"<?php echo esc_url( function_exists( 'kosmiteia_option' ) ? kosmiteia_option( 'social_facebook', '#' ) : '#' ); ?>","service":"facebook","label":"Facebook"} /-->

			<!-- wp:social-link {"url":"<?php echo esc_url( function_exists( 'kosmiteia_option' ) ? kosmiteia_option( 'social_instagram', '#' ) : '#' ); ?>","service":"instagram","label":"Instagram"} /-->

			<!-- wp:social-link {"url":"<?php echo esc_url( function_exists( 'kosmiteia_option' ) ? kosmiteia_option( 'social_linkedin', '#' ) : '#' ); ?>","service":"linkedin","label":"LinkedIn"} /-->

			<!-- wp:social-link {"url":"<?php echo esc_url( function_exists( 'kosmiteia_option' ) ? kosmiteia_option( 'social_youtube', '#' ) : '#' ); ?>","service":"youtube","label":"YouTube"} /--></ul>
			<!-- /wp:social-links -->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->

	<!-- wp:separator {"className":"is-style-wide"} -->
	<hr class="wp-block-separator has-alpha-channel-opacity is-style-wide"/>
	<!-- /wp:separator -->

	<!-- wp:group {"align":"wide","className":"kosmiteia-footer-bottom","layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide kosmiteia-footer-bottom">
		<!-- wp:paragraph {"fontSize":"small"} -->
		<p class="has-small-font-size"><?php esc_html_e( '© {{year}} {{site}}. Με επιφύλαξη παντός δικαιώματος.', 'kosmiteia' ); ?></p>
		<!-- /wp:paragraph -->

		<!-- wp:paragraph {"fontSize":"small"} -->
		<p class="has-small-font-size"><a href="#"><?php esc_html_e( 'Δήλωση προσβασιμότητας', 'kosmiteia' ); ?></a> · <a href="#"><?php esc_html_e( 'Πολιτική απορρήτου', 'kosmiteia' ); ?></a> · <a href="#"><?php esc_html_e( 'Χάρτης ιστότοπου', 'kosmiteia' ); ?></a></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
