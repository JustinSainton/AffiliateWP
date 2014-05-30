<?php

class Affiliate_WP_Exchange extends Affiliate_WP_Base {

	public function init() {

		$this->context = 'it-exchange';

		add_action( 'it_exchange_add_transaction_success', array( $this, 'add_pending_referral' ), 10 );
		add_action( 'it_exchange_update_transaction_status', array( $this, 'mark_referral_complete' ), 10, 3 );
		add_action( 'it_exchange_update_transaction_status', array( $this, 'revoke_referral_on_refund' ), 10, 3 );
		add_action( 'it_exchange_update_transaction_status', array( $this, 'revoke_referral_on_void' ), 10, 3 );
		add_action( 'wp_trash_post', array( $this, 'revoke_referral_on_delete' ), 10 );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );
	}

	public function add_pending_referral( $transaction_id = 0 ) {

		if( $this->was_referred() ) {

			$transaction = get_post_meta( $transaction_id, '_it_exchange_cart_object', true );

			if( $this->get_affiliate_email() == $transaction->shipping_address['email'] ) {
				return; // Customers cannot refer themselves
			}

			$this->insert_pending_referral( $transaction->total, $transaction_id, $transaction->description );
	
		}

	}

	public function mark_referral_complete( $transaction, $old_status, $old_status_cleared ) {

		$new_status         = it_exchange_get_transaction_status( $transaction->ID );
		$new_status_cleared = it_exchange_transaction_is_cleared_for_delivery( $transaction->ID );

		if ( ( $new_status != $old_status ) && ! $old_status_cleared && $new_status_cleared ) {

			$this->complete_referral( $transaction->ID );

		}

	}

	public function revoke_referral_on_refund( $transaction, $old_status, $old_status_cleared ) {
	
		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		if( 'refunded' == $transaction->get_status() && 'paid' == $old_status ) {

			$this->reject_referral( $transaction->ID );

		}

	}

	public function revoke_referral_on_void( $transaction, $old_status, $old_status_cleared ) {
	
		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		if( 'voided' == $transaction->get_status() ) {

			$this->reject_referral( $transaction->ID );

		}

	}

	public function revoke_referral_on_delete( $transaction_id = 0 ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		$post = get_post( $transaction_id );

		if( ! $post ) {
			return;
		}

		if( 'it_exchange_tran' != $post->post_type ) {
			return;
		}

		$this->reject_referral( $transaction_id );

	}

	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'it-exchange' != $referral->context ) {

			return $reference;

		}

		$url = get_edit_post_link( $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}

}
new Affiliate_WP_Exchange;