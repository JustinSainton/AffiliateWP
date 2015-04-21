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

			if( $discount_aff && affiliate_wp()->tracking->is_valid_affiliate( $affiliate_id ) ) {

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

			if ( $this->is_affiliate_email( $user->user_email ) ) {
				return; // Customers cannot refer themselves
			}

			$key   = rcp_get_subscription_key( $user_id );
			$total = $this->calculate_referral_amount( $price, $key );

			$this->insert_pending_referral( $total, $key, rcp_get_subscription( $user_id ) );
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

		add_filter( 'affwp_is_admin_page', '__return_true' );
		affwp_admin_scripts();

		$affiliate_id = $wpdb->get_var( $wpdb->prepare( "SELECT meta_value FROM $wpdb->usermeta WHERE meta_key = %s", 'affwp_discount_rcp_' . $discount_id ) );
		$user_id      = affwp_get_affiliate_user_id( $affiliate_id );
		$user         = get_userdata( $user_id );
		$user_name    = $user ? $user->user_login : '';

?>
		<table class="form-table">
			<tbody>
				<tr class="form-field">
					<th scope="row" valign="top">
						<label for="affiliate_id"><?php _e( 'Affiliate Discount?', 'affiliate-wp' ); ?></label>
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
	public function store_discount_affiliate( $args, $discount_id = 0 ) {

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

		update_user_meta( $user_id, 'affwp_discount_rcp_' . $discount_id, $affiliate_id );
	}

	/**
	 * Updates the affiliate ID in the discounts meta if it is an affiliate's discount
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function update_discount_affiliate( $discount_id = 0, $args ) {

		if( empty( $_POST['user_id'] ) ) {
			$user = get_user_by( 'login', $_POST['user_name'] );
			if( $user ) {
				$user_id = $user->ID;
			}
		} else {
			$user_id = absint( $_POST['user_id'] );
		}

		if ( empty( $_POST['user_name'] ) ) {
			delete_user_meta( $user_id, 'affwp_discount_rcp_' . $discount_id );
			return;
		}

		if( empty( $_POST['user_id'] ) && empty( $_POST['user_name'] ) ) {
			return;
		}

		$affiliate_id = affwp_get_affiliate_id( $user_id );

		update_user_meta( $user_id, 'affwp_discount_rcp_' . $discount_id, $affiliate_id );
		
	}
	
}
new Affiliate_WP_RCP;
