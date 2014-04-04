<?php

class Affiliate_WP_Shortcodes {

	public function __construct() {

		add_shortcode( 'affiliate_area', array( $this, 'affiliate_area' ) );
		add_shortcode( 'affiliate_conversion_script', array( $this, 'conversion_script' ) );

	}

	public function affiliate_area( $atts, $content = null ) {

		ob_start();

		if( is_user_logged_in() && affwp_is_affiliate() ) {
			
			affiliate_wp()->templates->get_template_part( 'dashboard' );
	
		} elseif( is_user_logged_in() && affiliate_wp()->settings->get( 'allow_affiliate_registration' ) ) {
	
			affiliate_wp()->templates->get_template_part( 'register' );

		} else {

			if( affiliate_wp()->settings->get( 'allow_affiliate_registration' ) ) {

				affiliate_wp()->templates->get_template_part( 'register' );

			} else {
				affiliate_wp()->templates->get_template_part( 'no', 'access' );
			}

			if( ! is_user_logged_in() ) {

				affiliate_wp()->templates->get_template_part( 'login' );

			}

		}

		return ob_get_clean();

	}

	public function conversion_script( $atts, $content = null ) {


		shortcode_atts( array( 'amount' => '', 'description' => '', 'reference' => '', 'context' => '' ), $atts, 'affwp_conversion_script' );

		$defaults = array(
			'amount'      => '',
			'description' => '',
			'context'     => '',
			'reference'   => '',
			'status'      => ''
		);

		$args = wp_parse_args( $atts, $defaults );

		wp_enqueue_script( 'jquery-cookie', AFFILIATEWP_PLUGIN_URL . 'assets/js/jquery.cookie.js', array( 'jquery' ), '1.4.0' );
		wp_localize_script( 'jquery-cookie', 'affwp_scripts', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

		$md5 = md5( $args['amount'] . $args['description'] . $args['reference'] . $args['context'] . $args['status'] );

		return affiliate_wp()->tracking->conversion_script( $args, $md5 );

	}

}
new Affiliate_WP_Shortcodes;