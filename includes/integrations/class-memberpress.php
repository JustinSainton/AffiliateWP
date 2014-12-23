<?php

class Affiliate_WP_MemberPress extends Affiliate_WP_Base {
	
	public function init() {

		$this->context = 'memberpress';

		add_action( 'mepr-track-signup', array( $this, 'set_referred_flag_on_signup' ), 10, 4 );
		add_action( 'mepr-txn-status-pending', array( $this, 'add_pending_referral' ), 10 );
		add_action( 'mepr-txn-status-complete', array( $this, 'mark_referral_complete' ), 10 );
		add_action( 'mepr-txn-status-complete', array( $this, 'mark_subscription_referral_complete' ), 10 );
		add_action( 'mepr-txn-status-refunded', array( $this, 'revoke_referral_on_refund' ), 10 );
	
		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

	}

	public function set_referred_flag_on_signup( $product_price, $user, $product_id, $txn_id ) {

		if( $this->was_referred() ) {

			if( $this->get_affiliate_email() == $user->user_email ) {
				return; // Customers cannot refer themselves
			}

			set_transient( '_affwp_memberpress_referred_' . $user->ID, '1', 7200 );
		}

	}

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

	public function mark_referral_complete( $txn ) {

		// Completes a referral for a one-time purchase

		if( ! empty( $txn->subscription_id ) ) {
			return;
		}

		$this->complete_referral( $txn->trans_num );
	}

	public function mark_subscription_referral_complete( $txn ) {

		// Completes a referral for a subscription payment

		if( false === get_transient( '_affwp_memberpress_referred_' . $txn->user_id ) ) {
			return; // This was not a referred sign up
		}

		$subscription = $txn->subscription();

		// Only continue if this is the first subscription payment
		if( $subscription->txn_count > 1 ) {
			return;
		}

		// Calculate the referral total
		$referral_total = $this->calculate_referral_amount( $txn->amount, $txn->subscription_id, $txn->product_id );

		// Store a pending referral
		$this->insert_pending_referral( $referral_total, $txn->subscription_id, get_the_title( $txn->product_id ), $txn->trans_num );

		// Mark the referral as complete
		$this->complete_referral( $txn->subscription_id );

	}

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