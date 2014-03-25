<?php

class Affiliate_WP_WooCommerce extends Affiliate_WP_Base {
	
	public function init() {

		$this->context = 'woocommerce';

		add_action( 'woocommerce_checkout_order_processed', array( $this, 'add_pending_referral' ), 10, 2 );
		
		// There should be an option to choose which of these is used
		add_action( 'woocommerce_order_status_completed', array( $this, 'mark_referral_complete' ), 10 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'mark_referral_complete' ), 10 );
	
		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

	}

	public function add_pending_referral( $order_id = 0, $posted ) {

		if( $this->was_referred() ) {

			$order  = new WC_Order( $order_id );
			$this->insert_pending_referral( $order->get_total(), $order_id );
		}

	}

	public function mark_referral_complete( $order_id = 0 ) {

		$order  = new WC_Order( $order_id );

		$this->complete_referral( $order_id );

		// TODO add order note about referral

	}

	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'woocommerce' != $referral->context ) {

			return $reference;

		}

		$url = get_edit_post_link( $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}
	
}
new Affiliate_WP_WooCommerce;