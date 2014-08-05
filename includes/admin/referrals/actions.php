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
		wp_die( __( 'You do not have permission to manage referrals', 'affiliate-wp' ) );
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