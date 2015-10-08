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
	}

	public function add_pending_referral( $order ) {

		// Check if an affiliate coupon was used
		$affiliate_id = $this->get_coupon_affiliate_id( $order->discount_code );

		if( $this->was_referred() || $affiliate_id ) {

			if( false !== $affiliate_id ) {
				$this->affiliate_id = $affiliate_id;
			}

			$user = get_userdata( $order->user_id );

			if ( $this->is_affiliate_email( $user->user_email ) ) {
				return; // Customers cannot refer themselves
			}

			$referral_total = $this->calculate_referral_amount( $order->subtotal, $order->id );

			$referral_id = $this->insert_pending_referral( $referral_total, $order->id, $order->membership_name );

			if( 'success' === strtolower( $order->status ) ) {

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

		if( isset( $_REQUEST['saveid'] ) ) {
			$user_name = sanitize_text_field( $_POST['user_name'] );

			if( empty( $_POST['user_id'] ) ) {
				$user = get_user_by( 'login', $_POST['user_name'] );

				if( $user ) {
					$user_id = $user->ID;
				}
			} else {
				$user_id = absint( $_POST['user_id'] );
			}

			$affiliate_id = affwp_get_affiliate_id( $user_id );

			$db_code = $wpdb->get_row("SELECT *, UNIX_TIMESTAMP(starts) as starts, UNIX_TIMESTAMP(expires) as expires FROM $wpdb->pmpro_discount_codes WHERE ID ='" . esc_sql($edit) . "' LIMIT 1");

			affwp_update_affiliate_meta( $affiliate_id, 'affwp_discount_pmp_' . $edit, $db_code->code );
		}

		add_filter( 'affwp_is_admin_page', '__return_true' );
		affwp_admin_scripts();

		if($edit > 0) {
			$table = $wpdb->prefix . 'affiliate_wp_affiliatemeta';
			$affiliate_id = $wpdb->get_var( $wpdb->prepare( "SELECT affiliate_id FROM $table WHERE meta_key = %s", 'affwp_discount_pmp_' . $edit ) );
		} else {
			$affiliate_id = false;
		}

		$user_id      = affwp_get_affiliate_user_id( $affiliate_id );
		$user         = get_userdata( $user_id );
		$user_name    = $user ? $user->user_login : '';
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
					</td>
				</tr>
			</tbody>
		</table>
		<?php
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

		if( pmpro_checkDiscountCode( $coupon_code ) ) {
			$table = $wpdb->prefix . 'affiliate_wp_affiliatemeta';
			$db_code = $wpdb->get_row("SELECT *, UNIX_TIMESTAMP(starts) as starts, UNIX_TIMESTAMP(expires) as expires FROM $wpdb->pmpro_discount_codes WHERE code ='" . esc_sql($coupon_code) . "' LIMIT 1");
			$affiliate_id = $wpdb->get_var( $wpdb->prepare( "SELECT affiliate_id FROM $table WHERE meta_key = %s", 'affwp_discount_pmp_' . $edit ) );
		}

		return $affiliate_id;
	}
}
new Affiliate_WP_PMP;
