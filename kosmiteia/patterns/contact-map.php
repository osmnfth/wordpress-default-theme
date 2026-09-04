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

			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"kosmiteia/option","args":{"key":"contact_address"}}}}} -->
			<p><?php esc_html_e( 'Διεύθυνση', 'kosmiteia' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"kosmiteia/option","args":{"key":"contact_hours_label"}}}}} -->
			<p><?php esc_html_e( 'Ωράριο', 'kosmiteia' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:paragraph {"metadata":{"bindings":{"content":{"source":"kosmiteia/option","args":{"key":"contact_phone_label"}}}}} -->
			<p><?php esc_html_e( 'Τηλέφωνο', 'kosmiteia' ); ?></p>
			<!-- /wp:paragraph -->

			<!-- wp:list -->
			<ul class="wp-block-list">

				<!-- wp:list-item -->
				<li><?php esc_html_e( 'Μέσα μεταφοράς: αστικές γραμμές και στάση μετρό στην είσοδο της Πανεπιστημιούπολης', 'kosmiteia' ); ?></li>
				<!-- /wp:list-item -->
			</ul>
			<!-- /wp:list -->
		</div>
		<!-- /wp:column -->

		<!-- wp:column -->
		<div class="wp-block-column">
			<!-- wp:kosmiteia/map {"lat":<?php echo (float) ( function_exists( 'kosmiteia_option' ) ? kosmiteia_option( 'map_lat', 41.1226 ) : 41.1226 ); ?>,"lng":<?php echo (float) ( function_exists( 'kosmiteia_option' ) ? kosmiteia_option( 'map_lng', 25.4064 ) : 25.4064 ); ?>,"zoom":<?php echo (int) ( function_exists( 'kosmiteia_option' ) ? kosmiteia_option( 'map_zoom', 16 ) : 16 ); ?>,"height":420,"markerTitle":"<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>","markerAddress":"<?php echo esc_attr( function_exists( 'kosmiteia_option' ) ? kosmiteia_option( 'contact_address' ) : '' ); ?>"} /-->
		</div>
		<!-- /wp:column -->
	</div>
	<!-- /wp:columns -->
</section>
<!-- /wp:group -->
