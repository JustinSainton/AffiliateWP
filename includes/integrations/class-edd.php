<?php

class Affiliate_WP_EDD extends Affiliate_WP_Base {

	private $context;

	public function init() {

		$this->context = 'edd';

		add_action( 'edd_insert_payment', array( $this, 'add_pending_referral' ), 10, 2 );
		add_action( 'edd_complete_purchase', array( $this, 'complete_referral' ) );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );
	}

	public function add_pending_referral( $payment_id = 0, $payment_data = array() ) {

		if( $this->was_referred() ) {
			$this->insert_pending_referral( $payment_data['price'], $payment_id );
		}

	}

	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'edd' != $referral->context ) {

			return $reference;

		}

		$url = admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}

	// TODO add note about referral

}
new Affiliate_WP_EDD;