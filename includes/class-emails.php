<?php

class Affiliate_WP_Emails {

	public function __construct() {

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
					$message .= sprintf( "Review pending applications: %s\n\n", admin_url( 'admin.php?page=affiliate-wp&status=pending' ) ); 

				}

				break;

		}

		$this->send( $email, $subject, $message );
	}

	public function send( $email, $subject, $message ) {

		$headers   = array();
		$headers[] = 'From: ' . stripslashes_deep( html_entity_decode( get_option( 'blog_name' ), ENT_COMPAT, 'UTF-8' ) ) . '<' . get_option( 'admin_email' ) . '>';
		$headers   = apply_filters( 'affwp_email_headers', $headers );

		wp_mail( $email, $subject, $message, $headers );

	}

}