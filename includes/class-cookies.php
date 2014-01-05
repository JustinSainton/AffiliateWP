<?php

class Affiliate_WP_Cookies {
	
	private $referral_var;

	private $expiration_time;

	public function __construct() {

		$this->set_expiration_time();

		add_action( 'init', array( $this, 'set_referral_var' ), -9999 );
		add_action( 'template_redirect', array( $this, 'set_referral_cookie' ), -999 );

	}

	public function get_referral_var() {
		return $this->referral_var;
	}

	public function set_referral_var() {
		$this->referral_var = apply_filters( 'affwp_referral_var', 'ref' );
	}

	public function set_referral_cookie() {
		
		if( ! isset( $_GET[ $this->get_referral_var() ] ) ) {
			return; // no referral var present
		}

		if( $this->is_referral_cookie_set() ) {
			return; // cookie already set
		}

		$affiliate_id = $this->get_affiliate_id();

		setcookie( 'affwp_referral', $affiliate_id, current_time( 'timestamp' ) + $this->expiration_time, COOKIEPATH, COOKIE_DOMAIN );		

		$this->track_visit();

	}

	public function set_visit_cookie( $visit_id = 0 ) {
		setcookie( 'affwp_visit_id', $visit_id, current_time( 'timestamp' ) + $this->expiration_time, COOKIEPATH, COOKIE_DOMAIN );		
	}

	public function is_referral_cookie_set() {
		return ! empty( $_COOKIE[ 'affwp_referral'] );
	}

	public function get_affiliate_id() {
		return isset( $_GET[ $this->get_referral_var() ] ) ? absint( $_GET[ $this->get_referral_var() ] ) : 0;
	}

	public function set_expiration_time() {
		// Default time is 24 hours (in seconds)
		$this->expiration_time = apply_filters( 'affwp_cookie_expiration_time', 86400 );
	}

	public function get_expiration_time() {
		return $this->expiration_time;
	}

	public function track_visit() {

		$visit_id = affiliate_wp()->visits->add( array(
			'affiliate_id' => $this->get_affiliate_id(),
			'ip'           => affiliate_wp()->base->get_ip()
		) );

		$this->set_visit_cookie( $visit_id );

	}

}