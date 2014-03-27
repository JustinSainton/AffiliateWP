<?php

class Affiliate_WP_EDD extends Affiliate_WP_Base {

	public function init() {

		$this->context = 'edd';

		add_action( 'edd_insert_payment', array( $this, 'add_pending_referral' ), 10, 2 );
		add_action( 'edd_complete_purchase', array( $this, 'mark_referral_complete' ) );
		add_action( 'edd_update_payment_status', array( $this, 'revoke_referral_on_refund' ), 10, 3 );
		add_action( 'edd_payment_delete', array( $this, 'revoke_referral_on_delete' ), 10 );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );
	}

	public function add_pending_referral( $payment_id = 0, $payment_data = array() ) {

		if( $this->was_referred() ) {

			$this->insert_pending_referral( $payment_data['price'], $payment_id );
		
			$referral = affiliate_wp()->referrals->get_by( 'reference', $payment_id, 'edd' );
			$amount   = affwp_currency_filter( affwp_format_amount( $referral->amount ) );
			$name     = affiliate_wp()->affiliates->get_affiliate_name( $referral->affiliate_id );

			edd_insert_payment_note( $payment_id, sprintf( __( 'Referral #%d for %s recorded for %s', 'affiliate-wp' ),$referral->referral_id, $amount, $name ) );
		}

	}

	public function mark_referral_complete( $payment_id = 0 ) {

		$this->complete_referral( $payment_id );
	}


	public function revoke_referral_on_refund( $payment_id = 0, $new_status, $old_status ) {
	
		if( 'publish' != $old_status && 'revoked' != $old_status ) {
			return;
		}

		if( 'refunded' != $new_status ) {
			return;
		}

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		$this->reject_referral( $payment_id );

	}

	public function revoke_referral_on_delete( $payment_id = 0 ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		$this->reject_referral( $payment_id );

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