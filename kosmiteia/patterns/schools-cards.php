<?php
/**
 * Title: Σχολές σε κάρτες (3 στήλες)
 * Slug: kosmiteia/schools-cards
 * Categories: kosmiteia, posts
 * Description: Δυναμική λίστα Σχολών σε κάρτες με φωτογραφία, τίτλο, περίληψη και σύνδεσμο.
 * Keywords: schools, cards, grid
 *
 * @package Kosmiteia
 */

?>
<!-- wp:group {"tagName":"section","anchor":"sxoles","className":"kosmiteia-reveal","align":"full","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull kosmiteia-reveal" id="sxoles" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:heading {"level":2,"className":"is-style-kosmiteia-underline","fontSize":"x-large"} -->
		<h2 class="wp-block-heading is-style-kosmiteia-underline has-x-large-font-size"><?php esc_html_e( 'Οι Σχολές μας', 'kosmiteia' ); ?></h2>
		<!-- /wp:heading -->

		<!-- wp:paragraph -->
		<p><?php esc_html_e( 'Τρεις Σχολές με διακριτή ταυτότητα, κοινό στόχο την ποιοτική εκπαίδευση και την έρευνα.', 'kosmiteia' ); ?></p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->

	<!-- wp:query {"query":{"perPage":3,"pages":0,"offset":0,"postType":"kosm_school","order":"asc","orderBy":"title","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":null,"parents":[]},"align":"wide","layout":{"type":"default"}} -->
	<div class="wp-block-query alignwide">
		<!-- wp:post-template {"className":"is-style-kosmiteia-cards","style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"grid","columnCount":3}} -->
			<!-- wp:post-featured-image {"isLink":true,"aspectRatio":"3/2","className":"is-style-kosmiteia-zoom"} /-->

			<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","right":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
				<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"large"} /-->

				<!-- wp:post-excerpt {"excerptLength":22,"fontSize":"small"} /-->

				<!-- wp:read-more {"content":"<?php esc_attr_e( 'Δείτε τη Σχολή', 'kosmiteia' ); ?>","fontSize":"small"} /-->
			</div>
			<!-- /wp:group -->
		<!-- /wp:post-template -->

		<!-- wp:query-no-results -->
		<!-- wp:paragraph {"align":"center"} -->
		<p class="has-text-align-center"><?php esc_html_e( 'Δεν έχουν καταχωριστεί ακόμη Σχολές. Προσθέστε την πρώτη από τον πίνακα ελέγχου:', 'kosmiteia' ); ?> <strong><?php esc_html_e( 'Σχολές → Προσθήκη νέας', 'kosmiteia' ); ?></strong>.</p>
		<!-- /wp:paragraph -->
		<!-- /wp:query-no-results -->
	</div>
	<!-- /wp:query -->
</section>
<!-- /wp:group -->
