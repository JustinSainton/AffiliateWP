<?php

class Affiliate_WP_Jigoshop extends Affiliate_WP_Base {
	
	public function init() {

		$this->context = 'jigoshop';

		// Filters the integration settings.
		add_filter( 'affwp_settings_integrations', array( $this, 'filter_affwp_integration_settings' ), 10, 1 );

		add_action( 'jigoshop_new_order', array( $this, 'add_pending_referral' ), 10, 1 );

		// Mark referral complete when action is called.
		$referral_complete = get_option('affwp_jigoshop_referral_complete');
		if( !empty( $referral_complete ) ) {
			add_action( 'jigoshop_order_status_' . $referral_complete, array( $this, 'mark_referral_complete' ), 10 );
		}

		/**
		 * Revoke referral when the order status has changed from the following only:
		 *
		 * completed to refunded
		 * on-hold to refunded
		 * processing to refunded
		 * processing to cancelled
		 * completed to canelled
		 */
		$revoke_referral = get_option('affwp_jigoshop_revoke_referral');
		if( !empty( $revoke_referral ) ) {
			add_action( 'jigoshop_order_status_' . $revoke_referral, array( $this, 'revoke_referral_on_refund' ), 10 );
		}

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

	}

	// Applies additional integration settings for Jigoshop.
	public function filter_affwp_integration_settings( $integrations ) {
		$settings = array(
							'affwp_jigoshop_referral_complete' => array(
															'name' => __( 'Referrals Complete', 'affiliate-wp' ),
															'desc' => __( 'Select the order status you want the referrals to be marked complete.' ),
															'type' => 'select',
															'options' => array(
																			'completed' => __( 'Completed', 'affiliate-wp' ),
																			'processing' => __( 'Processing', 'affiliate-wp' ),
															),
							),
							'affwp_jigoshop_revoke_referral' => array(
														'name' => __( 'Revoke Referrals', 'affiliate-wp' ),
														'desc' => __( 'Select the old and new order status you want the referrals to be revoked when changed.' ),
														'type' => 'select',
														'options' => array(
																		'completed_to_refunded' => __( 'Completed to Refunded', 'affiliate-wp' ),
																		'on-hold_to_refunded' => __( 'On-Hold to Refunded', 'affiliate-wp' ),
																		'processing_to_refunded' => __( 'Processing to Refunded', 'affiliate-wp' ),
																		'processing_to_cancelled' => __( 'Processing to Cancelled', 'affiliate-wp' ),
																		'completed_to_cancelled' => __( 'Completed to Cancelled', 'affiliate-wp' ),
														),
							),
		);

		$settings = array_merge( $integrations, $settings );

		return $settings;
	}

	public function add_pending_referral( $order_id = 0 ) {

		if( $this->was_referred() ) {

			$order = new jigoshop_order( $order_id );

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

		if( empty( $referral->context ) || 'jigoshop' != $referral->context ) {

			return $reference;

		}

		$url = get_edit_post_link( $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}
	
}
new Affiliate_WP_Jigoshop;

?>