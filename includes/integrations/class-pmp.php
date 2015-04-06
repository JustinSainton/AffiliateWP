<?php

class Affiliate_WP_PMP extends Affiliate_WP_Base {
	
	public function init() {

		$this->context = 'pmp';

		add_action( 'pmpro_add_order', array( $this, 'add_pending_referral' ), 10 );
		add_action( 'pmpro_updated_order', array( $this, 'mark_referral_complete' ), 10 );
		add_action( 'admin_init', array( $this, 'revoke_referral_on_refund_and_cancel' ), 10);
		add_action( 'pmpro_delete_order', array( $this, 'revoke_referral_on_delete' ), 10, 2 );
		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );
	}

	public function add_pending_referral( $order ) {

		if( $this->was_referred() ) {

			$user = get_userdata( $order->user_id );

			if ( $this->is_affiliate_email( $user->user_email ) ) {
				return; // Customers cannot refer themselves
			}

			$referral_total = $this->calculate_referral_amount( $order->subtotal, $order->code );

			$referral_id = $this->insert_pending_referral( $referral_total, $order->code, $order->membership_name );

			if( 'success' === strtolower( $order->status ) ) {

				if( $referral_id ) {
					affiliate_wp()->referrals->update( $referral_id, array( 'custom' => $order->id ), '', 'referral' );
				}

				$this->complete_referral( $order->code );

			}
		}

	}

	public function mark_referral_complete( $order ) {

		if( 'success' !== strtolower( $order->status ) ) {
			return;
		}

		// Now update the referral to have a nice reference. PMP doesn't make the order ID available early enough
		$referral = affiliate_wp()->referrals->get_by( 'reference', $order->code );
		if( $referral ) {
			affiliate_wp()->referrals->update( $referral->referral_id, array( 'custom' => $order->id ), '', 'referral' );
		}

		$this->complete_referral( $order->code );
	}

	public function revoke_referral_on_refund_and_cancel() {

		/*
		 * PMP does not have hooks for when an order is refunded or voided, so we detect the form submission manually
		 */

		if( ! isset( $_REQUEST['save'] ) ) {
			return;
		}

		if( ! isset( $_REQUEST['order'] ) ) {
			return;
		}

		if( ! isset( $_REQUEST['status'] ) ) {
			return;
		}

		if( ! isset( $_REQUEST['membership_id'] ) ) {
			return;
		}

		if( 'refunded' != $_REQUEST['status'] ) {
			return;
		}

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		$this->reject_referral( absint( $_REQUEST['order'] ) );

	}

	public function revoke_referral_on_delete( $order_id = 0, $order ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		$this->reject_referral( $order_id );

	}

	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'pmp' != $referral->context ) {

			return $reference;

		}

		$url = admin_url( 'admin.php?page=pmpro-orders&order=' . $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}
	
}
new Affiliate_WP_PMP;