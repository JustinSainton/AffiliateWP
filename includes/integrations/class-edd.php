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
		add_action( 'edd_complete_purchase', array( $this, 'track_discount_referral' ), 9 );
		add_action( 'edd_complete_purchase', array( $this, 'mark_referral_complete' ) );
		add_action( 'edd_update_payment_status', array( $this, 'revoke_referral_on_refund' ), 10, 3 );
		add_action( 'edd_payment_delete', array( $this, 'revoke_referral_on_delete' ), 10 );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

		// Discount code tracking actions and filters
		add_action( 'edd_add_discount_form_bottom', array( $this, 'discount_edit' ) );
		add_action( 'edd_edit_discount_form_bottom', array( $this, 'discount_edit' ) );
		add_action( 'edd_post_update_discount', array( $this, 'store_discount_affiliate' ), 10, 2 );
		add_action( 'edd_post_insert_discount', array( $this, 'store_discount_affiliate' ), 10, 2 );

		// Integration with EDD commissions to adjust commission rates if a referral is present
		add_filter( 'eddc_calc_commission_amount', array( $this, 'commission_rate' ), 10, 2 );
		add_filter( 'affwp_settings_integrations', array( $this, 'commission_settings' ), 10 );

		// Per product referral rates
		add_action( 'edd_meta_box_settings_fields', array( $this, 'download_settings' ), 100 );
		add_filter( 'edd_metabox_fields_save', array( $this, 'download_save_fields' ) );

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

			$downloads = edd_get_payment_meta_cart_details( $payment_id );
			if( is_array( $downloads ) ) {
				
				// Calculate the referral amount based on product prices
				$referral_total = 0.00;
				foreach( $downloads as $download ) {

					$referral_total += $this->calculate_referral_amount( $download['price'], $payment_id, $download['id'] );

				}

			} else {

				$referral_total = $this->calculate_referral_amount( $payment_data['price'], $payment_id );

			}

			$referral_id = $this->insert_pending_referral( $referral_total, $payment_id, $this->get_referral_description( $payment_id ) );

			//only continue if the insert was a success
			if ( false !== $referral_id ) {


				$amount = affwp_currency_filter( affwp_format_amount( $referral_total ) );
				$name   = affiliate_wp()->affiliates->get_affiliate_name( $this->affiliate_id );

				edd_insert_payment_note( $payment_id, sprintf( __( 'Referral #%d for %s recorded for %s', 'affiliate-wp' ), $referral_id, $amount, $name ) );
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

				$this->affiliate_id = $affiliate_id;

				$existing = affiliate_wp()->referrals->get_by( 'reference', $payment_id, $this->context );

				$downloads = edd_get_payment_meta_cart_details( $payment_id );
				
				if ( is_array( $downloads ) ) {
					
					// Calculate the referral amount based on product prices
					$referral_total = 0.00;
					foreach ( $downloads as $download ) {

						$referral_total += $this->calculate_referral_amount( $download['price'], $payment_id, $download['id'] );

					}

				} else {

					$referral_total = $this->calculate_referral_amount( edd_get_payment_subtotal( $payment_id ), $payment_id );

				}


				if ( ! empty( $existing->referral_id ) ) {

					// If a referral was already recorded, overwrite it with the affiliate from the coupon
					affiliate_wp()->referrals->update( $existing->referral_id, array( 'affiliate_id' => $this->affiliate_id, 'status' => 'unpaid', 'amount' => $referral_total ) );

				} else {

					if( 0 == $referral_total && affiliate_wp()->settings->get( 'ignore_zero_referrals' ) ) {
						return false; // Ignore a zero amount referral
					}

					$referral_id = affiliate_wp()->referrals->add( array(
						'amount'       => $referral_total,
						'reference'    => $payment_id,
						'description'  => $this->get_referral_description( $payment_id ),
						'affiliate_id' => $this->affiliate_id,
						'context'      => $this->context
					) );

					$referral_total = affwp_currency_filter( affwp_format_amount( $referral_total ) );
					$name           = affiliate_wp()->affiliates->get_affiliate_name( $affiliate_id );

					edd_insert_payment_note( $payment_id, sprintf( __( 'Referral #%d for %s recorded for %s', 'affiliate-wp' ), $referral_id, $referral_total, $name ) );

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

	/**
	 * Adjust the commission rate recorded if a referral is present
	 *
	 * @access  public
	 * @since   1.2
	*/
	public function commission_rate( $amount, $args ) {

		if( ! affiliate_wp()->settings->get( 'edd_adjust_commissions' ) ) {
			return $amount;
		}

		$referral_amount = affiliate_wp()->referrals->get_column_by( 'amount', 'reference', $args['payment_id']  );

		if( ! $referral_amount ) {
			return $amount;
		}

		if( 'flat' == $args['type'] ) {
			return $args['rate'] - $referral_amount;
		}

		$args['price'] -= $referral_amount;

		if ( $args['rate'] >= 1 ) {
			$amount = $args['price'] * ( $args['rate'] / 100 ); // rate format = 10 for 10%
		} else {
			$amount = $args['price'] * $args['rate']; // rate format set as 0.10 for 10%
		}


		return $amount;
	}

	/**
	 * Add a setting to toggle whether referrals adjust EDD commissions
	 *
	 * @access  public
	 * @since   1.2
	*/
	public function commission_settings( $settings ) {

		if( function_exists( 'eddc_record_commission' ) ) {

			$settings[ 'edd_adjust_commissions' ] = array(
				'name' => __( 'Adjust EDD Commissions', 'affiliate-wp' ),
				'desc' => __( 'Should AffiliateWP adjust the commission amounts recorded for purchases that include affiliate referrals? This will subtract the referral amount from the base amount used to calculate the commission total.', 'affiliate-wp' ),
				'type' => 'checkbox'
			);

		}
		
		return $settings;
	}

	/**
	 * Adds per-product referral rate settings input fields
	 *
	 * @access  public
	 * @since   1.2
	*/
	public function download_settings( $download_id = 0 ) {

		$rate = get_post_meta( $download_id, '_affwp_' . $this->context . '_product_rate', true );
?>
		<p>
			<strong><?php _e( 'Affiliate Rates:', 'affiliate-wp' ); ?></strong>
		</p>

		<p>
			<label for="affwp_product_rate">
				<input type="text" name="_affwp_edd_product_rate" id="affwp_product_rate" class="small-text" value="<?php echo esc_attr( $rate ); ?>" />
				<?php _e( 'Referral Rate', 'affiliate-wp' ); ?>
			</label>
		</p>

		<p><?php _e( 'These settings will be used to calculate affiliate earnings per-sale. Leave blank to use default affiliate rates.', 'affiliate-wp' ); ?></p>
<?php
	}

	/**
	 * Tells EDD to save our product settings
	 *
	 * @access  public
	 * @since   1.2
	 * @return  array
	*/
	public function download_save_fields( $fields = array() ) {
		$fields[] = '_affwp_edd_product_rate';
		return $fields;
	}

}
new Affiliate_WP_EDD;
