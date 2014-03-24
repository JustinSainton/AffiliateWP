<?php

class Affiliate_WP_Shortcodes {

	public function __construct() {

		add_shortcode( 'affiliate_area', array( $this, 'affiliate_area' ) );

	}

	public function affiliate_area( $atts, $content = null ) {

	}

}