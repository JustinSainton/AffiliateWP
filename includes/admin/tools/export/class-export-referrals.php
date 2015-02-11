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
class Affiliate_WP_Referral_Export extends Affiliate_WP_Export {

	/**
	 * Our export type. Used for export-type specific filters/actions
	 * @var string
	 * @since 1.0
	 */
	public $export_type = 'referrals';

	/**
	 * ate
	 * @var array
	 * @since 1.0
	 */
	public $date;

	/**
	 * Status
	 * @var string
	 * @since 1.0
	 */
	public $status;

	/**
	 * Affiliate ID
	 * @var int
	 * @since 1.0
	 */
	public $affiliate = null;

	/**
	 * Set the CSV columns
	 *
	 * @access public
	 * @since 1.0
	 * @return array $cols All the columns
	 */
	public function csv_cols() {
		$cols = array(
			'affiliate_id' => __( 'Affiliate ID', 'affiliate-wp' ),
			'email'        => __( 'Email', 'affiliate-wp' ),
			'amount'       => __( 'Amount', 'affiliate-wp' ),
			'currency'     => __( 'Currency', 'affiliate-wp' ),
			'reference'    => __( 'Reference', 'affiliate-wp' ),
			'context'      => __( 'Context', 'affiliate-wp' ),
			'status'       => __( 'Status', 'affiliate-wp' ),
			'date'         => __( 'Date', 'affiliate-wp' )
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
			'status'       => $this->status,
			'date'         => ! empty( $this->date ) ? $this->date : '',
			'affiliate_id' => $this->affiliate,
			'number'       => -1
		);

		$data         = array();
		$affiliates   = array();
		$referral_ids = array();
		$referrals    = affiliate_wp()->referrals->get_referrals( $args );

		if( $referrals ) {

			foreach( $referrals as $referral ) {

				$data[] = array(
					'affiliate_id' => $referral->affiliate_id,
					'email'        => affwp_get_affiliate_email( $referral->affiliate_id ),
					'amount'       => $referral->amount,
					'currency'     => $referral->currency,
					'reference'    => $referral->reference,
					'context'      => $referral->context,
					'status'       => $referral->status,
					'date'         => $referral->date,
				);

			}

		}

		$data = apply_filters( 'affwp_export_get_data', $data );
		$data = apply_filters( 'affwp_export_get_data_' . $this->export_type, $data );

		return $data;
	}

}