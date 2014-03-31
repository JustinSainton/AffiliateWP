<?php

class Affiliate_WP_WooCommerce extends Affiliate_WP_Base {
	
	public function init() {

		$this->context = 'woocommerce';

		add_action( 'woocommerce_checkout_order_processed', array( $this, 'add_pending_referral' ), 10, 2 );
		
		// There should be an option to choose which of these is used
		add_action( 'woocommerce_order_status_completed', array( $this, 'mark_referral_complete' ), 10 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'mark_referral_complete' ), 10 );
		add_action( 'woocommerce_order_status_completed_to_refunded', array( $this, 'revoke_referral_on_refund' ), 10 );
		add_action( 'woocommerce_order_status_on-hold_to_refunded', array( $this, 'revoke_referral_on_refund' ), 10 );
		add_action( 'woocommerce_order_status_processing_to_refunded', array( $this, 'revoke_referral_on_refund' ), 10 );
		add_action( 'woocommerce_order_status_processing_to_cancelled', array( $this, 'revoke_referral_on_refund' ), 10 );
		add_action( 'woocommerce_order_status_completed_to_cancelled', array( $this, 'revoke_referral_on_refund' ), 10 );
	
		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

	}

	public function add_pending_referral( $order_id = 0, $posted ) {

		if( $this->was_referred() ) {

			$order       = new WC_Order( $order_id );

			$description = ''; 
			$items       = $order->get_items();
			foreach( $items as $key => $item ) {
				$description .= $item['name'];
				if( $key + 1 < count( $items ) ) {
					$description .= ', ';
				}
			}

			$this->insert_pending_referral( $order->get_total(), $order_id, $description );
		
			$referral = affiliate_wp()->referrals->get_by( 'reference', $order_id, $this->context );
			$amount   = affwp_currency_filter( affwp_format_amount( $referral->amount ) );
			$name     = affiliate_wp()->affiliates->get_affiliate_name( $referral->affiliate_id );

			$order->add_order_note( sprintf( __( 'Referral #%d for %s recorded for %s', 'affiliate-wp' ),$referral->referral_id, $amount, $name ) );

		}

	}

	public function mark_referral_complete( $order_id = 0 ) {

		$this->complete_referral( $order_id );

	}

	public function revoke_referral_on_refund( $order_id = 0 ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		$this->reject_referral( $order_id );

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