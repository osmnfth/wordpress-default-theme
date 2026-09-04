<?php
/**
 * Title: Πρόσφατες ανακοινώσεις
 * Slug: kosmiteia/announcements-latest
 * Categories: kosmiteia, posts
 * Description: Οι πιο πρόσφατες ανακοινώσεις με ημερομηνία, κατηγορία και περίληψη.
 * Keywords: announcements, news, posts
 *
 * @package Kosmiteia
 */

?>
<!-- wp:group {"tagName":"section","anchor":"anakoinoseis","className":"kosmiteia-reveal","align":"full","backgroundColor":"accent-4","style":{"spacing":{"padding":{"top":"var:preset|spacing|70","bottom":"var:preset|spacing|70"},"blockGap":"var:preset|spacing|50"}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignfull has-accent-4-background-color has-background kosmiteia-reveal" id="anakoinoseis" style="padding-top:var(--wp--preset--spacing--70);padding-bottom:var(--wp--preset--spacing--70)">
	<!-- wp:group {"align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"bottom"}} -->
	<div class="wp-block-group alignwide">
		<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained","justifyContent":"left"}} -->
		<div class="wp-block-group">
			<!-- wp:heading {"level":2,"className":"is-style-kosmiteia-underline","fontSize":"x-large"} -->
			<h2 class="wp-block-heading is-style-kosmiteia-underline has-x-large-font-size"><?php esc_html_e( 'Πρόσφατες ανακοινώσεις', 'kosmiteia' ); ?></h2>
			<!-- /wp:heading -->

			<!-- wp:paragraph -->
			<p><?php esc_html_e( 'Προκηρύξεις, προθεσμίες και νέα της ακαδημαϊκής κοινότητας.', 'kosmiteia' ); ?></p>
			<!-- /wp:paragraph -->
		</div>
		<!-- /wp:group -->

		<!-- wp:buttons -->
		<div class="wp-block-buttons">
			<!-- wp:button {"className":"is-style-outline"} -->
			<div class="wp-block-button is-style-outline"><a class="wp-block-button__link wp-element-button" href="{{url_announcements}}"><?php esc_html_e( 'Όλες οι ανακοινώσεις', 'kosmiteia' ); ?></a></div>
			<!-- /wp:button -->
		</div>
		<!-- /wp:buttons -->
	</div>
	<!-- /wp:group -->

	<!-- wp:query {"query":{"perPage":4,"pages":0,"offset":0,"postType":"kosm_announcement","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false,"taxQuery":null,"parents":[]},"align":"wide","layout":{"type":"default"}} -->
	<div class="wp-block-query alignwide">
		<!-- wp:post-template {"className":"is-style-kosmiteia-cards","style":{"spacing":{"blockGap":"var:preset|spacing|40"}},"layout":{"type":"grid","columnCount":2}} -->
			<!-- wp:group {"style":{"spacing":{"padding":{"top":"var:preset|spacing|40","right":"var:preset|spacing|40","bottom":"var:preset|spacing|40","left":"var:preset|spacing|40"},"blockGap":"var:preset|spacing|20"}},"layout":{"type":"constrained"}} -->
			<div class="wp-block-group" style="padding-top:var(--wp--preset--spacing--40);padding-right:var(--wp--preset--spacing--40);padding-bottom:var(--wp--preset--spacing--40);padding-left:var(--wp--preset--spacing--40)">
				<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","flexWrap":"wrap","verticalAlignment":"center"}} -->
				<div class="wp-block-group">
					<!-- wp:post-date {"format":"j F Y","isLink":false,"fontSize":"small"} /-->

					<!-- wp:post-terms {"term":"kosm_ann_category","fontSize":"small"} /-->
				</div>
				<!-- /wp:group -->

				<!-- wp:post-title {"level":3,"isLink":true,"fontSize":"large"} /-->

				<!-- wp:post-excerpt {"excerptLength":26,"fontSize":"small"} /-->

				<!-- wp:read-more {"content":"<?php esc_attr_e( 'Διαβάστε την ανακοίνωση', 'kosmiteia' ); ?>","fontSize":"small"} /-->
			</div>
			<!-- /wp:group -->
		<!-- /wp:post-template -->

		<!-- wp:query-no-results -->
		<!-- wp:paragraph {"align":"center"} -->
		<p class="has-text-align-center"><?php esc_html_e( 'Δεν υπάρχουν ανακοινώσεις αυτή τη στιγμή. Προσθέστε την πρώτη από:', 'kosmiteia' ); ?> <strong><?php esc_html_e( 'Ανακοινώσεις → Προσθήκη νέας', 'kosmiteia' ); ?></strong>.</p>
		<!-- /wp:paragraph -->
		<!-- /wp:query-no-results -->
	</div>
	<!-- /wp:query -->
</section>
<!-- /wp:group -->
