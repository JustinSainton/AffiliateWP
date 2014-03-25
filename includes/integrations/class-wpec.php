<?php

class Affiliate_WP_WPEC extends Affiliate_WP_Base {
	
	public function init() {

		$this->content = 'wpec';

		add_action( 'wpsc_update_purchase_log_status', array( $this, 'add_pending_referral' ), 10, 4 );
		add_action( 'wpsc_update_purchase_log_status', array( $this, 'mark_referral_complete' ), 10, 4 );
	}

	public function add_pending_referral( $order_id = 0, $current_status, $previous_status, $order ) {


		if( $this->was_referred() ) {

			$this->insert_pending_referral( $order->get( 'totalprice' ), $order_id );
		}

	}

	public function mark_referral_complete( $order_id = 0, $current_status, $previous_status, $order ) {

		if( $order->is_transaction_completed() ) {

			$this->complete_referral( $order_id );

		}

		// TODO add order note about referral

	}
	
}
new Affiliate_WP_WPEC;