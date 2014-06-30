<?php

class Affiliate_WP_Gravity_Forms extends Affiliate_WP_Base {

	public function init() {

		$this->context = 'gravityforms';

		add_action( 'gform_after_submission', array( $this, 'add_pending_referral' ), 10, 2 );
		add_action( 'gform_entry_created', array( $this, 'mark_referral_complete' ), 10, 100 );
		add_action( 'gform_update_payment_status', array( $this, 'mark_referral_complete' ), 10, 3 );

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

			$this->insert_pending_referral( $total, $entry['id'], $desc );
		
		}

	}

	public function mark_referral_complete( $entry, $form ) {
		echo '<pre>'; print_r( $entry ); echo '</pre>'; exit;
		if( empty( $entry['payment_status'] ) ) {
			return;
		}

		if( 'Approved' == $entry['payment_status'] || 'Active' == $entry['payment_status'] ) {

			$this->complete_referral( $entry['id'] );
	
		}

	}

	public function mark_referral_complete_on_change( $lead_id, $property_value, $previous_value ) {

		if( $property_value != 'Paid' && $property_value != 'Approved' ) {
			return;
		}

		$this->complete_referral( $lead_id );
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