<div id="affwp-affiliate-dashboard-url-generator" class="affwp-tab-content">

	<h4><?php _e( 'Referral URL Generator', 'affiliate-wp' ); ?></h4>

	<?php if ( 'id' == affwp_get_referral_format() ) : ?>
		<p><?php printf( __( 'Your affiliate ID is: <strong>%s</strong>', 'affiliate-wp' ), affwp_get_affiliate_id() ); ?></p>
	<?php elseif ( 'username' == affwp_get_referral_format() ) : ?>
		<p><?php printf( __( 'Your affiliate username is: <strong>%s</strong>', 'affiliate-wp' ), affwp_get_affiliate_username() ); ?></p>
	<?php endif; ?>

	<?php
        $affiliate_urls = apply_filters('affwp_affiliate_dashboard_urls', array( 'Your referral URL is:' => affwp_get_affiliate_referral_url() ));
        if ( !empty($affiliate_urls) ) :
            foreach ($affiliate_urls as $label => $url) :
                echo '<p>';
                printf(__($label . ' <strong>%s</strong>', 'affiliate-wp'), esc_url(urldecode($url)));
                echo '</p>';
            endforeach;
        endif;
        ?>
	
	<p><?php _e( 'Enter any URL from this website in the form below to generate a referral link!', 'affiliate-wp' ); ?></p>

	<form id="affwp-generate-ref-url" class="affwp-form" method="get" action="#affwp-generate-ref-url">
		<div class="affwp-wrap affwp-base-url-wrap">
			<label for="affwp-url"><?php _e( 'Page URL', 'affiliate-wp' ); ?></label>
			<input type="text" name="url" id="affwp-url" value="<?php echo esc_url( urldecode( affwp_get_affiliate_base_url() ) ); ?>" />
		</div>

		<div class="affwp-wrap affwp-campaign-wrap">
			<label for="affwp-campaign"><?php _e( 'Campaign Name (optional)', 'affiliate-wp' ); ?></label>
			<input type="text" name="campaign" id="affwp-campaign" value="" />
		</div>

		<div class="affwp-wrap affwp-referral-url-wrap" <?php if ( ! isset( $_GET['url'] ) ) { echo 'style="display:none;"'; } ?>>
			<label for="affwp-referral-url"><?php _e( 'Referral URL', 'affiliate-wp' ); ?></label>
			<input type="text" id="affwp-referral-url" value="<?php echo esc_url( urldecode( affwp_get_affiliate_referral_url() ) ); ?>" />
			<div class="description"><?php _e( '(now copy this referral link and share it anywhere)', 'affiliate-wp' ); ?></div>
		</div>

		<div class="affwp-referral-url-submit-wrap">
			<input type="hidden" class="affwp-affiliate-id" value="<?php echo esc_attr( urldecode( affwp_get_referral_format_value() ) ); ?>" />
			<input type="hidden" class="affwp-referral-var" value="<?php echo esc_attr( affiliate_wp()->tracking->get_referral_var() ); ?>" />
			<input type="submit" class="button" value="<?php _e( 'Generate URL', 'affiliate-wp' ); ?>" />
		</div>
	</form>
</div>
