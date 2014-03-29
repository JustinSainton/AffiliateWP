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

	<table id="affwp-affiliate-dashboard-referral-counts" class="affwp-table">

		<thead>

			<tr>

				<th><?php _e( 'Total Referrals', 'affwp' ); ?></th>
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

	<table id="affwp-affiliate-dashboard-earnings-stats" class="affwp-table">

		<thead>

			<tr>

				<th><?php _e( 'Paid Earnings', 'affwp' ); ?></th>
				<th><?php _e( 'Unpaid Earnings', 'affwp' ); ?></th>
				<th><?php _e( 'Conversion Rate', 'affwp' ); ?></th>

			</tr>

		</thead>

		<tbody>

			<tr>

				<td><?php echo affwp_get_affiliate_earnings( $affiliate_id, true ); ?></td>
				<td><?php echo affwp_get_affiliate_unpaid_earnings( $affiliate_id ); ?></td>
				<td><?php echo affwp_get_affiliate_conversion_rate( $affiliate_id ); ?></td>

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

	<h4><?php _e( 'Referral URL Generator', 'affiliate-wp' ); ?></h4>

	<div id="affwp-affiliate-dashboard-url-generator">

		<p><?php printf( __( 'Your affiliate ID is: <strong>%d</strong>', 'affiliate-wp' ), $affiliate_id ); ?></p>
		<p><?php _e( 'Enter any URL below to generate a referral link!', 'affiliate-wp' ); ?></p>

		<?php
		$base_url     = isset( $_GET['url'] ) ? urldecode( $_GET['url'] ) : home_url( '/' );
		$referral_url = isset( $_GET['url'] ) ? add_query_arg( affiliate_wp()->tracking->get_referral_var(), $affiliate_id, urldecode( $_GET['url'] ) ) : home_url( '/' );
		?>

		<form method="get" id="affwp_generate_ref_url" class="affwp_form"> 
			<div id="affwp_base_url_wrap">
				<label for="affwp_url"><?php _e( 'URL', 'affiliate-wp' ); ?></label>
				<input type="text" name="url" id="affwp_url" value="<?php echo esc_attr( $base_url ); ?>"/>
			</div>
			<div id="affwp_referral_url_wrap"<?php if( ! isset( $_GET['url'] ) ) { echo 'style="display:none;"'; } ?>>
				<label for="affwp_referral_url"><?php _e( 'Referral URL', 'affiliate-wp' ); ?></label>
				<input type="text" id="affwp_referral_url" value="<?php echo esc_attr( $referral_url ); ?>"/>
				<div class="description"><?php _e( '(now copy this referral link and share it anywhere)', 'affiliate-wp' ); ?></div>
			</div>
			<div id="affwp_referral_url_submit_wrap">
				<input type="hidden" id="affwp_affiliate_id" value="<?php echo esc_attr( $affiliate_id ); ?>"/>
				<input type="hidden" id="affwp_referral_var" value="<?php echo esc_attr( affiliate_wp()->tracking->get_referral_var() ); ?>"/>
				<input type="submit" value="<?php _e( 'Generate URL', 'affiliate-wp' ); ?>"/>
			</div>
		</form>

	</div>

</div>