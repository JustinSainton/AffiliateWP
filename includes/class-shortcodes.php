<?php

class Affiliate_WP_Shortcodes {

	public function __construct() {

		add_shortcode( 'affiliate_area', array( $this, 'affiliate_area' ) );

	}

	public function affiliate_area( $atts, $content = null ) {

		ob_start();

		if( is_user_logged_in() && affwp_is_affiliate() ) {
			
			affiliate_wp()->templates->get_template_part( 'dashboard' );
	
		} elseif( is_user_logged_in() ) {
	
			affiliate_wp()->templates->get_template_part( 'register' );

		} else {

			affiliate_wp()->templates->get_template_part( 'login' );

		}

		return ob_get_clean();

	}

}
new Affiliate_WP_Shortcodes;