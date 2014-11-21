<?php

class Affiliate_WP_MemberPress extends Affiliate_WP_Base {
	
	public function init() {

		$this->context = 'memberpress';

		add_action( 'mepr-track-signup', array( $this, 'set_referred_flag' ), 10, 4 );
		add_action( 'mepr-txn-status-complete', array( $this, 'mark_referral_complete' ), 10 );
		add_action( 'mepr-txn-status-refunded', array( $this, 'revoke_referral_on_refund' ), 10 );
	
		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

	}

	public function set_referred_flag( $product_price, $user, $product_id, $txn_id ) {

		if( $this->was_referred() ) {

			if( $this->get_affiliate_email() == $user->user_email ) {
				return; // Customers cannot refer themselves
			}

			set_transient( '_affwp_memberpress_referred_' . $user->ID, '1', 7200 );
		}

	}

	public function mark_referral_complete( $txn ) {

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
		$this->insert_pending_referral( $referral_total, $txn->subscription_id, get_the_title( $txn->product_id ) );

		// Mark the referral as complete
		$this->complete_referral( $txn->subscription_id );

	}

	public function revoke_referral_on_refund( $txn ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		$this->reject_referral( $txn->subscription_id );
	
	}

	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'memberpress' != $referral->context ) {

			return $reference;

		}

		$url = admin_url( 'admin.php?page=memberpress-subscriptions&search=' . $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}
}
new Affiliate_WP_MemberPress;