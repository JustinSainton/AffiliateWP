<?php

class Affiliate_WP_Shortcodes {

	public function __construct() {

		add_shortcode( 'affiliate_area',              array( $this, 'affiliate_area'         ) );
		add_shortcode( 'affiliate_login',             array( $this, 'affiliate_login'        ) );
		add_shortcode( 'affiliate_registration',      array( $this, 'affiliate_registration' ) );
		add_shortcode( 'affiliate_conversion_script', array( $this, 'conversion_script'      ) );
		add_shortcode( 'affiliate_referral_url',      array( $this, 'referral_url'           ) );
		add_shortcode( 'affiliate_content',           array( $this, 'affiliate_content'      ) );
		add_shortcode( 'non_affiliate_content',       array( $this, 'non_affiliate_content'  ) );
		add_shortcode( 'affiliate_creative',          array( $this, 'affiliate_creative'     ) );
		add_shortcode( 'affiliate_creatives',         array( $this, 'affiliate_creatives'     ) );

	}

	/**
	 *  Renders the affiliate area
	 *
	 *  @since 1.0
	 *  @return string
	 */
	public function affiliate_area( $atts, $content = null ) {

		ob_start();

		if ( is_user_logged_in() && affwp_is_affiliate() ) {

			affiliate_wp()->templates->get_template_part( 'dashboard' );

		} elseif ( is_user_logged_in() && affiliate_wp()->settings->get( 'allow_affiliate_registration' ) ) {

			affiliate_wp()->templates->get_template_part( 'register' );

		} else {

			if ( affiliate_wp()->settings->get( 'allow_affiliate_registration' ) ) {

				affiliate_wp()->templates->get_template_part( 'register' );

			} else {
				affiliate_wp()->templates->get_template_part( 'no', 'access' );
			}

			if ( ! is_user_logged_in() ) {

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
		extract( shortcode_atts( array(
				'redirect' => '',
			), $atts, 'affiliate_login' )
		);

		if ( ! is_user_logged_in() ) {

			$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
			wp_enqueue_style( 'affwp-forms', AFFILIATEWP_PLUGIN_URL . 'assets/css/forms' . $suffix . '.css', AFFILIATEWP_VERSION );

			return affiliate_wp()->login->login_form( $redirect );
		}

	}


	/**
	 *  Renders the affiliate registration form
	 *
	 *  @since 1.1
	 *  @return string
	 */
	public function affiliate_registration( $atts, $content = null ) {
		extract( shortcode_atts( array(
				'redirect' => '',
			), $atts, 'affiliate_registration' )
		);

		if ( ! affiliate_wp()->settings->get( 'allow_affiliate_registration' ) ) {
			return;
		}

		if ( affwp_is_affiliate() ) {
			return;
		}

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
		wp_enqueue_style( 'affwp-forms', AFFILIATEWP_PLUGIN_URL . 'assets/css/forms' . $suffix . '.css', AFFILIATEWP_VERSION );

		return affiliate_wp()->register->register_form( $redirect );

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
		if ( ! affwp_is_affiliate() ) {
			return;
		}

		shortcode_atts( array(
			'url'    => '',
			'format' => '',
			'pretty' => ''
		), $atts, 'affiliate_referral_url' );

		// get affiliate username
		$affiliate = affwp_get_affiliate( affwp_get_affiliate_id() );
		$user_info = get_userdata( $affiliate->user_id );
		$username  = esc_html( $user_info->user_login );

		// format passed in from shortcode
		if ( isset( $atts['format'] ) ) {
			if ( 'id' == $atts['format'] ) {
				$format = affwp_get_affiliate_id();
			} elseif ( 'username' == $atts['format'] ) {
				$format = $username;
			}
		} elseif ( ! isset( $format ) ) {
			// get format from settings
			$format = affiliate_wp()->settings->get( 'referral_format' );

			switch ( $format ) {
				case 'id':
					$format = affwp_get_affiliate_id();
				break;
				
				case 'username':
					$format = $username;
				break;
			}
		}

		// pretty affiliate URLs
		$is_pretty_affiliate_urls = affiliate_wp()->settings->get( 'referral_pretty_urls' );

		// base URL
		if ( ! empty( $content ) ) {
			$base = $content;
		} else {
			$base = ! empty( $atts[ 'url' ] ) ? trailingslashit( esc_url( $atts[ 'url' ] ) ) : home_url( '/' );
		}

		// pretty affiliate URLS is enabled via shortcode
		if ( isset( $atts['pretty'] ) ) {
			if ( 'yes' == $atts['pretty'] ) {
				// pretty affiliate URLs enabled
				$content = $base . affiliate_wp()->tracking->get_referral_var() . '/' . $format;
			} elseif ( 'no' == $atts['pretty'] ) {
				// pretty affiliate URLS disabled
				$content = add_query_arg( affiliate_wp()->tracking->get_referral_var(), $format, $base );
			}		
		} elseif ( $is_pretty_affiliate_urls ) {
			// pretty affiliate URLs enabled from settings
			$content = $base . affiliate_wp()->tracking->get_referral_var() . '/' . $format;
		} else {
			$content = add_query_arg( affiliate_wp()->tracking->get_referral_var(), $format, $base );
		}
		
		return $content;
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

		return do_shortcode( $content );
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

		return do_shortcode( $content );
	}

	/**
	 * Affiliate creative shortcode.
	 *
	 * @since  1.1.4
	 * @return string
	 */
	public function affiliate_creative( $atts, $content = null ) {

		shortcode_atts(
			array(
				'id'         => '',                    // ID of the creative
				'image_id'   => '',                    // ID of image from media library if not using creatives section
				'image_link' => '',                    // External URL if image is hosted off-site
				'link'       => '',                    // Where the banner links to
				'preview'    => 'yes',                 // Display an image/text preview above HTML code
				'text'       => get_bloginfo( 'name' ) // Text shown in alt/title tags
			),
			$atts,
			'affiliate_creative'
		);

		if ( ! affwp_is_affiliate() )
			return;

		$content = affiliate_wp()->creative->affiliate_creative( $atts );

		return do_shortcode( $content );
	}

	/**
	 * Affiliate creatives shortcode.
	 * Shows all the creatives from Affiliates -> Creatives
	 *
	 * @since  1.1.4
	 * @return string
	 */
	public function affiliate_creatives( $atts, $content = null ) {

		shortcode_atts(
			array(
				'preview' => 'yes' // Display an image/text preview above HTML code
			),
			$atts,
			'affiliate_creatives'
		);

		if ( ! affwp_is_affiliate() )
			return;

		$content = affiliate_wp()->creative->affiliate_creatives( $atts );

		return do_shortcode( $content );
	}

}
new Affiliate_WP_Shortcodes;