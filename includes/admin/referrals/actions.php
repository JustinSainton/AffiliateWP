<?php

/**
 * Process the add referral request
 *
 * @since 1.2
 * @return void
 */
function affwp_process_add_referral( $data ) {

	if ( ! is_admin() ) {
		return false;
	}

	if ( ! current_user_can( 'manage_referrals' ) ) {
		wp_die( __( 'You do not have permission to manage referrals', 'affiliate-wp' ), __( 'Error', 'affiliate-wp' ), array( 'response' => 403 ) );
	}

	if ( ! wp_verify_nonce( $data['affwp_add_referral_nonce'], 'affwp_add_referral_nonce' ) ) {
		wp_die( __( 'Security check failed', 'affiliate-wp' ), array( 'response' => 403 ) );
	}

	if ( affwp_add_referral( $data ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-referrals&affwp_notice=referral_added' ) );
		exit;
	} else {
		wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-referrals&affwp_notice=referral_add_failed' ) );
		exit;
	}

}
add_action( 'affwp_add_referral', 'affwp_process_add_referral' );

/**
 * Process the update referral request
 *
 * @since 1.2
 * @return void
 */
function affwp_process_update_referral( $data ) {

	if ( ! is_admin() ) {
		return false;
	}

	if ( ! current_user_can( 'manage_referrals' ) ) {
		wp_die( __( 'You do not have permission to manage referrals', 'affiliate-wp' ), array( 'response' => 403 ) );
	}

	if ( ! wp_verify_nonce( $data['affwp_edit_referral_nonce'], 'affwp_edit_referral_nonce' ) ) {
		wp_die( __( 'Security check failed', 'affiliate-wp' ), array( 'response' => 403 ) );
	}

	if ( affiliate_wp()->referrals->update_referral( $data['referral_id'], $data ) ) {
		wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-referrals&affwp_notice=referral_updated' ) );
		exit;
	} else {
		wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-referrals&affwp_notice=referral_update_failed' ) );
		exit;
	}

}
add_action( 'affwp_process_update_referral', 'affwp_process_update_referral' );

/**
 * Process the referral payout file generation
 *
 * @since 1.0
 * @return void
 */
function affwp_generate_referral_payout_file( $data ) {

	$export = new Affiliate_WP_Referral_Payout_Export;
	$export->date = array(
		'start' => $data['from'],
		'end'   => $data['to'] . ' 23:59:59'
	);
	$export->export();

}
add_action( 'affwp_generate_referral_payout', 'affwp_generate_referral_payout_file' );