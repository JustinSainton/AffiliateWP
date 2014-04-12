<?php

class Affiliate_WP_RCP extends Affiliate_WP_Base {
	
	/**
	 * Gets things started
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function init() {

		$this->context = 'rcp';

		add_action( 'rcp_form_processing', array( $this, 'add_pending_referral' ), 10, 3 );
		add_action( 'rcp_insert_payment', array( $this, 'mark_referral_complete' ), 10, 3 );
		add_action( 'rcp_delete_payment', array( $this, 'revoke_referral_on_delete' ), 10 );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

		// Discount code tracking actions and filters
		add_action( 'rcp_add_discount_form', array( $this, 'discount_edit' ) );
		add_action( 'rcp_edit_discount_form', array( $this, 'discount_edit' ) );
		add_action( 'rcp_add_discount', array( $this, 'store_discount_affiliate' ), 10, 2 );
		add_action( 'rcp_edit_discount', array( $this, 'update_discount_affiliate' ), 10, 2 );
		add_action( 'rcp_insert_payment', array( $this, 'track_discount_referral' ), 10, 3 );

	}

	/**
	 * Creates the pending referral during signup
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function add_pending_referral( $post_data, $user_id, $price ) {

		$affiliate_discount = false;

		if( ! empty( $_POST['rcp_discount'] ) ) {

			global $wpdb;

			$rcp_discounts = new RCP_Discounts;
			$discount_obj  = $rcp_discounts->get_by( 'code', $_POST['rcp_discount'] );
			$affiliate_id  = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = %s", 'affwp_discount_rcp_' . $discount_obj->id ) );
			$user_id       = affwp_get_affiliate_user_id( $affiliate_id );
			$discount_aff  = get_user_meta( $user_id, 'affwp_discount_rcp_' . $discount_obj->id, true );

			if( $discount_aff ) {

				$affiliate_discount = true;

				$amount = affwp_calc_referral_amount( $price, $affiliate_id );
				
				if( 0 == $amount && affiliate_wp()->settings->get( 'ignore_zero_referrals' ) ) {
					return false; // Ignore a zero amount referral
				}

				$referral_id = affiliate_wp()->referrals->add( array(
					'amount'       => $amount,
					'reference'    => rcp_get_subscription_key( $user_id ),
					'description'  => rcp_get_subscription( $user_id ),
					'affiliate_id' => $affiliate_id,
					'context'      => $this->context
				) );

			}

		}

		if( $this->was_referred() && ! $affiliate_discount ) {

			$user = get_userdata( $user_id );

			if( $this->get_affiliate_email() == $user->user_email ) {
				return; // Customers cannot refer themselves
			}

			$key = get_user_meta( $user_id, 'rcp_subscription_key', true );

			$this->insert_pending_referral( $price, $key, rcp_get_subscription( $user_id ) );
		}

	}

	/**
	 * Sets a referral to complet when a payment is inserted
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function mark_referral_complete( $payment_id, $args, $amount ) {

		$this->complete_referral( $args['subscription_key'] );
	
	}

	/**
	 * Revokes a referral when the payment is deleted
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function revoke_referral_on_delete( $payment_id = 0 ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		$payments = new RCP_Payments;
		$payment  = $payments->get_payment( $payment_id );
		$this->reject_referral( $payment->subscription_key );

	}

	/**
	 * Builds the reference link for the referrals table
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'rcp' != $referral->context ) {

			return $reference;

		}

		$url = admin_url( 'admin.php?page=rcp-payments&s=' . $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}

	/**
	 * Shows the affiliate drop down on the discount edit / add screens
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function discount_edit( $discount_id = 0 ) {

		global $wpdb;

		$affiliate_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = %s", 'affwp_discount_rcp_' . $discount_id ) );

		// TODO replace this with a select2 drop down
?>
		<table class="form-table">
			<tbody>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="affiliate_id"><?php _e( 'Affiliate Discount?', 'affiliate-wp' ); ?></label>
					</th>
					<td>
						<input type="text" id="affiliate_id" name="affiliate_id" value="<?php echo esc_attr( $affiliate_id ); ?>" style="width: 300px;"/>
						<p class="description"><?php _e( 'If you would like to connect this discount to an affiliate, select the affiliate it belongs to.', 'edd' ); ?></p>
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
	public function store_discount_affiliate( $args, $discount_id = 0 ) {

		if( empty( $_POST['affiliate_id'] ) ) {
			return;
		}

		$affiliate_id = sanitize_text_field( $_POST['affiliate_id'] );
		$user_id      = affwp_get_affiliate_user_id( $affiliate_id );

		update_user_meta( $user_id, 'affwp_discount_rcp_' . $discount_id, $affiliate_id );
	}

	/**
	 * Updates the affiliate ID in the discounts meta if it is an affiliate's discount
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function update_discount_affiliate( $discount_id = 0, $args ) {

		if( empty( $_POST['affiliate_id'] ) ) {
			return;
		}

		$affiliate_id = sanitize_text_field( $_POST['affiliate_id'] );
		$user_id      = affwp_get_affiliate_user_id( $affiliate_id );

		update_user_meta( $user_id, 'affwp_discount_rcp_' . $discount_id, $affiliate_id );
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
	
}
new Affiliate_WP_RCP;