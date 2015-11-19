<?php

class Affiliate_WP_PMP extends Affiliate_WP_Base {

	public function init() {

		$this->context = 'pmp';

		add_action( 'pmpro_added_order', array( $this, 'add_pending_referral' ), 10 );
		add_action( 'pmpro_updated_order', array( $this, 'mark_referral_complete' ), 10 );
		add_action( 'admin_init', array( $this, 'revoke_referral_on_refund_and_cancel' ), 10);
		add_action( 'pmpro_delete_order', array( $this, 'revoke_referral_on_delete' ), 10, 2 );
		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

		// Coupon support
		add_action( 'pmpro_discount_code_after_settings', array( $this, 'coupon_option' ) );
		add_action( 'pmpro_save_discount_code', array( $this, 'save_affiliate_coupon' ) );
	}

	public function add_pending_referral( $order ) {

		// Check if an affiliate coupon was used
		$coupon_affiliate_id = $this->get_coupon_affiliate_id( $order->discount_code );

		if ( $this->was_referred() || $coupon_affiliate_id ) {

			// get affiliate ID
			$affiliate_id = $this->get_affiliate_id( $order->id );

			if ( false !== $coupon_affiliate_id ) {
				$affiliate_id = $coupon_affiliate_id;
			}

			$user = get_userdata( $order->user_id );

			if ( $user instanceof WP_User && $this->is_affiliate_email( $user->user_email, $affiliate_id ) ) {
				return; // Customers cannot refer themselves
			}

			$referral_total = $this->calculate_referral_amount( $order->subtotal, $order->id, '', $affiliate_id );

			$referral_id = $this->insert_pending_referral( $referral_total, $order->id, $order->membership_name, '', array( 'affiliate_id' => $affiliate_id ) );

			if ( 'success' === strtolower( $order->status ) ) {

				if( $referral_id ) {
					affiliate_wp()->referrals->update( $referral_id, array( 'custom' => $order->id ), '', 'referral' );
				}

				$this->complete_referral( $order->id );

			}
		}

	}

	public function mark_referral_complete( $order ) {

		if( 'success' !== strtolower( $order->status ) ) {
			return;
		}

		$this->complete_referral( $order->id );
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

	/**
	 * Shows the affiliate drop down on the discount edit / add screens
	 *
	 * @access  public
	 * @since   1.7.5
	 */
	public function coupon_option( $edit ) {

		global $wpdb;

		add_filter( 'affwp_is_admin_page', '__return_true' );
		affwp_admin_scripts();

		$user_id   = 0;
		$user_name = '';

		if( $edit > 0 ) {
			$table = $wpdb->prefix . 'affiliate_wp_affiliatemeta';
			$affiliate_id = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE meta_key = %s", 'affwp_discount_pmp_' . $edit ) );
		} else {
			$affiliate_id = false;
		}
		if( $affiliate_id ) {
			$user_id      = affwp_get_affiliate_user_id( $affiliate_id );
			$user         = get_userdata( $user_id );
			$user_name    = $user ? $user->user_login : '';
		}
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th scope="row" valign="top"><label for="user_name"><?php _e( 'Affiliate Discount?', 'affiliate-wp' ); ?></label></th>
					<td class="form-field affwp-pmp-coupon-field">
						<span class="affwp-ajax-search-wrap">
							<span class="affwp-pmp-coupon-input-wrap">
								<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $user_id ); ?>" />
								<input type="text" name="user_name" id="user_name" value="<?php echo esc_attr( $user_name ); ?>" class="affwp-user-search" data-affwp-status="active" autocomplete="off" style="width:150px" />
								<img class="affwp-ajax waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" style="display: none;"/>
							</span>
							<span id="affwp_user_search_results"></span>
							<small class="pmpro_lite"><?php _e( 'If you would like to connect this discount to an affiliate, enter the name of the affiliate it belongs to.', 'affiliate-wp' ); ?></small>
						</span>
						<?php wp_nonce_field( 'affwp_pmp_coupon_nonce', 'affwp_pmp_coupon_nonce' ); ?>
					</td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Saves an affiliate coupon
	 *
	 * @access  public
	 * @since   1.7.5
	 */
	public function save_affiliate_coupon( $save_id = 0 ) {

		global $wpdb;

		if( empty( $_REQUEST['affwp_pmp_coupon_nonce'] ) ) {
			return;
		}

		if( ! wp_verify_nonce( $_REQUEST['affwp_pmp_coupon_nonce'], 'affwp_pmp_coupon_nonce' ) ) {
			return;
		}

		$user_name = sanitize_text_field( $_POST['user_name'] );

		if( empty( $_POST['user_id'] ) ) {
			$user = get_user_by( 'login', $_POST['user_name'] );

			if( $user ) {
				$user_id = $user->ID;
			}
		} else {
			$user_id = absint( $_POST['user_id'] );
		}

		$coupon       = $wpdb->get_row( "SELECT * FROM $wpdb->pmpro_discount_codes WHERE code = '" . esc_sql( $_REQUEST['code'] ) . "' LIMIT 1" );
		$affiliate_id = affwp_get_affiliate_id( $user_id );

		if( empty( $_POST['user_name'] ) ) {
			affwp_delete_affiliate_meta( $affiliate_id, 'affwp_discount_pmp_' . $coupon->id );
			return;
		}


		affwp_update_affiliate_meta( $affiliate_id, 'affwp_discount_pmp_' . $coupon->id, $coupon->code );

	}

	/**
	 * Get the affiliate associated with a coupon
	 *
	 * @access  public
	 * @since   1.7.5
	 */
	public function get_coupon_affiliate_id( $coupon_code ) {
		global $wpdb;

		$affiliate_id = false;

		if( ! empty( $coupon_code ) && pmpro_checkDiscountCode( $coupon_code ) ) {
			$table        = $wpdb->prefix . 'affiliate_wp_affiliatemeta';
			$affiliate_id = $wpdb->get_var( $wpdb->prepare( "SELECT affiliate_id FROM $table WHERE meta_value = %s", $coupon_code ) );
		}

		return $affiliate_id;
	}
}
new Affiliate_WP_PMP;
