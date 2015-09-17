<?php

abstract class Affiliate_WP_Base {

	/**
	 * The context for referrals. This refers to the integration that is being used.
	 *
	 * @access  public
	 * @since   1.2
	 */
	public $context;

	/**
	 * The ID of the referring affiliate
	 *
	 * @access  public
	 * @since   1.2
	 */
	public $affiliate_id;

	/**
	 * Constructor
	 *
	 * @access  public
	 * @since   1.0
	 */
	public function __construct() {

		$this->affiliate_id = affiliate_wp()->tracking->get_affiliate_id();

		$this->init();

	}

	/**
	 * Gets things started
	 *
	 * @access  public
	 * @since   1.0
	 * @return  void
	 */
	public function init() {

	}

	/**
	 * Determines if the current session was referred through an affiliate link
	 *
	 * @access  public
	 * @since   1.0
	 * @return  bool
	 */
	public function was_referred() {
		return affiliate_wp()->tracking->was_referred();
	}

	/**
	 * Inserts a pending referral. Used when orders are initially created
	 *
	 * @access  public
	 * @since   1.0
	 * @param   $amount The final referral commission amount
	 * @param   $reference The reference column for the referral per the current context
	 * @param   $description A plaintext description of the refferral
	 * @param   $products An array of product details
	 * @param   $data Any custom data that can be passed to and stored with the referral
	 * @return  bool
	 */
	public function insert_pending_referral( $amount = '', $reference = 0, $description = '', $products = array(), $data = array() ) {

		if( ! (bool) apply_filters( 'affwp_integration_create_referral', true, $this->context ) ) {
			return false; // Allow extensions to prevent referrals from being created
		}

		if( affiliate_wp()->referrals->get_by( 'reference', $reference, $this->context ) ) {
			return false; // Referral already created for this reference
		}

		if( empty( $amount ) && affiliate_wp()->settings->get( 'ignore_zero_referrals' ) ) {
			return false; // Ignore a zero amount referral
		}

		$visit_id = affiliate_wp()->tracking->get_visit_id();

		$args = apply_filters( 'affwp_insert_pending_referral', array(
			'amount'       => $amount,
			'reference'    => $reference,
			'description'  => $description,
			'campaign'     => affiliate_wp()->tracking->get_campaign(),
			'affiliate_id' => $this->affiliate_id,
			'visit_id'     => $visit_id,
			'products'     => ! empty( $products ) ? maybe_serialize( $products ) : '',
			'custom'       => ! empty( $data ) ? maybe_serialize( $data ) : '',
			'context'      => $this->context
		), $amount, $reference, $description, $this->affiliate_id, $visit_id, $data, $this->context );

		return affiliate_wp()->referrals->add( $args );

	}

	/**
	 * Completes a referal. Used when orders are marked as completed
	 *
	 * @access  public
	 * @since   1.0
	 * @param   $reference The reference column for the referral to complete per the current context
	 * @return  bool
	 */
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

			do_action( 'affwp_complete_referral', $referral->referral_id, $referral, $reference );

			return true;
		}

		return false;

	}

	/**
	 * Rejects a referal. Used when orders are refunded, deleted, or voided
	 *
	 * @access  public
	 * @since   1.0
	 * @param   $reference The reference column for the referral to reject per the current context
	 * @return  bool
	 */
	public function reject_referral( $reference = 0 ) {
		if ( empty( $reference ) ) {
			return false;
		}

		$referral = affiliate_wp()->referrals->get_by( 'reference', $reference, $this->context );

		if ( empty( $referral ) ) {
			return false;
		}

		if ( is_object( $referral ) && 'paid' == $referral->status ) {
			// This referral has already been paid so it cannot be rejected
			return false;
		}

		if ( affiliate_wp()->referrals->update( $referral->referral_id, array( 'status' => 'rejected' ), '', 'referral' ) ) {

			return true;

		}

		return false;

	}

	/**
	 * Retrieves the ID of the referring affiliate
	 *
	 * @access  public
	 * @since   1.0
	 * @return  int
	 */
	public function get_affiliate_id() {
		return absint( apply_filters( 'affwp_get_referring_affiliate_id', $this->affiliate_id ) );
	}

	/**
	 * Retrieves the email address of the referring affiliate
	 *
	 * @access  public
	 * @since   1.0
	 * @return  string
	 */
	public function get_affiliate_email() {
		return affwp_get_affiliate_email( $this->get_affiliate_id() );
	}

	/**
	 * Determine if the passed email belongs to the affiliate
	 *
	 * Checks a given email address against the referring affiliate's
	 * user email and payment email addresses to prevent customers from
	 * referring themselves.
	 *
	 * @access  public
	 * @since   1.6
	 * @param   string $email
	 * @return  bool
	 */
	public function is_affiliate_email( $email ) {

		$is_affiliate_email = false;

		// Get affiliate emails
		$user_email    = affwp_get_affiliate_email( $this->affiliate_id );
		$payment_email = affwp_get_affiliate_payment_email( $this->affiliate_id );

		// True if the email is valid and matches affiliate user email or payment email, otherwise false
		$is_affiliate_email = ( is_email( $email ) && ( $user_email === $email || $payment_email === $email ) );

		return (bool) apply_filters( 'affwp_is_customer_email_affiliate_email', $is_affiliate_email, $email, $this->affiliate_id );

	}

	/**
	 * Retrieves the rate and type for a specific product
	 *
	 * @access  public
	 * @since   1.2
	 * @return  array
	 */
	public function calculate_referral_amount( $base_amount = '', $reference = '', $product_id = 0 ) {

		$rate = '';

		$this->affiliate_id = $this->get_affiliate_id();

		if ( ! empty( $product_id ) ) {

			$rate = $this->get_product_rate( $product_id, $args = array( 'reference' => $reference ) );

		}

		$amount = affwp_calc_referral_amount( $base_amount, $this->affiliate_id, $reference, $rate, $product_id );

		return $amount;

	}

	/**
	 * Retrieves the rate and type for a specific product
	 *
	 * @access  public
	 * @since   1.2
	 * @return  float
	*/
	public function get_product_rate( $product_id = 0, $args = array() ) {

		$rate = get_post_meta( $product_id, '_affwp_' . $this->context . '_product_rate', true );

		return apply_filters( 'affwp_get_product_rate', $rate, $product_id, $args, $this->affiliate_id, $this->context );

	}

	/**
	 * Retrieves the product details array for the referral
	 *
	 * @access  public
	 * @since   1.6
	 * @return  array
	*/
	public function get_products( $order_id = 0 ) {

		return array();

	}

}
