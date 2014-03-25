<?php

class Affiliate_WP_Base {

	private $context;

	public function __construct() {
		$this->init();
	}

	public function init() {

	}

	public function was_referred() {
		return affiliate_wp()->tracking->was_referred();
	}

	public function insert_pending_referral( $amount = '', $reference = 0, $data = array() ) {

		if( affiliate_wp()->referrals->get_by( 'reference', $reference ) ) {
			return false; // Referral already created for this reference
		}

		return affiliate_wp()->referrals->add( array(
			'amount'       => affwp_calc_referral_amount( $amount, affiliate_wp()->tracking->get_affiliate_id(), $reference ),
			'reference'    => $reference,
			'affiliate_id' => affiliate_wp()->tracking->get_affiliate_id(),
			'visit_id'     => affiliate_wp()->tracking->get_visit_id(),
			'custom'       => ! empty( $data ) ? maybe_serialize( $data ) : '',
			'context'      => $this->context
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

		if( is_object( $referral ) && $referral->status != 'pending' ) {
			// This referral has already been completed, rejected, or paid
			return false;
		}

		if( affiliate_wp()->referrals->update( $referral->referral_id, array( 'status' => 'unpaid' ) ) ) {
			
			// Update the visit ID that spawned this referral
			affiliate_wp()->visits->update( $referral->visit_id, array( 'referral_id' => $referral->referral_id ) );

			do_action( 'affwp_complete_referral', $referral->referral_id, $referral, $reference );

			return true;
		}

		return false;

	}

}