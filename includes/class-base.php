<?php

class Affiliate_WP_Base {
	

	public function __construct() {

	}

	public function is_referral() {
		return affiliate_wp()->cookies->is_referral_cookie_set();
	}

	public function complete_referral() {

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