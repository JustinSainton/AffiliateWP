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
function affwp_notify_on_registration( $affiliate_id = 0, $status = '', $args = array() ) {

	if( ! affiliate_wp()->settings->get( 'registration_notifications' ) ) {
		return;
	}

	if( empty( $affiliate_id ) || empty( $status ) ) {
		return;
	}

	$emails           = new Affiliate_WP_Emails;
	$emails->__set( 'affiliate_id', $affiliate_id );

	$email            = apply_filters( 'affwp_registration_admin_email', get_option( 'admin_email' ) );
	$user_info        = get_userdata( affwp_get_affiliate_user_id( $affiliate_id ) );
	$user_url         = $user_info->user_url;
	$promotion_method = get_user_meta( affwp_get_affiliate_user_id( $affiliate_id ), 'affwp_promotion_method', true );

	$subject          = affiliate_wp()->settings->get( 'registration_subject', __( 'New Affiliate Registration', 'affiliate-wp' ) );
	$message          = affiliate_wp()->settings->get( 'registration_email', '' );

	if( empty( $message ) ) {

		$message  = __( 'A new affiliate has registered on your site, ', 'affiliate-wp' ) . home_url() . "\n\n";
		$message .= sprintf( __( 'Name: %s', 'affiliate-wp' ), $args['display_name'] ) . "\n\n";

		if( $user_url ) {
			$message .= sprintf( __( 'Website URL: %s', 'affiliate-wp' ), esc_url( $user_url ) ) . "\n\n";
		}

		if( $promotion_method ) {
			$message .= sprintf( __( 'Promotion method: %s', 'affiliate-wp' ), esc_attr( $promotion_method ) ) . "\n\n";
		}

		if( affiliate_wp()->settings->get( 'require_approval' ) ) {
			$message .= sprintf( __( 'Review pending applications: %s', 'affiliate-wp' ), admin_url( 'admin.php?page=affiliate-wp-affiliates&status=pending' ) ) . "\n\n";
		}

	}

	// $args is setup for backwards compatibility with < 1.6
	$args    = array( 'affiliate_id' => $affiliate_id, 'name' => $args['display_name'] );
	$subject = apply_filters( 'affwp_registration_subject', $subject, $args );
	$message = apply_filters( 'affwp_registration_email', $message, $args );

	$emails->send( $email, $subject, $message );

}
add_action( 'affwp_register_user', 'affwp_notify_on_registration', 10, 3 );


/**
 * Send email on affiliate approval
 *
 * @since 1.6
 * @param int $affiliate_id The ID of the registered affiliate
 * @param string $status
 * @param string $old_status
 */
function affwp_notify_on_approval( $affiliate_id = 0, $status = '', $old_status = '' ) {

	if( empty( $affiliate_id ) ) {
		return;
	}

	if( 'active' != $status || 'pending' != $old_status ) {
		return;
	}

	$emails       = new Affiliate_WP_Emails;
	$emails->__set( 'affiliate_id', $affiliate_id );

	$email        = affwp_get_affiliate_email( $affiliate_id );
	$subject      = affiliate_wp()->settings->get( 'accepted_subject', __( 'Affiliate Application Accepted', 'affiliate-wp' ) );
	$message      = affiliate_wp()->settings->get( 'accepted_email', '' );

	if( empty( $message ) ) {
		$message  = sprintf( __( 'Congratulations %s!', 'affiliate-wp' ), affiliate_wp()->affiliates->get_affiliate_name( $affiliate_id ) ) . "\n\n";
		$message .= sprintf( __( 'Your affiliate application on %s has been accepted!', 'affiliate-wp' ), home_url() ) . "\n\n";
		$message .= sprintf( __( 'Log into your affiliate area at %s', 'affiliate-wp' ), affiliate_wp()->login->get_login_url() ) . "\n\n";
	}

	// $args is setup for backwards compatibility with < 1.6
	$args         = array( 'affiliate_id' => $affiliate_id );
	$subject      = apply_filters( 'affwp_application_accepted_subject', $subject, $args );
	$message      = apply_filters( 'affwp_application_accepted_email', $message, $args );

	$emails->send( $email, $subject, $message );

}
add_action( 'affwp_set_affiliate_status', 'affwp_notify_on_approval', 10, 3 );

/**
 * Send email on pending affiliate registration
 *
 * @since 1.6.1
 * @param int $affiliate_id The ID of the registered affiliate
 * @param string $status
 * @param array $args
 */
