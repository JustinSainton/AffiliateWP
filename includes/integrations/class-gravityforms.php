<?php

class Affiliate_WP_Gravity_Forms extends Affiliate_WP_Base {

	public function init() {

		$this->context = 'gravityforms';

		add_filter( 'gform_entry_created', array( $this, 'add_pending_referral' ), 10, 2 );
		add_action( 'gform_post_payment_completed', array( $this, 'mark_referral_complete' ), 10, 2 );
		add_action( 'gform_post_payment_refunded', array( $this, 'revoke_referral_on_refund' ), 10, 2 );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

	}

	public function add_pending_referral( $entry, $form ) {

		if( $this->was_referred() ) {

			// Do some craziness to determine the price (this should be easy but is not)

			$desc      = '';
			$entry     = GFFormsModel::get_lead( $entry['id'] );
			$products  = GFCommon::get_product_fields( $form, $entry );
			$total     = 0;
			foreach ( $products['products'] as $key => $product ) {	

				$desc .= $product['name'];
				if( $key + 1 < count( $products ) ) {
					$description .= ', ';
				}

				$price = GFCommon::to_number( $product['price'] );
				if ( is_array( rgar( $product,'options' ) ) ) {
					$count = sizeof( $product['options'] );
					$index = 1;
					foreach ( $product['options'] as $option ) {
						$price += GFCommon::to_number( $option['price'] );
					}
				}
				$subtotal = floatval( $product['quantity'] ) * $price;
				$total += $subtotal;

			}

			$total += floatval( $products['shipping']['price'] );

			$referral_total = $this->calculate_referral_amount( $total, $entry['id'] );

			$this->insert_pending_referral( $referral_total, $entry['id'], $desc );
		
		}

	}

	public function mark_referral_complete( $entry, $action ) {
		
		$this->complete_referral( $entry['id'] );

		$referral = affiliate_wp()->referrals->get_by( 'reference', $entry['id'], $this->context );
		$amount   = affwp_currency_filter( affwp_format_amount( $referral->amount ) );
		$name     = affiliate_wp()->affiliates->get_affiliate_name( $referral->affiliate_id );
		$note     = sprintf( __( 'Referral #%d for %s recorded for %s', 'affiliate-wp' ), $referral->referral_id, $amount, $name );

		GFFormsModel::add_note( $entry["id"], 0, 'AffiliateWP', $note );
	}

	public function revoke_referral_on_refund( $entry, $action ) {
		
		$this->reject_referral( $entry['id'] );

		$referral = affiliate_wp()->referrals->get_by( 'reference', $entry['id'], $this->context );
		$amount   = affwp_currency_filter( affwp_format_amount( $referral->amount ) );
		$name     = affiliate_wp()->affiliates->get_affiliate_name( $referral->affiliate_id );
		$note     = sprintf( __( 'Referral #%d for %s for %s rejected', 'affiliate-wp' ), $referral->referral_id, $amount, $name );

		GFFormsModel::add_note( $entry["id"], 0, 'AffiliateWP', $note );
	
	}

	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'gravityforms' != $referral->context ) {

			return $reference;

		}

		$entry = GFFormsModel::get_lead( $reference );

		$url = admin_url( 'admin.php?page=gf_entries&view=entry&id=' . $entry['form_id'] . '&lid=' . $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}

}
new Affiliate_WP_Gravity_Forms;