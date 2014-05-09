<?php

class Affiliate_WP_WooCommerce extends Affiliate_WP_Base {
	
	public function init() {

		$this->context = 'woocommerce';

		add_action( 'woocommerce_checkout_order_processed', array( $this, 'add_pending_referral' ), 10, 2 );
		
		// There should be an option to choose which of these is used
		add_action( 'woocommerce_order_status_completed', array( $this, 'mark_referral_complete' ), 10 );
		add_action( 'woocommerce_order_status_processing', array( $this, 'mark_referral_complete' ), 10 );
		add_action( 'woocommerce_order_status_completed_to_refunded', array( $this, 'revoke_referral_on_refund' ), 10 );
		add_action( 'woocommerce_order_status_on-hold_to_refunded', array( $this, 'revoke_referral_on_refund' ), 10 );
		add_action( 'woocommerce_order_status_processing_to_refunded', array( $this, 'revoke_referral_on_refund' ), 10 );
		add_action( 'woocommerce_order_status_processing_to_cancelled', array( $this, 'revoke_referral_on_refund' ), 10 );
		add_action( 'woocommerce_order_status_completed_to_cancelled', array( $this, 'revoke_referral_on_refund' ), 10 );
	
		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

		//add_filter( 'woocommerce_coupon_data_tabs', array( $this, 'coupon_tab' ) );
		add_action( 'woocommerce_coupon_options', array( $this, 'coupon_option' ) );
		add_action( 'woocommerce_coupon_options_save', array( $this, 'store_discount_affiliate' ) );

	}

	public function add_pending_referral( $order_id = 0, $posted ) {

		if( $this->was_referred() ) {

			$order       = new WC_Order( $order_id );

			if( $this->get_affiliate_email() == $order->billing_email ) {
				return; // Customers cannot refer themselves
			}

			$description = ''; 
			$items       = $order->get_items();
			foreach( $items as $key => $item ) {
				$description .= $item['name'];
				if( $key + 1 < count( $items ) ) {
					$description .= ', ';
				}
			}

			$this->insert_pending_referral( $order->get_total(), $order_id, $description );
		
			$referral = affiliate_wp()->referrals->get_by( 'reference', $order_id, $this->context );
			$amount   = affwp_currency_filter( affwp_format_amount( $referral->amount ) );
			$name     = affiliate_wp()->affiliates->get_affiliate_name( $referral->affiliate_id );

			$order->add_order_note( sprintf( __( 'Referral #%d for %s recorded for %s', 'affiliate-wp' ),$referral->referral_id, $amount, $name ) );

		}

	}

	public function mark_referral_complete( $order_id = 0 ) {

		$this->complete_referral( $order_id );

	}

	public function revoke_referral_on_refund( $order_id = 0 ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		$this->reject_referral( $order_id );

	}

	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'woocommerce' != $referral->context ) {

			return $reference;

		}

		$url = get_edit_post_link( $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}

	/**
	 * Register a custom tab in the Coupon metabox
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function coupon_tab( $tabs ) {

		// This can't be used until WooCommerce adds an action to the bottom of coupon_options div

		$tabs['affiliatewp'] = array(
			'label'  => __( 'Affiliate Tracking', 'affiliate-wp' ),
			'target' => 'affiliate_wp_tracking',
			'class'  => '',
		);

		return $tabs;
	}

	/**
	 * Shows the affiliate drop down on the discount edit / add screens
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function coupon_option() {

		global $post;

		add_filter( 'affwp_is_admin_page', '__return_true' );
		affwp_admin_scripts();

		$affiliate_id = get_post_meta( $post->ID, 'affwp_discount_affiliate', true );
		$user_id      = affwp_get_affiliate_user_id( $affiliate_id );
		$user         = get_userdata( $user_id );
		$user_name    = $user ? $user->user_login : '';
?>
		<p class="form-field affwp-woo-coupon-field">
			<label for="user_name"><?php _e( 'Affiliate Discount?', 'affiliate-wp' ); ?></label>
			<span class="affwp-ajax-search-wrap">
				<input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr( $user_id ); ?>" />
				<input type="text" name="user_name" id="user_name" value="<?php echo esc_attr( $user_name ); ?>" class="affwp-user-search" autocomplete="off" style="width: 300px;" />
				<img class="affwp-ajax waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" style="display: none;"/>
				<span id="affwp_user_search_results"></span>
				<img class="help_tip" data-tip='<?php _e( 'If you would like to connect this discount to an affiliate, enter the name of the affiliate it belongs to.', 'affiliate-wp' ); ?>' src="<?php echo WC()->plugin_url(); ?>/assets/images/help.png" height="16" width="16" />
			</span>
		</p>
<?php
	}

	/**
	 * Stores the affiliate ID in the discounts meta if it is an affiliate's discount
	 *
	 * @access  public
	 * @since   1.1
	*/
	public function store_discount_affiliate( $coupon_id = 0 ) {

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

		update_post_meta( $coupon_id, 'affwp_discount_affiliate', $affiliate_id );
	}
	
}
new Affiliate_WP_WooCommerce;