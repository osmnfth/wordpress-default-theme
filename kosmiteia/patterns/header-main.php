<?php
/**
 * Title: Κεφαλίδα με λογότυπο, μενού και γλώσσες
 * Slug: kosmiteia/header-main
 * Categories: kosmiteia, header
 * Block Types: core/template-part/header
 * Description: Κεφαλίδα με λογότυπο, τίτλο ιστότοπου, κύριο μενού και επιλογέα γλώσσας.
 * Keywords: header, logo, navigation, language
 *
 * @package Kosmiteia
 */

?>
<!-- wp:group {"className":"kosmiteia-header","align":"full","layout":{"type":"default"}} -->
<div class="wp-block-group alignfull kosmiteia-header">
	<!-- wp:group {"layout":{"type":"constrained"}} -->
	<div class="wp-block-group">
		<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|20","bottom":"var:preset|spacing|20"},"blockGap":"var:preset|spacing|30"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"space-between","verticalAlignment":"center"}} -->
		<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--20);padding-bottom:var(--wp--preset--spacing--20)">
			<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","flexWrap":"nowrap","verticalAlignment":"center"}} -->
			<div class="wp-block-group">
				<!-- wp:site-logo {"width":180} /-->

				<!-- wp:group {"style":{"spacing":{"blockGap":"0"}},"layout":{"type":"constrained"}} -->
				<div class="wp-block-group">
					<!-- wp:site-title {"level":0,"fontSize":"medium"} /-->
					<!-- wp:site-tagline {"fontSize":"small"} /-->
				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->

			<!-- wp:group {"style":{"spacing":{"blockGap":"var:preset|spacing|20"}},"layout":{"type":"flex","flexWrap":"wrap","justifyContent":"right","verticalAlignment":"center"}} -->
			<div class="wp-block-group">
				<!-- wp:navigation {"overlayMenu":"mobile","overlayBackgroundColor":"base","overlayTextColor":"contrast","layout":{"type":"flex","justifyContent":"right","flexWrap":"wrap"}} /-->

				<!-- wp:kosmiteia/language-switcher {"display":"short"} /-->
			</div>
			<!-- /wp:group -->
		</div>
		<!-- /wp:group -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
