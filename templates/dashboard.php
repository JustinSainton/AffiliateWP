<?php $affiliate_id = affwp_get_affiliate_id(); ?>
<?php $user_id = affwp_get_affiliate_user_id( $affiliate_id ); ?>
<div id="affwp-affiliate-dashboard">

	<?php do_action( 'affwp_affiliate_dashboard_top', $affiliate_id ); ?>

	<h4><?php _e( 'Stats', 'affiliate-wp' ); ?></h4>

	<?php if ( 'pending' == affwp_get_affiliate_status( $affiliate_id ) ) : ?>

		<p class="affwp-notice"><?php _e( 'Your affiliate account is pending approval', 'affiliate-wp' ); ?></p>

	<?php elseif ( 'inactive' == affwp_get_affiliate_status( $affiliate_id ) ) : ?>

		<p class="affwp-notice"><?php _e( 'Your affiliate account is not active', 'affiliate-wp' ); ?></p>

	<?php elseif ( 'rejected' == affwp_get_affiliate_status( $affiliate_id ) ) : ?>

		<p class="affwp-notice"><?php _e( 'Your affiliate account request has been rejected', 'affiliate-wp' ); ?></p>

	<?php endif; ?>

	<?php do_action( 'affwp_affiliate_dashboard_notices', $affiliate_id ); ?>

	<table id="affwp-affiliate-dashboard-referral-counts" class="affwp_table">

		<thead>

			<tr>

				<th><?php _e( 'Unpaid Referrals', 'affwp' ); ?></th>
				<th><?php _e( 'Paid Referrals', 'affwp' ); ?></th>
				<th><?php _e( 'Visits', 'affwp' ); ?></th>
				<th><?php _e( 'Conversion Rate', 'affwp' ); ?></th>

			</tr>

		</thead>

		<tbody>

			<tr>

				<td><?php echo affwp_count_referrals( $affiliate_id, 'unpaid' ); ?></td>
				<td><?php echo affwp_count_referrals( $affiliate_id, 'paid' ); ?></td>
				<td><?php echo affwp_count_visits( $affiliate_id ); ?></td>
				<td><?php echo affwp_get_affiliate_conversion_rate( $affiliate_id ); ?></td>

			</tr>

		</tobdy>

	</table>

	<?php do_action( 'affwp_affiliate_dashboard_after_counts', $affiliate_id ); ?>

	<table id="affwp-affiliate-dashboard-earnings-stats" class="affwp_table">

		<thead>

			<tr>

				<th><?php _e( 'Unpaid Earnings', 'affwp' ); ?></th>
				<th><?php _e( 'Paid Earnings', 'affwp' ); ?></th>
				<th><?php _e( 'Commission Rate', 'affwp' ); ?></th>

			</tr>

		</thead>

		<tbody>

			<tr>

				<td><?php echo affwp_get_affiliate_unpaid_earnings( $affiliate_id, true ); ?></td>
				<td><?php echo affwp_get_affiliate_earnings( $affiliate_id, true ); ?></td>
				<td><?php echo affwp_get_affiliate_rate( $affiliate_id, true ); ?></td>

			</tr>

		</tobdy>

	</table>

	<?php do_action( 'affwp_affiliate_dashboard_after_earnings', $affiliate_id ); ?>

	<h4><?php _e( 'Referrals Over Time', 'affiliate-wp' ); ?></h4>

	<?php
	$graph = new Affiliate_WP_Referrals_Graph;
	$graph->set( 'x_mode', 'time' );
	$graph->set( 'affiliate_id', $affiliate_id );
	$graph->display();
	?>

	<?php do_action( 'affwp_affiliate_dashboard_after_graphs', $affiliate_id ); ?>

	<h4><?php _e( 'Notifications', 'affiliate-wp' ); ?></h4>

	<div id="affwp-affiliate-dashboard-notifications">

		<p><?php _e( 'Enable or disable the email notifications you would like to receive.', 'affiliate-wp' ); ?></p>

		<form method="post" id="affwp_email_notifications" class="affwp_form">
			<div id="affwp_send_notifications_wrap">
				<input type="checkbox" name="referral_notifications" id="affwp_referral_notifications" value="1"<?php checked( true, get_user_meta( $user_id, 'affwp_referral_notifications', true ) ); ?>/>
				<label for="affwp_referral_notifications"><?php _e( 'New referral notifications', 'affiliate-wp' ); ?></label>
			</div>
			<div id="affwp_save_notifications_wrap">
				<input type="hidden" name="affwp_action" value="update_notification_settings"/>
				<input type="hidden" id="affwp_affiliate_id" name="affiliate_id" value="<?php echo esc_attr( $affiliate_id ); ?>"/>
				<input type="submit" value="<?php _e( 'Save Notification Settings', 'affiliate-wp' ); ?>"/>
			</div>
		</form>

	</div>

	<h4><?php _e( 'Referral URL Generator', 'affiliate-wp' ); ?></h4>

	<div id="affwp-affiliate-dashboard-url-generator">

		<p><?php printf( __( 'Your affiliate ID is: <strong>%d</strong>', 'affiliate-wp' ), $affiliate_id ); ?></p>
		<p><?php _e( 'Enter any URL on this website below to generate a referral link!', 'affiliate-wp' ); ?></p>

		<?php
		$base_url     = isset( $_GET['url'] ) ? urldecode( $_GET['url'] ) : home_url( '/' );
		$referral_url = isset( $_GET['url'] ) ? add_query_arg( affiliate_wp()->tracking->get_referral_var(), $affiliate_id, urldecode( $_GET['url'] ) ) : home_url( '/' );
		?>

		<form method="get" id="affwp_generate_ref_url" class="affwp_form">
			<div id="affwp_base_url_wrap">
				<input type="text" name="url" id="affwp_url" value="<?php echo esc_attr( $base_url ); ?>"/>
				<label for="affwp_url"><?php _e( 'Page URL', 'affiliate-wp' ); ?></label>
			</div>
			<div id="affwp_referral_url_wrap"<?php if( ! isset( $_GET['url'] ) ) { echo 'style="display:none;"'; } ?>>
				<input type="text" id="affwp_referral_url" value="<?php echo esc_attr( $referral_url ); ?>"/>
				<label for="affwp_referral_url"><?php _e( 'Referral URL', 'affiliate-wp' ); ?></label>
				<div class="description"><?php _e( '(now copy this referral link and share it anywhere)', 'affiliate-wp' ); ?></div>
			</div>
			<div id="affwp_referral_url_submit_wrap">
				<input type="hidden" id="affwp_affiliate_id" value="<?php echo esc_attr( $affiliate_id ); ?>"/>
				<input type="hidden" id="affwp_referral_var" value="<?php echo esc_attr( affiliate_wp()->tracking->get_referral_var() ); ?>"/>
				<input type="submit" value="<?php _e( 'Generate URL', 'affiliate-wp' ); ?>"/>
			</div>
		</form>

	</div>

	<?php do_action( 'affwp_affiliate_dashboard_bottom', $affiliate_id ); ?>

</div>