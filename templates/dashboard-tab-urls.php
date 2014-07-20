<div id="affwp-affiliate-dashboard-url-generator" class="affwp-tab-content">
		
	<h4><?php _e( 'Referral URL Generator', 'affiliate-wp' ); ?></h4>

	<p><?php printf( __( 'Your affiliate ID is: <strong>%d</strong>', 'affiliate-wp' ), affwp_get_affiliate_id() ); ?></p>
	<p><?php printf( __( 'Your referral URL is: <strong>%s</strong>', 'affiliate-wp' ), add_query_arg( affiliate_wp()->tracking->get_referral_var(), affwp_get_affiliate_id(), home_url( '/' ) ) ); ?></p>
	<p><?php _e( 'Enter any URL from this website in the form below to generate a referral link!', 'affiliate-wp' ); ?></p>

	<?php
	$base_url     = isset( $_GET['url'] ) ? urldecode( $_GET['url'] ) : home_url( '/' );
	$referral_url = isset( $_GET['url'] ) ? add_query_arg( affiliate_wp()->tracking->get_referral_var(), affwp_get_affiliate_id(), urldecode( $_GET['url'] ) ) : home_url( '/' );
	?>

	<form id="affwp-generate-ref-url" class="affwp-form" method="get" action="#affwp-generate-ref-url">
		<div class="affwp-base-url-wrap">
			<label for="affwp-url"><?php _e( 'Page URL', 'affiliate-wp' ); ?></label>
			<input type="text" name="url" id="affwp-url" value="<?php echo esc_attr( $base_url ); ?>" />
			
		</div>

		<div class="affwp-referral-url-wrap" <?php if ( ! isset( $_GET['url'] ) ) { echo 'style="display:none;"'; } ?>>
			<label for="affwp-referral-url"><?php _e( 'Referral URL', 'affiliate-wp' ); ?></label>
			<input type="text" id="affwp-referral-url" value="<?php echo esc_attr( $referral_url ); ?>" />
			<div class="description"><?php _e( '(now copy this referral link and share it anywhere)', 'affiliate-wp' ); ?></div>
		</div>

		<div class="affwp-referral-url-submit-wrap">
			<input type="hidden" id="affwp-affiliate-id" value="<?php echo esc_attr( affwp_get_affiliate_id() ); ?>" />
			<input type="hidden" id="affwp-referral-var" value="<?php echo esc_attr( affiliate_wp()->tracking->get_referral_var() ); ?>" />
			<input type="submit" class="button" value="<?php _e( 'Generate URL', 'affiliate-wp' ); ?>" />
		</div>
	</form>

</div>
