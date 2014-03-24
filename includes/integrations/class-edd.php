<?php

class Affiliate_WP_EDD extends Affiliate_WP_Base {
	
	public function init() {
		add_action( 'edd_insert_payment', array( $this, 'add_pending_referral' ), 10, 2 );
		add_action( 'edd_complete_purchase', array( $this, 'complete_referral' ) );
	}

	public function add_pending_referral( $payment_id = 0, $payment_data = array() ) {

		if( $this->was_referred() ) {
			$this->insert_pending_referral( $payment_data['price'], $payment_id );
		}

	}
	
}
new Affiliate_WP_EDD;