<?php

class Affiliate_WP_Shopp extends Affiliate_WP_Base {
	
	public function init() {
		add_action( '', array( $this, 'add_pending_referral' ), 10 );
		add_action( '', array( $this, 'mark_referral_complete' ), 10 );
	}

	public function add_pending_referral( $order_id = 0 ) {


		if( $this->was_referred() ) {

			$this->insert_pending_referral( $amount, $order_id );
		}

	}

	public function mark_referral_complete( $order_id = 0 ) {

		if( $order->is_transaction_completed() ) {

			$this->complete_referral( $order_id );

		}

		// TODO add order note about referral

	}
	
}
new Affiliate_WP_Shopp;