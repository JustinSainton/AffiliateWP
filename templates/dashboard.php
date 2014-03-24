<?php $affiliate_id = affwp_get_affiliate_id(); ?>
<div id="affwp-affiliate-dashboard">

	<h4><?php _e( 'Stats', 'affiliate-wp' ); ?></h4>

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

</div>