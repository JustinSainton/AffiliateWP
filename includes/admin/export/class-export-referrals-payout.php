<?php
/**
 * Export Class
 *
 * This is the base class for all export methods. Each data export type (referrals, affiliates, visits) extend this class
 *
 * @package     Affiliate WP
 * @subpackage  Admin/Export
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Affiliate_WP_Export Class
 *
 * @since 1.0
 */
class Affiliate_WP_Referral_Payout_Export extends Affiliate_WP_Referral_Export {

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 1.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'email'    => __( 'Email', 'affiliate-wp' ),
			'amount'   => __( 'Amount', 'affiliate-wp' ),
			'currency' => __( 'Currency', 'affiliate-wp' ),
		);
		return $cols;
	}

	/**
	 * Get the data being exported
	 *
	 * @access public
	 * @since 1.0
	 * @return array $data Data for Export
	 */
	public function get_data() {

		$args = array(

			'status' => 'unpaid',
			'date'   => ! empty( $this->date ) ? $this->date   : '',

		);

		$data         = array();
		$affiliates   = array();
		$referral_ids = array();
		$referrals    = affiliate_wp()->referrals->get_referrals( $args );

		if( $referrals ) {

			foreach( $referrals as $referral ) {

				if( in_array( $referral->affiliate_id, $affiliates ) ) {

					// Add the amount to an affiliate that already has a referral in the export

					$amount = $data[ $referral->affiliate_id ]['amount'] + $referral->amount;

					$data[ $referral->affiliate_id ]['amount'] = $amount;

				} else {

					$email = affwp_get_affiliate_email( $referral->affiliate_id );
		
					$data[ $referral->affiliate_id ] = array(
						'email'    => $email,
						'amount'   => $referral->amount,
						'currency' => ! empty( $referral->currency ) ? $referral->currency : affwp_get_currency() 
					);

					$affiliates[]   = $referral->affiliate_id;
					$referral_ids[] = $referral->referral_id;

				}

			}

			affiliate_wp()->referrals->bulk_update_status( $referral_ids, 'paid' );

		}

		$data = apply_filters( 'affwp_export_get_data', $data );
		$data = apply_filters( 'affwp_export_get_data_' . $this->export_type, $data );

		return $data;
	}

}