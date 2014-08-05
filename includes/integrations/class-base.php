<?php

abstract class Affiliate_WP_Base {

	public $context;

	public function __construct() {
		$this->init();
	}

	public function init() {

	}

	public function was_referred() {
		return affiliate_wp()->tracking->was_referred();
	}

	public function insert_pending_referral( $amount = '', $reference = 0, $description = '', $data = array() ) {

		if( ! (bool) apply_filters( 'affwp_integration_create_referral', true, $this->context ) ) {
			return false; // Allow extensions to prevent referrals from being created
		}

		if( affiliate_wp()->referrals->get_by( 'reference', $reference, $this->context ) ) {
			return false; // Referral already created for this reference
		}

		if( 0 == $amount && affiliate_wp()->settings->get( 'ignore_zero_referrals' ) ) {
			return false; // Ignore a zero amount referral
		}

		return affiliate_wp()->referrals->add( array(
			'amount'       => $amount,
			'reference'    => $reference,
			'description'  => $description,
			'affiliate_id' => affiliate_wp()->tracking->get_affiliate_id(),
			'visit_id'     => affiliate_wp()->tracking->get_visit_id(),
			'custom'       => ! empty( $data ) ? maybe_serialize( $data ) : '',
			'context'      => $this->context
		) );

	}

	public function complete_referral( $reference = 0 ) {
		if ( empty( $reference ) ) {
			return false;
		}

		$referral = affiliate_wp()->referrals->get_by( 'reference', $reference, $this->context );

		if ( empty( $referral ) ) {
			return false;
		}

		if ( is_object( $referral ) && $referral->status != 'pending' ) {
			// This referral has already been completed, rejected, or paid
			return false;
		}

		if ( ! apply_filters( 'affwp_auto_complete_referral', true ) )
			return false;

		if ( affwp_set_referral_status( $referral->referral_id, 'unpaid' ) ) {

			// Update the visit ID that spawned this referral
			affiliate_wp()->visits->update( $referral->visit_id, array( 'referral_id' => $referral->referral_id ) );

			do_action( 'affwp_complete_referral', $referral->referral_id, $referral, $reference );

			return true;
		}

		return false;

	}

	public function reject_referral( $reference = 0 ) {
		if ( empty( $reference ) ) {
			return false;
		}

		$referral = affiliate_wp()->referrals->get_by( 'reference', $reference, $this->context );

		if( empty( $referral ) ) {
			return false;
		}

		if( is_object( $referral ) && 'paid' == $referral->status ) {
			// This referral has already been paid so it cannot be rejected
			return false;
		}

		if( affiliate_wp()->referrals->update( $referral->referral_id, array( 'status' => 'rejected' ) ) ) {

			return true;

		}

		return false;

	}

	public function get_affiliate_email() {
		return affwp_get_affiliate_email( affiliate_wp()->tracking->get_affiliate_id() );
	}

	/**
	 * Retrieves the rate and type for a specific product
	 *
	 * @access  public
	 * @since   1.2
	 * @return  array
	*/
	public function calculate_referral_amount( $base_amount = '', $reference = '', $product_id = 0 ) {

		$rate         = '';
		$affiliate_id = affiliate_wp()->tracking->get_affiliate_id();

		if( ! empty( $product_id ) ) {

			$rate = $this->get_product_rate( $product_id );
			$type = affwp_get_affiliate_rate_type( $affiliate_id );

			if ( 'percentage' == $type ) {

				// Sanitize the rate and ensure it's in the proper format
				if ( $rate > 1 ) {

					$rate = $rate / 100;
				
				}

			}

		}

		$amount = affwp_calc_referral_amount( $base_amount, $affiliate_id, $reference, $rate );
	
		return $amount;

	}


	/**
	 * Retrieves the rate and type for a specific product
	 *
	 * @access  public
	 * @since   1.2
	 * @return  float
	*/
	public function get_product_rate( $product_id = 0 ) {

		$rate = get_post_meta( $product_id, '_affwp_' . $this->context . '_product_rate', true );

		if( empty( $rate ) ) {

			$rate = affwp_get_affiliate_rate();

		}

		return (float) $rate;

	}

}