function affwp_notify_on_pending_affiliate_registration( $affiliate_id = 0, $status = '', $args ) {

	if ( empty( $affiliate_id ) ) {
		return;
	}

	if ( 'pending' != $status ) {
		return;
	}

	$emails       = new Affiliate_WP_Emails;
	$emails->__set( 'affiliate_id', $affiliate_id );

	$email        = affwp_get_affiliate_email( $affiliate_id );
	$subject      = affiliate_wp()->settings->get( 'pending_subject', __( 'Your Affiliate Application Is Being Reviewed', 'affiliate-wp' ) );
	$message      = affiliate_wp()->settings->get( 'pending_email', '' );

	if ( empty( $message ) ) {
		$message  = sprintf( __( 'Hi %s!', 'affiliate-wp' ), affiliate_wp()->affiliates->get_affiliate_name( $affiliate_id ) ) . "\n\n";
		$message .= __( 'Thanks for your recent affiliate registration on {site_name}.', 'affiliate-wp' ) . "\n\n";
		$message .= __( 'We\'re currently reviewing your affiliate application and will be in touch soon!', 'affiliate-wp' ) . "\n\n";
	}

	$emails->send( $email, $subject, $message );

}
add_action( 'affwp_register_user', 'affwp_notify_on_pending_affiliate_registration', 10, 3 );

/**
 * Send email on rejected affiliate registration
 *
 * @since 1.6.1
 * @param int $affiliate_id The ID of the registered affiliate
 * @param string $status
 * @param string $old_status
 */
function affwp_notify_on_rejected_affiliate_registration( $affiliate_id = 0, $status = '', $old_status = '' ) {

	if ( empty( $affiliate_id ) ) {
		return;
	}

	if ( 'rejected' != $status || 'pending' != $old_status ) {
		return;
	}

	$emails       = new Affiliate_WP_Emails;
	$emails->__set( 'affiliate_id', $affiliate_id );

	$email        = affwp_get_affiliate_email( $affiliate_id );
	$subject      = affiliate_wp()->settings->get( 'rejection_subject', __( 'Your Affiliate Application Has Been Rejected', 'affiliate-wp' ) );
	$message      = affiliate_wp()->settings->get( 'rejection_email', '' );

	if ( empty( $message ) ) {
		$message  = sprintf( __( 'Hi %s,', 'affiliate-wp' ), affiliate_wp()->affiliates->get_affiliate_name( $affiliate_id ) ) . "\n\n";
		$message .= __( 'We regret to inform you that your recent affiliate registration on {site_name} was rejected.', 'affiliate-wp' ) . "\n\n";
	}

	$emails->send( $email, $subject, $message );

}
add_action( 'affwp_set_affiliate_status', 'affwp_notify_on_rejected_affiliate_registration', 10, 3 );

/**
 * Send email on new referrals
 *
 * @since 1.6
 * @param int $affiliate_id The ID of the registered affiliate
 * @param array $referral
 */
function affwp_notify_on_new_referral( $affiliate_id = 0, $referral ) {

	$user_id = affwp_get_affiliate_user_id( $affiliate_id );

	if( ! get_user_meta( $user_id, 'affwp_referral_notifications', true ) ) {
		return;
	}

	if( empty( $affiliate_id ) ) {
		return;
	}

	if( empty( $referral ) ) {
		return;
	}

	$emails  = new Affiliate_WP_Emails;
	$emails->__set( 'affiliate_id', $affiliate_id );
	$emails->__set( 'referral', $referral );

	$email   = affwp_get_affiliate_email( $affiliate_id );
	$subject = affiliate_wp()->settings->get( 'referral_subject', __( 'Referral Awarded!', 'affiliate-wp' ) );
	$message = affiliate_wp()->settings->get( 'referral_email', false );
	$amount  = html_entity_decode( affwp_currency_filter( $referral->amount ), ENT_COMPAT, 'UTF-8' );

	if( ! $message ) {
		$message  = sprintf( __( 'Congratulations %s!', 'affiliate-wp' ), affiliate_wp()->affiliates->get_affiliate_name( $affiliate_id ) ) . "\n\n";
		$message .= sprintf( __( 'You have been awarded a new referral of %s on %s!', 'affiliate-wp' ), $amount, home_url() ) . "\n\n";
		$message .= sprintf( __( 'log into your affiliate area to view your earnings or disable these notifications: %s', 'affiliate-wp' ), affiliate_wp()->login->get_login_url() ) . "\n\n";
	}

	// $args is setup for backwards compatibility with < 1.6
	$args    = array( 'affiliate_id' => $affiliate_id, 'amount' => $referral->amount, 'referral' => $referral );
	$subject = apply_filters( 'affwp_new_referral_subject', $subject, $args );
	$message = apply_filters( 'affwp_new_referral_email', $message, $args );

	$emails->send( $email, $subject, $message );

}
add_action( 'affwp_referral_accepted', 'affwp_notify_on_new_referral', 10, 2 );
