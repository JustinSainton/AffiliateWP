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

		add_action( 'mepr-txn-status-pending', array( $this, 'add_pending_referral' ), 10 );
		add_action( 'mepr-txn-status-complete', array( $this, 'mark_referral_complete' ), 10 );
		add_action( 'mepr-txn-status-refunded', array( $this, 'revoke_referral_on_refund' ), 10 );
	
		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

	}

	/**
	 * Store a pending referraling when a one-time product is purchased
	 *
	 * @access  public
	 * @since   1.5
	*/
	public function add_pending_referral( $txn ) {

		// Pending referrals are only created for one-time purchases
		if ( $this->was_referred() ) {

			$referral = affiliate_wp()->referrals->get_by( 'reference', $txn->id, $this->context );

			if ( ! empty( $referral ) ) {
				return;
			}

			$user = get_userdata( $txn->user_id );

			if ( $user && $this->get_affiliate_email() == $user->user_email ) {
				return; // Customers cannot refer themselves
			}

			// get referral total
			$referral_total = $this->calculate_referral_amount( $txn->amount, $txn->id, $txn->product_id );

			// insert a pending referral
			$this->insert_pending_referral( $referral_total, $txn->id, get_the_title( $txn->product_id ), array(), $txn->subscription_id );

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
		$this->complete_referral( $txn->id );
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

		$this->reject_referral( $txn->id );
	
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

		$url = admin_url( 'admin.php?page=memberpress-trans&search=' . $reference );
		
		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}
}
new Affiliate_WP_MemberPress;