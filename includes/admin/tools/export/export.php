<?php
/**
 * Export processing
 *
 * @package     Affiliate WP
 * @subpackage  Admin/Tools/Export
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Process a referrals export
 *
 * @since       1.0
 * @return      void
 */
function affwp_process_referrals_export() {

	if( empty( $_POST['affwp_export_referrals_nonce'] ) ) {
		return;
	}

	if( ! wp_verify_nonce( $_POST['affwp_export_referrals_nonce'], 'affwp_export_referrals_nonce' ) ) {
		return;
	}

	if( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	$start  = ! empty( $_POST['start_date'] ) ? sanitize_text_field( $_POST['start_date'] ) : false;
	$end    = ! empty( $_POST['end_date'] )   ? sanitize_text_field( $_POST['end_date'] )   : false;
	$status = ! empty( $_POST['status'] )     ? sanitize_text_field( $_POST['status'] )     : false;

	$export = new Affiliate_WP_Referral_Export;
	$export->date = array(
		'start' => $start,
		'end'   => $end
	);
	$export->status = $status;
	$export->export();

}
add_action( 'affwp_export_referrals', 'affwp_process_referrals_export' );