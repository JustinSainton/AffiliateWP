<?php $affiliate_id = affwp_get_affiliate_id(); ?>
<div id="affwp-affiliate-dashboard">

	<h4><?php _e( 'Stats', 'affiliate-wp' ); ?></h4>

	<?php if ( 'pending' == affwp_get_affiliate_status( $affiliate_id ) ) : ?>

		<p class="affwp-notice"><?php _e( 'Your affiliate account is pending approval', 'affiliate-wp' ); ?></p>

	<?php elseif ( 'inactive' == affwp_get_affiliate_status( $affiliate_id ) ) : ?>

		<p class="affwp-notice"><?php _e( 'Your affiliate account is not active', 'affiliate-wp' ); ?></p>
	
	<?php elseif ( 'rejected' == affwp_get_affiliate_status( $affiliate_id ) ) : ?>

		<p class="affwp-notice"><?php _e( 'Your affiliate account request has been rejected', 'affiliate-wp' ); ?></p>

	<?php endif; ?>

	<table id="affwp-affiliate-dashboard-stats" class="affwp-table">

		<thead>

			<tr>

				<th><?php _e( 'Referrals', 'affwp' ); ?></th>
				<th><?php _e( 'Unpaid Referrals', 'affwp' ); ?></th>
				<th><?php _e( 'Paid Referrals', 'affwp' ); ?></th>
				<th><?php _e( 'Visits', 'affwp' ); ?></th>

			</tr>

		</thead>

		<tbody>

			<tr>

				<td><?php echo affwp_count_referrals( $affiliate_id ); ?></td>
				<td><?php echo affwp_count_referrals( $affiliate_id, 'unpaid' ); ?></td>
				<td><?php echo affwp_count_referrals( $affiliate_id, 'paid' ); ?></td>
				<td><?php echo affwp_count_visits( $affiliate_id ); ?></td>

			</tr>

		</tobdy>

	</table>

	<h4><?php _e( 'Referrals Over Time', 'affiliate-wp' ); ?></h4>

	<?php
	$graph = new Affiliate_WP_Referrals_Graph;
	$graph->set( 'x_mode', 'time' );
	$graph->set( 'affiliate_id', $affiliate_id );
	$graph->display();
	?>	

	<h4><?php _e( 'Your Affiliate Links', 'affiliate-wp' ); ?></h4>

	<div id="affwp-affiliate-dashboard-links">

		<?php echo add_query_arg( affiliate_wp()->tracking->get_referral_var(), $affiliate_id, home_url('/') ); ?>

	</div>

</div>