<div id="affwp-affiliate-dashboard-graphs" class="affwp-tab-content">

	<h4><?php _e( 'Referral Graphs', 'affiliate-wp' ); ?></h4>

	<?php
	$graph = new Affiliate_WP_Referrals_Graph;
	$graph->set( 'x_mode', 'time' );
	$graph->set( 'affiliate_id', affwp_get_affiliate_id() );
	$graph->display();
	?>

	<?php do_action( 'affwp_affiliate_dashboard_after_graphs', affwp_get_affiliate_id() ); ?>

</div>