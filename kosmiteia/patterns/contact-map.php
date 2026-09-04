<?php
/**
 * Title: Επικοινωνία: στοιχεία και χάρτης
 * Slug: kosmiteia/contact-map
 * Categories: kosmiteia, contact
 * Description: Δύο στήλες με τα στοιχεία της Γραμματείας και διαδραστικό χάρτη Leaflet / OpenStreetMap.
 * Keywords: contact, map, leaflet, επικοινωνία, χάρτης, διεύθυνση
 *
 * @package Kosmiteia
 */

?>
<!-- wp:group {"tagName":"section","className":"kosmiteia-reveal","align":"wide","style":{"spacing":{"blockGap":"var:preset|spacing|40","margin":{"top":"var:preset|spacing|60"}}},"layout":{"type":"constrained"}} -->
<section class="wp-block-group alignwide kosmiteia-reveal" style="margin-top:var(--wp--preset--spacing--60)">
	<!-- wp:heading {"level":2,"className":"is-style-kosmiteia-underline","fontSize":"x-large"} -->
	<h2 class="wp-block-heading is-style-kosmiteia-underline has-x-large-font-size"><?php esc_html_e( 'Πού θα μας βρείτε', 'kosmiteia' ); ?></h2>
	<!-- /wp:heading -->

	<!-- wp:columns {"align":"wide","style":{"spacing":{"blockGap":{"top":"var:preset|spacing|40","left":"var:preset|spacing|50"}}}} -->
	<div class="wp-block-columns alignwide">
		<!-- wp:column {"width":"38%"} -->
		<div class="wp-block-column" style="flex-basis:38%">
			<!-- wp:paragraph -->
			<p><?php esc_html_e( 'Η Γραμματεία της Κοσμητείας στεγάζεται στο Κτίριο Διοίκησης της Πανεπιστημιούπολης. Η είσοδος είναι προσβάσιμη σε άτομα με αναπηρία.', 'kosmiteia' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:list -->
			<ul class="wp-block-list">
				<!-- wp:list-item -->
				<li><?php esc_html_e( 'Διεύθυνση: Πανεπιστημιούπολη, Κτίριο Διοίκησης', 'kosmiteia' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php esc_html_e( 'Ωράριο: Δευτέρα έως Παρασκευή, 09:00-14:00', 'kosmiteia' ); ?></li>
				<!-- /wp:list-item -->

				<!-- wp:list-item -->
				<li><?php esc_html_e( 'Μέσα μεταφοράς: αστικές γραμμές και στάση μετρό στην είσοδο της Πανεπιστημιούπολης', 'kosmiteia' ); ?></li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:kosmiteia/map {"lat":37.9682,"lng":23.783,"zoom":16,"height":420,"markerTitle":"<?php echo esc_attr__( 'Κοσμητεία Σχολών', 'kosmiteia' ); ?>","markerAddress":"<?php echo esc_attr__( 'Πανεπιστημιούπολη, Κτίριο Διοίκησης', 'kosmiteia' ); ?>"} /-->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</section>
<!-- /wp:group -->
