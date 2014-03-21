<?php

class Affiliate_WP_Base {

	public function __construct() {
		$this->init();
	}

	public function init() {

	}

	public function was_referred() {
		return affiliate_wp()->tracking->was_referred();
	}

	public function insert_pending_referral( $amount = '', $reference = 0, $data = array() ) {

		return affiliate_wp()->referrals->add( array(
			'amount'       => affwp_calc_referral_amount( $amount, affiliate_wp()->tracking->get_affiliate_id() ),
			'reference'    => $reference,
			'affiliate_id' => affiliate_wp()->tracking->get_affiliate_id(),
			'visit_id'     => affiliate_wp()->tracking->get_visit_id(),
			'custom'       => ! empty( $data ) ? maybe_serialize( $data ) : ''
		) );

	}

	public function complete_referral( $reference = 0 ) {

		if( empty( $reference ) ) {
			return false;
		}

		if( affiliate_wp()->referrals->update( $reference, array( 'status' => 'unpaid' ), 'reference' ) ) {
			return true;
		}

		return false;

	}

}