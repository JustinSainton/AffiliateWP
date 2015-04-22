<?php

class Affiliate_WP_MemberPress extends Affiliate_WP_Base {
	
	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.5
	*/
	public function init() {

		$this->context = 'memberpress';

		add_action( 'mepr-track-signup', array( $this, 'set_referred_flag_on_signup' ), 10, 4 );
		add_action( 'mepr-txn-status-pending', array( $this, 'add_pending_referral' ), 10 );
		add_action( 'mepr-txn-status-complete', array( $this, 'mark_referral_complete' ), 10 );
		add_action( 'mepr-txn-status-complete', array( $this, 'mark_subscription_referral_complete' ), 10 );
		add_action( 'mepr-txn-status-refunded', array( $this, 'revoke_referral_on_refund' ), 10 );
	
		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

	}

	/**
	 * Set a flag during a recurring signup so that we can identify this when the payment is processed
	 *
	 * @access  public
	 * @since   1.5
	*/
	public function set_referred_flag_on_signup( $product_price, $user, $product_id, $txn_id ) {

		if( $this->was_referred() ) {

			if ( $this->is_affiliate_email( $user->user_email ) ) {
				return; // Customers cannot refer themselves
			}

			$prd = new MeprProduct( $product_id );
			if( $prd->is_one_time_payment() ) {
				return;
			}

			// Calculate the referral total
			$referral_total = $this->calculate_referral_amount( $product_price, $user->ID, $product_id );

			// Store a pending referral
			$this->insert_pending_referral( $referral_total, $user->ID, get_the_title( $product_id ) );

		}

	}

	/**
	 * Store a pending referraling when a one-time product is purchased
	 *
	 * @access  public
	 * @since   1.5
	*/
	public function add_pending_referral( $txn ) {

		// Pending referrals are only created for one-time purchases

		if ( $this->was_referred() && empty( $txn->subscription_id ) ) {

			$referral = affiliate_wp()->referrals->get_by( 'reference', $txn->trans_num, $this->context );

			if ( ! empty( $referral ) ) {
				return;
			}

			$user = get_userdata( $txn->user_id );

			if ( $user && $this->get_affiliate_email() == $user->user_email ) {
				return; // Customers cannot refer themselves
			}

			// get referral total
			$referral_total = $this->calculate_referral_amount( $txn->amount, $txn->trans_num, $txn->product_id );

			// insert a pending referral
			$this->insert_pending_referral( $referral_total, $txn->trans_num, get_the_title( $txn->product_id ) );

		}
	}

	/**
	 * Update a referral to Unpaid when a one-time purchase is completed
	 *
	 * @access  public
	 * @since   1.5
	*/
	public function mark_referral_complete( $txn ) {

		// Completes a referral for a one-time purchase

		if( ! empty( $txn->subscription_id ) ) {
			return;
		}

		$this->complete_referral( $txn->trans_num );
	}

	/**
	 * Mark a referral from a subscription as Unpaid when the payment is completed
	 *
	 * @access  public
	 * @since   1.5
	*/
	public function mark_subscription_referral_complete( $txn ) {

		// Completes a referral for a subscription payment

		if( empty( $txn->subscription_id ) ) {
			return;
		}

		$subscription = $txn->subscription();

		// Only continue if this is the first subscription payment
		if( is_object( $subscription ) && $subscription->txn_count > 1 ) {
			return;
		}

		$referral = affiliate_wp()->referrals->get_by( 'reference', $txn->user_id, $this->context );

		if( ! $referral ) {
			return false; // Referral already created for this reference
		}

		affiliate_wp()->referrals->update_referral( $referral->referral_id, array( 'reference' => $txn->subscription_id, 'status' => 'unpaid' ) );

	}

	/**
	 * Reject referrals when the transaction is refunded
	 *
	 * @access  public
	 * @since   1.5
	*/
	public function revoke_referral_on_refund( $txn ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		if( empty( $txn->subscription_id ) ) {

			$this->reject_referral( $txn->trans_num );
	
		} else {

			$this->reject_referral( $txn->subscription_id );
		
		}
	
	}

	/**
	 * Setup the reference link
	 *
	 * @access  public
	 * @since   1.5
	*/
	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'memberpress' != $referral->context ) {

			return $reference;

		}

		if( ! empty( $referral->custom ) ) {

			$search = $referral->custom;

		} else {

			$search = $reference;

		}

		$url = admin_url( 'admin.php?page=memberpress-trans&search=' . $search );
		
		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}
}
new Affiliate_WP_MemberPress;