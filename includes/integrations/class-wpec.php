<?php

class Affiliate_WP_WPEC extends Affiliate_WP_Base {

	public function init() {

		$this->context = 'wpec';

		add_action( 'wpsc_update_purchase_log_status', array( $this, 'add_pending_referral' ), 10, 4 );
		add_action( 'wpsc_update_purchase_log_status', array( $this, 'mark_referral_complete' ), 10, 4 );
		add_action( 'wpsc_update_purchase_log_status', array( $this, 'revoke_referral_on_refund' ), 10, 4 );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

	}

	public function add_pending_referral( $order_id = 0, $current_status, $previous_status, $order ) {


		if( $this->was_referred() ) {

			if ( $this->is_affiliate_email( wpsc_get_buyers_email( $order_id ) ) ) {
				return; // Customers cannot refer themselves
			}

			$description = '';
			$items = $order->get_cart_contents();
			foreach( $items as $key => $item ) {
				$description .= $item->name;
				if( $key + 1 < count( $items ) ) {
					$description .= ', ';
				}
			}

			$amount = $order->get( 'totalprice' );

			if( affiliate_wp()->settings->get( 'exclude_tax' ) ) {

				$amount -= $order->get( 'wpec_taxes_total' );

			}

			if( affiliate_wp()->settings->get( 'exclude_shipping' ) ) {

				$amount -= $order->get( 'total_shipping' );

			}

			$referral_total = $this->calculate_referral_amount( $amount, $order_id );

			$this->insert_pending_referral( $referral_total, $order_id, $description );
		}

	}

	public function mark_referral_complete( $order_id = 0, $current_status, $previous_status, $order ) {

		if( $order->is_transaction_completed() ) {

			$this->complete_referral( $order_id );

		}

		// TODO add order note about referral

	}

	public function revoke_referral_on_refund( $order_id = 0, $current_status, $previous_status, $order ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		if( $order->is_refunded() || $order->is_payment_declined() ) {

			$this->reject_referral( $order_id );

		}

	}

	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'wpec' != $referral->context ) {

			return $reference;

		}

		$url = admin_url( 'index.php?page=wpsc-purchase-logs&c=item_details&id=' . $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}

}
new Affiliate_WP_WPEC;