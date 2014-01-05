<?php

class Affiliate_WP_Base {
	

	public function __construct() {

	}

	public function get_referral_affiliate() {
		return affiliate_wp()->cookies->is_referral_cookie_set();
	}

	public function insert_referral( $args = array() ) {

		$defaults = array(
			'affiliate_id' => $this->get_referral_affiliate(),
			'status'       => 'pending',
			'ip'           => $this->get_ip()
		);

		$args = wp_parse_args( $args, $defaults );		

		affiliate_wp()->referrals->add( $args );
		
		// update the original visit with the referral ID

	}

	public function get_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return apply_filters( 'affwp_get_ip', $ip );
	}

}