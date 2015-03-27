<div id="affwp-affiliate-dashboard-url-generator" class="affwp-tab-content">
		
	<h4><?php _e( 'Referral URL Generator', 'affiliate-wp' ); ?></h4>
	
	<?php if ( 'id' == affwp_get_referral_format() ) : ?>
		<p><?php printf( __( 'Your affiliate ID is: <strong>%s</strong>', 'affiliate-wp' ), affwp_get_affiliate_id() ); ?></p>
	<?php elseif ( 'username' == affwp_get_referral_format() ) : ?>
		<p><?php printf( __( 'Your affiliate username is: <strong>%s</strong>', 'affiliate-wp' ), affwp_get_affiliate_username() ); ?></p>
	<?php endif; ?>

	<p><?php printf( __( 'Your referral URL is: <strong>%s</strong>', 'affiliate-wp' ), esc_url( affwp_get_affiliate_referral_url() ) ); ?></p>
	<p><?php _e( 'Enter any URL from this website in the form below to generate a referral link!', 'affiliate-wp' ); ?></p>

	<form id="affwp-generate-ref-url" class="affwp-form" method="get" action="#affwp-generate-ref-url">
		<div class="affwp-wrap affwp-base-url-wrap">
			<label for="affwp-url"><?php _e( 'Page URL', 'affiliate-wp' ); ?></label>
			<input type="text" name="url" id="affwp-url" value="<?php echo esc_url( affwp_get_affiliate_base_url() ); ?>" />
		</div>

		<div class="affwp-wrap affwp-referral-url-wrap" <?php if ( ! isset( $_GET['url'] ) ) { echo 'style="display:none;"'; } ?>>
			<label for="affwp-referral-url"><?php _e( 'Referral URL', 'affiliate-wp' ); ?></label>
			<input type="text" id="affwp-referral-url" value="<?php echo esc_url( affwp_get_affiliate_referral_url() ); ?>" />
			<div class="description"><?php _e( '(now copy this referral link and share it anywhere)', 'affiliate-wp' ); ?></div>
		</div>

		<div class="affwp-referral-url-submit-wrap">
			<input type="hidden" id="affwp-affiliate-id" value="<?php echo esc_attr( affwp_get_referral_format_value() ); ?>" />
			<input type="hidden" id="affwp-referral-var" value="<?php echo esc_attr( affiliate_wp()->tracking->get_referral_var() ); ?>" />
			<input type="submit" class="button" value="<?php _e( 'Generate URL', 'affiliate-wp' ); ?>" />
		</div>
	</form>
</div>
