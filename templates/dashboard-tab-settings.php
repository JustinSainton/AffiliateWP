<div id="affwp-affiliate-dashboard-profile" class="affwp-tab-content">

	<h4><?php _e( 'Profile Settings', 'affiliate-wp' ); ?></h4>

	<form id="affwp-affiliate-dashboard-profile-form" class="affwp-form" method="post">
		<div class="affwp-wrap affwp-payment-email-wrap">
			<label for="affwp-payment-email"><?php _e( 'Your payment email', 'affiliate-wp' ); ?></label>
			<input id="affwp-payment-email" type="email" name="payment_email" value="<?php echo esc_attr( affwp_get_affiliate_email( affwp_get_affiliate_id() ) ); ?>" />
		</div>

		<div class="affwp-wrap affwp-send-notifications-wrap">
			<input id="affwp-referral-notifications" type="checkbox" name="referral_notifications" value="1"<?php checked( true, get_user_meta( affwp_get_affiliate_user_id( affwp_get_affiliate_id() ), 'affwp_referral_notifications', true ) ); ?>/>
			<label for="affwp-referral-notifications"><?php _e( 'Enable New Referral Notifications', 'affiliate-wp' ); ?></label>
		</div>

		<?php do_action( 'affwp_affiliate_dashboard_before_submit', affwp_get_affiliate_id(), affwp_get_affiliate_user_id( affwp_get_affiliate_id() ) ); ?>
		
		<div class="affwp-save-profile-wrap">
			<input type="hidden" name="affwp_action" value="update_profile_settings" />
			<input type="hidden" id="affwp-affiliate-id" name="affiliate_id" value="<?php echo esc_attr( affwp_get_affiliate_id() ); ?>" />
			<input type="submit" class="button" value="<?php esc_attr_e( 'Save Profile Settings', 'affiliate-wp' ); ?>" />
		</div>
	</form>
</div>