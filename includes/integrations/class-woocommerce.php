<?php

class Affiliate_WP_WooCommerce extends Affiliate_WP_Base {
	
	private $content;

	public function init() {

		$this->context = 'woocommerce';

		add_action( 'woocommerce_checkout_order_processed', array( $this, 'add_pending_referral' ), 10, 2 );
		add_action( 'woocommerce_payment_complete', array( $this, 'mark_referral_complete' ), 10 );
	}

	public function add_pending_referral( $order_id = 0, $posted ) {


		if( $this->was_referred() ) {

			$order  = new WC_Order( $order_id );
			$this->insert_pending_referral( $order->get_total(), $order->get_order_number() );
		}

	}

	public function mark_referral_complete( $order_id = 0 ) {

		$order  = new WC_Order( $order_id );

		$this->complete_referral( $order->get_order_number() );

		// TODO add order note about referral

	}
	
}
new Affiliate_WP_WooCommerce;