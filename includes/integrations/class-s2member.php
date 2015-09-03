<?php

class Affiliate_WP_S2Member extends Affiliate_WP_Base {


	public function init() {

		$this->context = 's2member';

		add_action( 'plugins_loaded', array( $this, 's2member_notify_url' ) );

		add_action( 'init', array( $this, 'mark_referral_complete' ) );

		add_action( 'init', array( $this, 'revoke_referral_on_refund' ) );

		add_action('ws_plugin__s2member_before_sc_paypal_button_after_shortcode_atts', array( $this, 's2member_set_referral_variable' ) );
		add_action('ws_plugin__s2member_pro_before_sc_stripe_form_after_shortcode_atts', array( $this, 's2member_set_referral_variable' ) );
		add_action('ws_plugin__s2member_pro_before_sc_authnet_form_after_shortcode_atts', array( $this, 's2member_set_referral_variable' ) );
		add_action('ws_plugin__s2member_pro_before_sc_paypal_form_after_shortcode_atts', array( $this, 's2member_set_referral_variable' ) );
	}

	/**
	 * Create a payment notification url & refund notification url for AffiliateWP
	 *
	 * @access  public
	 * @since
	*/
	public function s2member_notify_url(){

		$s2_options = &$GLOBALS['WS_PLUGIN__']['s2member']['o'];

		$s2_payment_notification_urls = &$s2_options['payment_notification_urls'];

		$auth_key = defined( 'AUTH_KEY' ) ? AUTH_KEY : '';
		$secret   = hash( 'md5', $auth_key );

		$affwp_payment_notify_url = home_url( '/?s2member_affiliatewp_notify=payment&amount=%%amount%%&txn_id=%%txn_id%%&item_name=%%item_name%%&&ip=%%user_ip%%&user_id=%%user_id%%&item_number=%%item_number%%&payer_email=%%payer_email%%&affiliate_id=%%cv1%%&secret='.$secret );

		// Add AffiliateWP payment notification URL to the list dynamically.
		if( stripos( $s2_payment_notification_urls, $affwp_payment_notify_url ) === FALSE ){

			$s2_payment_notification_urls .= "\n" . $affwp_payment_notify_url;

		}

  		$s2_ref_rev_notification_urls = &$s2_options['ref_rev_notification_urls'];

	  	$affwp_refund_notify_url = home_url( '/?s2member_affiliatewp_notify=refund&txn_id=%%parent_txn_id%%&secret='.$secret );

		// Add AffiliateWP refund notification URL notification URL to the list dynamically.
		if( stripos( $s2_ref_rev_notification_urls, $affwp_refund_notify_url ) === FALSE ){

			$s2_ref_rev_notification_urls .= "\n". $affwp_refund_notify_url;

		}

	}

	/**
	 * Mark a referral as complete when an payment is received
	 *
	 * @access  public
	 * @since
	*/
	public function mark_referral_complete() {

	    if( ! empty( $_REQUEST['s2member_affiliatewp_notify'] ) && $_REQUEST['s2member_affiliatewp_notify'] === 'payment' ) {

			$auth_key 	= defined( 'AUTH_KEY' ) ? AUTH_KEY : '';

			if( ! ( ! empty( $_REQUEST['secret'] ) && md5( $auth_key ) == $_REQUEST['secret'] ) ){
				return;
			}

            if( ! empty( $_REQUEST['user_id'] ) && ! empty( $_REQUEST['amount'] ) && ! empty( $_REQUEST['affiliate_id'] ) ){

            	$affiliate_id 	= (int) $_REQUEST['affiliate_id'];
            	$user_id 		= (int) $_REQUEST['user_id'];
            	$amount 		= $_REQUEST['amount'];
            	$txn_id 		= $_REQUEST['txn_id'];
            	$item_name 		= $_REQUEST['item_name'];
            	$user_ip 		= $_REQUEST['ip'];
            	$item_number 	= $_REQUEST['item_number'];
            	$payer_email 	= $_REQUEST['payer_email'];

            	$args =  array(
            		'user_id' 		=> $user_id,
            		'amount'		=> $amount,
            		'txn_id'		=> $txn_id,
            		'desc'			=> $item_name,
            		'affiliate_id'	=> $affiliate_id
            	);

				$this->add_pending_referral( $args );

				$this->complete_referral( $txn_id );
            }

            exit;
	    }

	}

	/**
	 * Revoke a referral on refund or cancellation
	 *
	 * @access  public
	 * @since
	*/
	public function revoke_referral_on_refund() {

	    if( ! empty( $_REQUEST['s2member_affiliatewp_notify'] ) && $_REQUEST['s2member_affiliatewp_notify'] === 'refund' ) {

			$auth_key 	= defined( 'AUTH_KEY' ) ? AUTH_KEY : '';

			if( ! ( ! empty( $_REQUEST['secret'] ) && md5( $auth_key ) == $_REQUEST['secret'] ) ){
				return;
			}

	    	if( ! empty( $_REQUEST['txn_id'] ) ){

				if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
					return;
				}

				$this->reject_referral( $_REQUEST['txn_id'] );

	    	}

	    	exit;

	    }

	}

	/**
	 * Record a pending referral
	 *
	 * @access  public
	 * @since
	*/
	public function add_pending_referral( $args ){

		if ( affiliate_wp()->tracking->is_valid_affiliate( $args['affiliate_id'] ) ) {

			$user           = get_userdata( $args['user_id'] );
			$customer_email = $user->user_email;

			if ( $this->is_affiliate_email( $customer_email ) ) {

				return; // Customers cannot refer themselves

			}

		    $amount      = $args['amount'];
			$order_id    = $args['txn_id'];
		    $description = $args['desc'];

			if( affiliate_wp()->settings->get( 'exclude_tax' ) ) {

			}

			$this->affiliate_id = $args['affiliate_id'];

			$referral_total = $this->calculate_referral_amount( $amount, $order_id );

			$this->insert_pending_referral( $referral_total, $order_id, $description );
		}
	}


	/**
	 * Add the Affiliate ID if set to the request that is sent to the gateway
	 *
	 * @access  public
	 * @since
	*/
	public function s2member_set_referral_variable( $vars ){

		if( affiliate_wp()->tracking->get_affiliate_id() ){

	        $affiliate_id 	= affiliate_wp()->tracking->get_affiliate_id();
	        $vars["__refs"]["attr"]["custom"] .= "|" . $affiliate_id;

		}
    }

}
new Affiliate_WP_S2Member;