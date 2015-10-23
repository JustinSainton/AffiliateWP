<?php

class Affiliate_WP_Rewrites {

	/**
	 * Get things started
	 *
	 * @since 1.7.8
	 */
	public function __construct() {

		$this->init();
	
	}

	/**
	 * Allow developers to extend and overwrite the default actions we add
	 *
	 * @since 1.7.8
	 */
	public function init() {

		add_action( 'init', array( $this, 'rewrites' ) );

		if ( function_exists( 'wc_get_page_id' ) && get_option( 'page_on_front' ) == wc_get_page_id( 'shop' ) ) {

			add_action( 'pre_get_posts', array( $this, 'unset_query_arg' ), -1 );

		} else {

			add_action( 'pre_get_posts', array( $this, 'unset_query_arg' ), 999999 );

		}

		add_action( 'redirect_canonical', array( $this, 'prevent_canonical_redirect' ), 0, 2 );
	}

	/**
	 * Registers the rewrite rules for pretty affiliate links
	 *
	 * This was in Affiliate_WP_Tracking until 1.7.8
	 *
	 * @since 1.3
	 */
	public function rewrites() {

		$taxonomies = get_taxonomies( array( 'public' => true, '_builtin' => false ), 'objects' );
		
		foreach( $taxonomies as $tax_id => $tax ) {

			add_rewrite_rule( $tax->rewrite['slug'] . '/(.+?)/' . affiliate_wp()->tracking->get_referral_var() . '(/(.*))?/?$', 'index.php?' . $tax_id . '=$matches[1]&ref=$matches[3]', 'top');		
			
		}

		add_rewrite_endpoint( affiliate_wp()->tracking->get_referral_var(), EP_ALL );

	}

	/**
	 * Removes our tracking query arg so as not to interfere with the WP query, see https://core.trac.wordpress.org/ticket/25143
	 *
	 * This was in Affiliate_WP_Tracking until 1.7.8
	 *
	 * @since 1.3.1
	 */
	public function unset_query_arg( $query ) {

		if ( is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$key = affiliate_wp()->tracking->get_referral_var();

		$ref = $query->get( $key );

		if ( ! empty( $ref ) ) {

			$this->referral = $ref;

			// unset ref var from $wp_query
			$query->set( $key, null );

			global $wp;

			// unset ref var from $wp
			unset( $wp->query_vars[ $key ] );

			// if in home (because $wp->query_vars is empty) and 'show_on_front' is page
			if ( empty( $wp->query_vars ) && get_option( 'show_on_front' ) === 'page' ) {

			 	// reset and re-parse query vars
				$wp->query_vars['page_id'] = get_option( 'page_on_front' );
				$query->parse_query( $wp->query_vars );

			}

		}

	}

	/**
	 * Filters on canonical redirects
	 *
	 * This was in Affiliate_WP_Tracking until 1.7.8
	 *
	 * @since 1.4
	 * @return string
	 */
	public function prevent_canonical_redirect( $redirect_url, $requested_url ) {

		if( ! is_front_page() ) {
			return $redirect_url;
		}

		$key = affiliate_wp()->tracking->get_referral_var();
		$ref = get_query_var( $key );

		if( ! empty( $ref ) || false !== strpos( $requested_url, $key ) ) {

			$redirect_url = $requested_url;

		}

		return $redirect_url;

	}

}