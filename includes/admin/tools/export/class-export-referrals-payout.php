<?php
/**
 * Export Class
 *
 * This is the base class for all export methods. Each data export type (referrals, affiliates, visits) extend this class
 *
 * @package     AffiliateWP
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
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.0
	 */
	public $export_type = 'referrals_payout';

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
			'date'   => ! empty( $this->date ) ? $this->date : '',
			'number' => -1
		);

		// Final data to be exported
		$data         = array();

		// The affiliates that have earnings to be paid
		$affiliates   = array();

		// The list of referrals that are possibly getting marked as paid
		$to_maybe_pay = array();

		// Retrieve the referrals from the database
		$referrals    = affiliate_wp()->referrals->get_referrals( $args );

		// The minimum payout amount
		$minimum      = ! empty( $_POST['minimum'] ) ? sanitize_text_field( affwp_sanitize_amount( $_POST['minimum'] ) ) : 0;

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

					$affiliates[] = $referral->affiliate_id;

				}

				// Add the referral to the list of referrals to maybe payout
				if( ! array_key_exists( $referral->affiliate_id, $to_maybe_pay ) ) {

					$to_maybe_pay[ $referral->affiliate_id ] = array();

				}

				$to_maybe_pay[ $referral->affiliate_id ][] = $referral->referral_id;

			}

			// Now determine which affiliates are above the minimum payout amount
			if( $minimum > 0 ) {
				foreach( $data as $affiliate_id => $payout ) {

					if( $payout['amount'] < $minimum ) {
						unset( $data[ $affiliate_id ] );
						unset( $to_maybe_pay[ $affiliate_id ] );
					}

				}
			}

			// We now know which referrals should be marked as paid
			foreach( $to_maybe_pay as $referral_list ) {
				foreach( $referral_list as $referral_id ) {
					affwp_set_referral_status( $referral_id, 'paid' );
				}
			}

		}

		$data = apply_filters( 'affwp_export_get_data', $data );
		$data = apply_filters( 'affwp_export_get_data_' . $this->export_type, $data );

		return $data;
	}

}