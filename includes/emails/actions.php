<?php
/**
 * Email actions
 *
 * @package AffiliateWP\Emails\Actions
 * @since 1.6
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Send email on affiliate registration
 *
 * @since 1.6
 * @param int $affiliate_id The ID of the registered affiliate
 * @param string $status
 * @param array $args
 * @return void
 */
function affiliate_wp_notify_on_registration( $affiliate_id = 0, $status = '', $args = array() ) {
	if( affiliate_wp()->settings->get( 'registration_notifications' ) ) {
		affiliate_wp()->emails->notification( 'registration', array( 'affiliate_id' => $affiliate_id, 'name' => $args['display_name'] ) );
	}
}
add_action( 'affwp_register_user', 'affiliate_wp_notify_on_registration', 10, 3 );


/**
 * Send email on affiliate approval
 *
 * @since 1.6
 * @param int $affiliate_id The ID of the registered affiliate
 * @param string $status
 * @param string $old_status
 */
function affiliate_wp_notify_on_approval( $affiliate_id = 0, $status = '', $old_status = '' ) {
	if( 'active' != $status || 'pending' != $old_status ) {
		return;
	}

	affiliate_wp()->emails->notification( 'application_accepted', array( 'affiliate_id' => $affiliate_id ) );
}
add_action( 'affwp_set_affiliate_status', 'affiliate_wp_notify_on_approval', 10, 3 );


/**
 * Send email on new referrals
 *
 * @since 1.6
 * @param int $affiliate_id The ID of the registered affiliate
 * @param array $referral
 */
function affiliate_wp_notify_on_new_referral( $affiliate_id = 0, $referral ) {
	$user_id = affwp_get_affiliate_user_id( $affiliate_id );

	if( ! get_user_meta( $user_id, 'affwp_referral_notifications', true ) ) {
		return;
	}

	affiliate_wp()->emails->notification( 'new_referral', array( 'affiliate_id' => $affiliate_id, 'amount' => $referral->amount ) );
}
add_action( 'affwp_referral_accepted', 'affiliate_wp_notify_on_new_referral', 10, 2 );
