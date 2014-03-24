<?php

class Affiliate_WP_Shortcodes {

	public function __construct() {

		add_shortcode( 'affiliate_area', array( $this, 'affiliate_area' ) );

	}

	public function affiliate_area( $atts, $content = null ) {

		ob_start();
		affiliate_wp()->templates->get_template_part( 'dashboard' );
		return ob_get_clean();

	}

}
new Affiliate_WP_Shortcodes;