<?php

class Affiliate_WP_Emails {

	public function __construct() {

		add_action( 'affwp_set_affiliate_status', array( $this, 'notify_on_approval' ), 10, 3 );

	}

	public function notify_on_approval( $affiliate_id = 0, $status = '', $old_status = '' ) {

		if ( 'active' != $status || 'pending' != $old_status ) {
			return;
		}

		affiliate_wp()->emails->notification( 'application_accepted', array( 'affiliate_id' => $affiliate_id ) );
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

				$message = apply_filters( 'affwp_registration_email', $message, $args );

				break;

			case 'application_accepted' :

				$email    = affwp_get_affiliate_email( $args['affiliate_id'] );
				$subject  = __( 'Affiliate Application Accepted', 'affiliate-wp' );
				$message  = sprintf( __( "Congratulations %s!\n\n", "affiliate-wp" ), affiliate_wp()->affiliates->get_affiliate_name( $args['affiliate_id'] ) );
				$message .= sprintf( __( "Your affiliate application on %s has been accepted!\n\n", "affiliate-wp" ), home_url() );
				$message .= sprintf( __( "Log into your affiliate area at %s\n\n", "affiliate-wp" ), get_permalink( affiliate_wp()->settings->get( 'affiliates_page' ) ) );

				$message = apply_filters( 'affwp_application_accepted_email', $message, $args );

				break;

		}

		$this->send( $email, $subject, $message );
	}

	public function send( $email, $subject, $message ) {

		$headers   = array();
		$headers[] = 'From: ' . stripslashes_deep( html_entity_decode( get_bloginfo( 'name' ), ENT_COMPAT, 'UTF-8' ) ) . '<' . get_option( 'admin_email' ) . '>';
		$headers   = apply_filters( 'affwp_email_headers', $headers );

		wp_mail( $email, $subject, $message, $headers );

	}

}