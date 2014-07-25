<div id="affwp-affiliate-dashboard-creatives" class="affwp-tab-content">

	<h4><?php _e( 'Creatives', 'affiliate-wp' ); ?></h4>

	<?php 
		$creatives = affiliate_wp()->creative->affiliate_creatives() ? affiliate_wp()->creative->affiliate_creatives() : __( 'Sorry, there are currently no creatives available.', 'affiliate-wp' );
		echo $creatives;
	?>
	
</div>