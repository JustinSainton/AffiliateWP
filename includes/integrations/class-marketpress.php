<?php

class Affiliate_WP_MarketPress extends Affiliate_WP_Base {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.6
	*/
	public function init() {

		$this->context = 'marketpress';

		add_action( 'mp_new_order', array( $this, 'add_pending_referral' ) );
		add_action( 'mp_order_paid', array( $this, 'mark_referral_complete' ) );
		add_action( 'trash_mp_order', array( $this, 'revoke_referral_on_delete' ), 10, 2 );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

	}

	/**
	 * Record a pending referral
	 *
	 * @access  public
	 * @since   1.6
	*/
	public function add_pending_referral( $order = array() ) {

		if ( $this->was_referred() ) {

			if( 0 == $order->post_author ) {

				$customer_email = $order->mp_shipping_info[ 'email' ];

			} else {

				$user_id        = $order->post_author;
				$user           = get_userdata( $user_id );
				$customer_email = $user->user_email;

			}

			if ( $this->is_affiliate_email( $customer_email ) ) {

				return; // Customers cannot refer themselves

			}

		    $amount      = $order->mp_order_total;
			$order_id    = $order->ID;
		    $description = array();
		    $items       = $order->mp_cart_info;

		    foreach( $items as $item ) {

		        $order_items = $item;

		        foreach( $order_items as $order_item ) {
		            $description[] .= $order_item['name'];
		        }

		    }

		    $description = join( ', ', $description );

			if( affiliate_wp()->settings->get( 'exclude_tax' ) ) {

				$amount -= $order->mp_tax_total;

			}

			if( affiliate_wp()->settings->get( 'exclude_shipping' ) ) {

				$amount -= $order->mp_shipping_total;

			}

			$referral_total = $this->calculate_referral_amount( $amount, $order_id );

			$this->insert_pending_referral( $referral_total, $order_id, $description );
		}

	}

	/**
	 * Mark a referral as complete when an order is completed
	 *
	 * @access  public
	 * @since   1.6
	*/
	public function mark_referral_complete( $order = array() ) {

		$order_id = $order->ID;

		$referral = affiliate_wp()->referrals->get_by( 'reference', $order_id, $this->context );

		/*
		 * Add pending referral if referral not yet created because mp_order_paid hook is executed before
		 * mp_order_paid, this prevent completed referral being marked as pending
		 */
		if ( empty( $referral ) ) {

			$this->add_pending_referral( $order );

		}

		$this->complete_referral( $order_id );

	}

	/**
	 * Revoke a referral when an order is deleted
	 *
	 * @access  public
	 * @since   1.6
	*/
	public function revoke_referral_on_delete( $order_id = 0, $post ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {

			return;

		}

		if( 'mp_order' != get_post_type( $order_id ) ) {

			return;

		}

		$this->reject_referral( $order_id );

	}

	/**
	 * Set up the reference URL from the referral to the order
	 *
	 * @access  public
	 * @since   1.6
	*/
	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'marketpress' != $referral->context ) {

			return $reference;

		}

		$url = admin_url( 'edit.php?post_type=product&page=marketpress-orders&order_id=' . $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';

	}

}
new Affiliate_WP_MarketPress;