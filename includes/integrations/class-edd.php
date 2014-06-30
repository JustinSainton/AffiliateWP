<?php

class Affiliate_WP_EDD extends Affiliate_WP_Base {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function init() {

		$this->context = 'edd';

		add_action( 'edd_insert_payment', array( $this, 'add_pending_referral' ), 10, 2 );
		add_action( 'edd_complete_purchase', array( $this, 'track_discount_referral' ), 10 );
		add_action( 'edd_complete_purchase', array( $this, 'mark_referral_complete' ) );
		add_action( 'edd_update_payment_status', array( $this, 'revoke_referral_on_refund' ), 10, 3 );
		add_action( 'edd_payment_delete', array( $this, 'revoke_referral_on_delete' ), 10 );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

		// Discount code tracking actions and filters
		add_action( 'edd_add_discount_form_bottom', array( $this, 'discount_edit' ) );
		add_action( 'edd_edit_discount_form_bottom', array( $this, 'discount_edit' ) );
		add_action( 'edd_post_update_discount', array( $this, 'store_discount_affiliate' ), 10, 2 );
		add_action( 'edd_post_insert_discount', array( $this, 'store_discount_affiliate' ), 10, 2 );
	}

	/**
	 * Records a pending referral when a pending payment is created
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function add_pending_referral( $payment_id = 0, $payment_data = array() ) {

		if( $this->was_referred() ) {

			$customer_email = edd_get_payment_user_email( $payment_id );

			if( $this->get_affiliate_email() == $customer_email ) {
				return; // Customers cannot refer themselves
			}

			$inserted = $this->insert_pending_referral( $payment_data['price'], $payment_id, $this->get_referral_description( $payment_id ) );

			if ( false !== $inserted ) { //only continue if the insert was a success

				$referral = affiliate_wp()->referrals->get_by( 'reference', $payment_id, 'edd' );
				$amount   = affwp_currency_filter( affwp_format_amount( $referral->amount ) );
				$name     = affiliate_wp()->affiliates->get_affiliate_name( $referral->affiliate_id );

				edd_insert_payment_note( $payment_id, sprintf( __( 'Referral #%d for %s recorded for %s', 'affiliate-wp' ),$referral->referral_id, $amount, $name ) );
			}
		}

	}

	/**
	 * Records referrals for the affiliate if a discount code belonging to the affiliate is used
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function track_discount_referral( $payment_id = 0 ) {

		$user_info = edd_get_payment_meta_user_info( $payment_id );

		if ( isset( $user_info['discount'] ) && $user_info['discount'] != 'none' ) {

			$discounts = array_map( 'trim', explode( ',', $user_info['discount'] ) );

			if( empty( $discounts ) ) {
				return;
			}

			foreach( $discounts as $code ) {

				$discount_id  = edd_get_discount_id_by_code( $code );
				$affiliate_id = get_post_meta( $discount_id, 'affwp_discount_affiliate', true );

				if( ! $affiliate_id ) {
					continue;
				}

				$existing = affiliate_wp()->referrals->get_by( 'reference', $payment_id, $this->context );

				if( $existing ) {

					// If a referral was already recored, overwrite it with the affiliate from the coupon
					affiliate_wp()->referrals->update( $existing, array( 'affiliate_id' => $affiliate_id, 'status' => 'unpaid' ) );

				} else {

					$amount = edd_get_payment_subtotal( $payment_id );
					$amount = affwp_calc_referral_amount( $amount, $affiliate_id );

					if( 0 == $amount && affiliate_wp()->settings->get( 'ignore_zero_referrals' ) ) {
						return false; // Ignore a zero amount referral
					}

					$referral_id = affiliate_wp()->referrals->add( array(
						'amount'       => $amount,
						'reference'    => $payment_id,
						'description'  => $this->get_referral_description( $payment_id ),
						'affiliate_id' => $affiliate_id,
						'context'      => $this->context
					) );

					affwp_set_referral_status( $referral_id, 'unpaid' );

					$amount   = affwp_currency_filter( affwp_format_amount( $amount ) );
					$name     = affiliate_wp()->affiliates->get_affiliate_name( $affiliate_id );

					edd_insert_payment_note( $payment_id, sprintf( __( 'Referral #%d for %s recorded for %s', 'affiliate-wp' ), $referral_id, $amount, $name ) );

				}
			}
		}

	}

	/**
	 * Sets a referral to unpaid when payment is completed
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function mark_referral_complete( $payment_id = 0 ) {

		$this->complete_referral( $payment_id );
	}

	/**
	 * Revokes a referral when payment is refunded
	 *
	 * @access  public
	 * @since   1.0
	*/
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

	/**
	 * Revokes a referral when a payment is deleted
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function revoke_referral_on_delete( $payment_id = 0 ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		$this->reject_referral( $payment_id );

	}

	/**
	 * Sets up the reference link in the Referrals table
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'edd' != $referral->context ) {

			return $reference;

		}

		$url = admin_url( 'edit.php?post_type=download&page=edd-payment-history&view=view-order-details&id=' . $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}

	/**
	 * Retrieves the referral description
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function get_referral_description( $payment_id = 0 ) {

		$description = '';
		$downloads   = edd_get_payment_meta_downloads( $payment_id );
		foreach( $downloads as $key => $item ) {
			$description .= get_the_title( $item['id'] );
			if( $key + 1 < count( $downloads ) ) {
				$description .= ', ';
			}
		}

		return $description;

	}

	/**
	 * Shows the affiliate drop down on the discount edit / add screens
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function discount_edit( $discount_id = 0 ) {

		add_filter( 'affwp_is_admin_page', '__return_true' );
		affwp_admin_scripts();

		$affiliate_id = get_post_meta( $discount_id, 'affwp_discount_affiliate', true );
		$user_id      = affwp_get_affiliate_user_id( $affiliate_id );
		$user         = get_userdata( $user_id );
		$user_name    = $user ? $user->user_login : '';
?>
		<table class="form-table">
			<tbody>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="user_name"><?php _e( 'Affiliate Discount?', 'affiliate-wp' ); ?></label>
					</th>
					<td>
						<span class="affwp-ajax-search-wrap">
							<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $user_id ); ?>" />
							<input type="text" name="user_name" id="user_name" value="<?php echo esc_attr( $user_name ); ?>" class="affwp-user-search" autocomplete="off" style="width: 300px;" />
							<img class="affwp-ajax waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" style="display: none;"/>
						</span>
						<div id="affwp_user_search_results"></div>
						<p class="description"><?php _e( 'If you would like to connect this discount to an affiliate, enter the name of the affiliate it belongs to.', 'edd' ); ?></p>
					</td>
				</tr>
			</tbody>
		</table>
<?php
	}

	/**
	 * Stores the affiliate ID in the discounts meta if it is an affiliate's discount
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function store_discount_affiliate( $details, $discount_id = 0 ) {

		if( empty( $_POST['user_id'] ) && empty( $_POST['user_name'] ) ) {
			return;
		}

		if( empty( $_POST['user_id'] ) ) {
			$user = get_user_by( 'login', $_POST['user_name'] );
			if( $user ) {
				$user_id = $user->ID;
			}
		} else {
			$user_id = absint( $_POST['user_id'] );
		}

		$affiliate_id = affwp_get_affiliate_id( $user_id );

		update_post_meta( $discount_id, 'affwp_discount_affiliate', $affiliate_id );
	}

}
new Affiliate_WP_EDD;