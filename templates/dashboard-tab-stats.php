<div id="affwp-affiliate-dashboard-referral-counts" class="affwp-tab-content">

	<h4><?php _e( 'Statistics', 'affiliate-wp' ); ?></h4>

	<table class="affwp-table">
		<thead>
			<tr>
				<th><?php _e( 'Unpaid Referrals', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Paid Referrals', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Visits', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Conversion Rate', 'affiliate-wp' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td><?php echo affwp_count_referrals( affwp_get_affiliate_id(), 'unpaid' ); ?></td>
				<td><?php echo affwp_count_referrals( affwp_get_affiliate_id(), 'paid' ); ?></td>
				<td><?php echo affwp_count_visits( affwp_get_affiliate_id() ); ?></td>
				<td><?php echo affwp_get_affiliate_conversion_rate( affwp_get_affiliate_id() ); ?></td>
			</tr>
		</tbody>
	</table>

	<?php do_action( 'affwp_affiliate_dashboard_after_counts', affwp_get_affiliate_id() ); ?>

</div>

<div id="affwp-affiliate-dashboard-earnings-stats" class="affwp-tab-content">
	<table class="affwp-table">
		<thead>
			<tr>
				<th><?php _e( 'Unpaid Earnings', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Paid Earnings', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Commission Rate', 'affiliate-wp' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<tr>
				<td><?php echo affwp_get_affiliate_unpaid_earnings( affwp_get_affiliate_id(), true ); ?></td>
				<td><?php echo affwp_get_affiliate_earnings( affwp_get_affiliate_id(), true ); ?></td>
				<td><?php echo affwp_get_affiliate_rate( affwp_get_affiliate_id(), true ); ?></td>
			</tr>
		</tbody>
	</table>

	<?php do_action( 'affwp_affiliate_dashboard_after_earnings', affwp_get_affiliate_id() ); ?>

</div>

<div id="affwp-affiliate-dashboard-campaign-stats" class="affwp-tab-content">
	<table class="affwp-table">
		<thead>
			<tr>
				<th><?php _e( 'Campaign', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Visits', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Unique Links', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Converted', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Conversion Rate', 'affiliate-wp' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php if( $campaigns = affwp_get_affiliate_campaigns( affwp_get_affiliate_id() ) ) : ?>
				<?php foreach( $campaigns as $campaign ) : ?>
					<tr>
						<td><?php echo ! empty( $campaign->campaign ) ? esc_html( $campaign->campaign ) : __( 'None set', 'affiliate-wp' ); ?></td>
						<td><?php echo esc_html( $campaign->visits ); ?></td>
						<td><?php echo esc_html( $campaign->unique_visits ); ?></td>
						<td><?php echo esc_html( $campaign->referrals ); ?></td>
						<td><?php echo esc_html( affwp_format_amount( $campaign->conversion_rate ) ); ?>%</td>
					</tr>
				<?php endforeach; ?>
			<?php else : ?>
				<tr>
					<td colspan="5"><?php _e( 'You have no referrals or visits that included a campaign name.', 'affiliate-wp' ); ?></td>
				</tr>
			<?php endif; ?>
		</tbody>
	</table>

	<?php do_action( 'affwp_affiliate_dashboard_after_campaign_stats', affwp_get_affiliate_id() ); ?>

</div>
