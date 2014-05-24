<?php

class Affiliate_WP_Shortcodes {

	public function __construct() {

		add_shortcode( 'affiliate_area', array( $this, 'affiliate_area' ) );
		add_shortcode( 'affiliate_login', array( $this, 'affiliate_login' ) );
		add_shortcode( 'affiliate_registration', array( $this, 'affiliate_registration' ) );
		add_shortcode( 'affiliate_conversion_script', array( $this, 'conversion_script' ) );
		add_shortcode( 'affiliate_referral_url', array( $this, 'referral_url' ) );
		add_shortcode( 'affiliate_content', array( $this, 'affiliate_content' ) );
		add_shortcode( 'non_affiliate_content', array( $this, 'non_affiliate_content' ) );
	}

	/**
	 *  Renders the affiliate area
	 *
	 *  @since 1.0
	 *  @return string
	 */
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

	/**
	 *  Renders the affiliate login form
	 *
	 *  @since 1.1
	 *  @return string
	 */
	public function affiliate_login( $atts, $content = null ) {

		ob_start();

		if( ! is_user_logged_in() ) {

			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			wp_enqueue_style( 'affwp-forms', AFFILIATEWP_PLUGIN_URL . 'assets/css/forms' . $suffix . '.css', AFFILIATEWP_VERSION );

			affiliate_wp()->templates->get_template_part( 'login' );

		}

		return ob_get_clean();

	}

	/**
	 *  Renders the affiliate registration form
	 *
	 *  @since 1.1
	 *  @return string
	 */
	public function affiliate_registration( $atts, $content = null ) {

		ob_start();

		if( ! affiliate_wp()->settings->get( 'allow_affiliate_registration' ) ) {
			return;
		}

		if( affwp_is_affiliate() ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_style( 'affwp-forms', AFFILIATEWP_PLUGIN_URL . 'assets/css/forms' . $suffix . '.css', AFFILIATEWP_VERSION );


		affiliate_wp()->templates->get_template_part( 'register' );

		return ob_get_clean();

	}

	/**
	 *  Outputs a generic conversion script for custom referral tracking
	 *
	 *  @since 1.0
	 *  @return string
	 */
	public function conversion_script( $atts, $content = null ) {

		shortcode_atts(
			array(
				'amount'      => '',
				'description' => '',
				'reference'   => '',
				'context'     => ''
			),
			$atts,
			'affwp_conversion_script'
		);

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

		return affiliate_wp()->tracking->conversion_script( $args );

	}

	/**
	 * Outputs the referral URL for the current affiliate
	 *
	 *  @since 1.0.1
	 *  @return string
	 */
	public function referral_url( $atts, $content = null ) {

		if( ! affwp_is_affiliate() ) {
			return;
		}

		return add_query_arg( affiliate_wp()->tracking->get_referral_var(), affwp_get_affiliate_id(), home_url( '/' ) );
	}

	/**
	 * Affiliate content shortcode.
	 * Renders the content if the current user is an affiliate.
	 * @since  1.0.4
	 * @return string 
	 */
	public function affiliate_content( $atts, $content = null ) {

		if ( ! affwp_is_affiliate() ) {
			return;
		}

		return $content;
	}

	/**
	 * Non Affiliate content shortcode.
	 * Renders the content if the current user is not an affiliate.
	 * @since  1.1
	 * @return string 
	 */
	public function non_affiliate_content( $atts, $content = null ) {

		if ( affwp_is_affiliate() ) {
			return;
		}

		return $content;
	}

}
new Affiliate_WP_Shortcodes;