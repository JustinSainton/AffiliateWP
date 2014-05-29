<?php

class Affiliate_WP_Emails {

	public function __construct() {

		add_action( 'affwp_register_user', array( $this, 'notify_on_registration' ), 10, 2 );
		add_action( 'affwp_set_affiliate_status', array( $this, 'notify_on_approval' ), 10, 3 );
		add_action( 'affwp_referral_accepted', array( $this, 'notify_of_new_referral' ), 10, 2 );

	}

	public function notify_on_registration( $affiliate_id = 0, $status = '' ) {

		if( affiliate_wp()->settings->get( 'registration_notifications' ) ) {
			affiliate_wp()->emails->notification( 'registration', array( 'affiliate_id' => $affiliate_id ) );
		}
	}

	public function notify_on_approval( $affiliate_id = 0, $status = '', $old_status = '' ) {

		if ( 'active' != $status || 'pending' != $old_status ) {
			return;
		}

		affiliate_wp()->emails->notification( 'application_accepted', array( 'affiliate_id' => $affiliate_id ) );
	}

	public function notify_of_new_referral( $affiliate_id = 0, $referral ) {

		$user_id = affwp_get_affiliate_user_id( $affiliate_id );

		if( ! get_user_meta( $user_id, 'affwp_referral_notifications', true ) ) {
			return;
		}

		affiliate_wp()->emails->notification( 'new_referral', array( 'affiliate_id' => $affiliate_id, 'amount' => $referral->amount ) );
	}

	public function notification( $type = '', $args = array() ) {

		if( empty( $type ) ) {
			return false;
		}

		switch( $type ) {

			case 'registration' :

				$email    = get_option( 'admin_email' );
				$subject  = __( 'New Affiliate Registration', 'affiliate-wp' );
				$message  = "A new affiliate has registered on your site, " . home_url() ."\n\n";
				$message .= __( 'Name: ', 'affiliate-wp' ) . affiliate_wp()->affiliates->get_affiliate_name( $args['affiliate_id'] ) . "\n\n";

				if( affiliate_wp()->settings->get( 'require_approval' ) ) {
					$message .= sprintf( "Review pending applications: %s\n\n", admin_url( 'admin.php?page=affiliate-wp-affiliates&status=pending' ) ); 

				}

				$subject = apply_filters( 'affwp_registration_subject', $subject, $args );
				$message = apply_filters( 'affwp_registration_email', $message, $args );

				break;

			case 'application_accepted' :

				$email    = affwp_get_affiliate_email( $args['affiliate_id'] );
				$subject  = __( 'Affiliate Application Accepted', 'affiliate-wp' );
				$message  = sprintf( __( "Congratulations %s!\n\n", "affiliate-wp" ), affiliate_wp()->affiliates->get_affiliate_name( $args['affiliate_id'] ) );
				$message .= sprintf( __( "Your affiliate application on %s has been accepted!\n\n", "affiliate-wp" ), home_url() );
				$message .= sprintf( __( "Log into your affiliate area at %s\n\n", "affiliate-wp" ), affiliate_wp()->login->get_login_url() );

				$subject = apply_filters( 'affwp_application_accepted_subject', $subject, $args );
				$message = apply_filters( 'affwp_application_accepted_email', $message, $args );

				break;

			case 'new_referral' :

				$email    = affwp_get_affiliate_email( $args['affiliate_id'] );
				$subject  = __( 'Referral Awarded!', 'affiliate-wp' );
				$amount   = html_entity_decode( affwp_currency_filter( $args['amount'] ), ENT_COMPAT, 'UTF-8' );
				$message  = sprintf( __( "Congratulations %s!\n\n", "affiliate-wp" ), affiliate_wp()->affiliates->get_affiliate_name( $args['affiliate_id'] ) );
				$message .= sprintf( __( "You have been awarded a new referral of %s on %s!\n\n", "affiliate-wp" ), $amount, home_url() );
				$message .= sprintf( __( "Log into your affiliate area to view your earnings or disable these notifications: %s\n\n", "affiliate-wp" ), affiliate_wp()->login->get_login_url() );

				$subject = apply_filters( 'affwp_new_referral_subject', $subject, $args );
				$message = apply_filters( 'affwp_new_referral_email', $message, $args );

				break;

		}

		$this->send( $email, $subject, $message );
	}

	public function send( $email, $subject, $message ) {

		$headers   = array();
		$headers[] = 'From: ' . stripslashes_deep( html_entity_decode( get_bloginfo( 'name' ), ENT_COMPAT, 'UTF-8' ) ) . ' <' . get_option( 'admin_email' ) . '>';
		$headers   = apply_filters( 'affwp_email_headers', $headers );

		wp_mail( $email, $subject, $message, $headers );

	}

}