<?php

class Affiliate_WP_RCP extends Affiliate_WP_Base {
	
	public function init() {

		$this->context = 'rcp';

		add_action( 'rcp_form_processing', array( $this, 'add_pending_referral' ), 10, 3 );
		add_action( 'rcp_insert_payment', array( $this, 'mark_referral_complete' ), 10, 3 );
		//add_action( 'rcp_delete_payment', array( $this, 'revoke_referral_on_delete' ), 10 );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );
	}

	public function add_pending_referral( $post_data, $user_id, $price ) {


		if( $this->was_referred() ) {

			$key = get_user_meta( $user_id, 'rcp_subscription_key', true );

			$this->insert_pending_referral( $price, $key );
		}

	}

	public function mark_referral_complete( $payment_id, $args, $amount ) {

		$this->complete_referral( $args['subscription_key'] );
	
	}

	public function revoke_referral_on_delete( $payment_id = 0 ) {

		// TODO: fix this, it doesn't fire when RCP payments are deleted

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		$payments = new RCP_Payments;
		$payment  = $payments->get_payment( $payment_id );
		$this->reject_referral( $payment->subscription_key );

	}
	
}
new Affiliate_WP_RCP;