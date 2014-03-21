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

		$referral = affiliate_wp()->referrals->get_by( 'reference', $reference );

		if( empty( $referral ) ) {
			return false;
		}

		if( affiliate_wp()->referrals->update( $reference, array( 'status' => 'unpaid' ), 'reference' ) ) {
			
			// Update the visit ID that spawned this referral
			affiliate_wp()->visits->update( $referral->visit_id, array( 'referral_id' => $referral_id ) );

			do_action( 'affwp_complete_referral', $referral_id, $referral, $reference );

			return true;
		}

		return false;

	}

}