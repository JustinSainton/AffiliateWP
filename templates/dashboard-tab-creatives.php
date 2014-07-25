<div id="affwp-affiliate-dashboard-creatives" class="affwp-tab-content">

	<h4><?php _e( 'Creatives', 'affiliate-wp' ); ?></h4>

	<?php $creatives = affiliate_wp()->creative->affiliate_creatives(); ?>

	<?php if( $creatives ) : ?>

		<?php echo $creatives; ?>

	<?php else : ?>

		<p><?php _e( 'Sorry, there are currently no creatives available.', 'affiliate-wp' ); ?></p>

	<?php endif; ?>

</div>