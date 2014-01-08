<?php

class Affiliate_WP_Base {
	

	public function __construct() {

	}

	public function is_valid_affiliate( $affiliate_id = 0 ) {

		$affiliate = affiliate_wp()->affiliates->get( 'affiliate_id', $affiliate_id );

		if( is_user_logged_in() ) {
			if( $this->get_affilite_id_of_user() == $affiliate ) {
				$affiliate = 0; // Affiliate ID is the same as the current user
			}
		}
		return ! empty( $affiliate );
	}

	public function get_referral_affiliate() {
		return affiliate_wp()->cookies->is_referral_cookie_set();
	}

	public function get_affilite_id_of_user( $user_id = 0 ) {

		if( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$affiliate_id = affiliate_wp()->affiliates->get_by( 'user_id', $user_id );

		if( ! empty( $affiliate_id ) ) {
			return $affiliate_id;
		}

		return false;
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