<?php

class Affiliate_WP_RCP extends Affiliate_WP_Base {
	
	public function init() {
		add_action( 'rcp_form_processing', array( $this, 'add_pending_referral' ), 10, 3 );
		add_action( 'rcp_insert_payment', array( $this, 'mark_referral_complete' ), 10, 3 );
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
	
}
new Affiliate_WP_RCP;