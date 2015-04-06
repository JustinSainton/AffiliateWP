<?php
/**
 * AffiliateWP Jigoshop Integration
 *
 * This integrates support for Jigoshop.
 * @since version: 1.0.2
 */

class Affiliate_WP_Jigoshop extends Affiliate_WP_Base {

	/**
	 * The order object
	 *
	 * @access  private
	 * @since   1.3
	*/
	private $order;


	/**
	 * Initiate
	 *
	 * @function init()
	 * @access public
	 */
	public function init() {
		$this->context = 'jigoshop';

		// Actions
		add_action( 'jigoshop_new_order', array( $this, 'add_pending_referral' ), 10, 1 ); // Referral added when order is made.
		add_action( 'jigoshop_order_status_completed', array( $this, 'mark_referral_complete' ), 10 ); // Referral is marked complete when order is completed.
		add_action( 'jigoshop_order_status_completed_to_refunded', array( $this, 'revoke_referral_on_refund' ), 10 ); // Referral is revoked when order has been refunded.

		// Filters
		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );
	}

	/**
	 * Add pending referral
	 *
	 * @function add_pending_referral
	 * @access public
	 */
	public function add_pending_referral( $order_id = 0 ) {

		if( $this->was_referred() ) {

			$this->order = apply_filters( 'affwp_get_jigoshop_order', new jigoshop_order( $order_id ) ); // Fetch order

			if ( $this->is_affiliate_email( $this->order->billing_email ) ) {
				return; // Customers cannot refer themselves
			}

			$description = ''; 
			$items       = $this->order->items;
			foreach( $items as $key => $item ) {
				$description .= $item['name'];
				if( $key + 1 < count( $items ) ) {
					$description .= ', ';
				}
			}

			$amount = $this->order->order_total;

			if( affiliate_wp()->settings->get( 'exclude_tax' ) ) {

				$amount -= $this->order->get_total_tax();

			}

			if( affiliate_wp()->settings->get( 'exclude_shipping' ) ) {

				$amount -= $this->order->order_shipping;

			}

			$referral_total = $this->calculate_referral_amount( $amount, $order_id );

			$this->insert_pending_referral( $referral_total, $order_id, $description );

			$referral = affiliate_wp()->referrals->get_by( 'reference', $order_id, $this->context );
			$amount   = affwp_currency_filter( affwp_format_amount( $referral->amount ) );
			$name     = affiliate_wp()->affiliates->get_affiliate_name( $referral->affiliate_id );

			$this->order->add_order_note( sprintf( __( 'Referral #%d for %s recorded for %s', 'affiliate-wp' ), $referral->referral_id, $amount, $name ) );

		}

	}

	/**
	 * Mark referral complete
	 *
	 * @function mark_referral_complete()
	 * @access public
	 */
	public function mark_referral_complete( $order_id = 0 ) {

		$this->complete_referral( $order_id );

	}

	/**
	 * Revoke referral on refund
	 *
	 * @function revoke_referral_on_refund()
	 * @access public
	 */
	public function revoke_referral_on_refund( $order_id = 0 ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		$this->reject_referral( $order_id );

	}

	/**
	 * Reference link
	 *
	 * @function reference_link()
	 * @access public
	 */
	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'jigoshop' != $referral->context ) {

			return $reference;

		}

		$url = get_edit_post_link( $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}

}

new Affiliate_WP_Jigoshop;