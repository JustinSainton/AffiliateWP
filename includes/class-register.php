<?php

class Affiliate_WP_Register {

	private $errors;

	public function __construct() {

		add_action( 'affwp_affiliate_register', array( $this, 'process_registration' ) );

	}

	public function process_registration( $data ) {

		if( ! isset( $_POST['affwp_register_nonce'] ) || ! wp_verify_nonce( $_POST['affwp_register_nonce'], 'affwp-register-nonce' ) ) {
			return;
		}

		do_action( 'affwp_pre_process_register_form' );

		if( ! is_user_logged_in() ) {

			if( empty( $data['affwp_user_login'] ) ) {
				$this->add_error( 'empty_username', __( 'Invalid username', 'affiliate-wp' ) );
			}

			if( username_exists( $data['affwp_user_login'] ) ) {
				$this->add_error( 'username_unavailable', __( 'Username already taken', 'affiliate-wp' ) );
			}

			if( ! validate_username( $data['affwp_user_login'] ) ) {
				$this->add_error( 'username_invalid', __( 'Invalid username', 'affiliate-wp' ) );
			}

			if( empty( $data['affwp_user_email'] ) || ! is_email( $data['affwp_user_email'] ) ) {
				$this->add_error( 'email_invalid', __( 'Invalid email', 'affiliate-wp' ) );
			}

			if( empty( $_POST['affwp_user_pass'] ) ) {
				$this->add_error( 'empty_password', __( 'Please enter a password', 'affiliate-wp' ) );
			}

			if( ( ! empty( $_POST['affwp_user_pass'] ) && empty( $_POST['affwp_user_pass2'] ) ) || ( $_POST['affwp_user_pass'] !== $_POST['affwp_user_pass2'] ) ) {
				$this->add_error( 'password_mismatch', __( 'Passwords do not match', 'affiliate-wp' ) );
			}

			if( empty( $_POST['affwp_user_name'] ) ) {
				$this->add_error( 'empty_name', __( 'Please enter your name', 'affiliate-wp' ) );
			}

		}

		if( empty( $_POST['affwp_tos'] ) ) {
			$this->add_error( 'empty_tos', __( 'Please agree to our terms of use', 'affiliate-wp' ) );
		}

		do_action( 'affwp_process_register_form' );

		// only log the user in if there are no errors
		if( empty( $this->errors ) ) {

			$this->register_user();

		}
	}

	private function register_user() {

		$status  = affiliate_wp()->settings->get( 'require_approval' ) ? 'pending' : 'active';

		if( ! is_user_logged_in() ) {

			$name       = explode( ' ', sanitize_text_field( $_POST['affwp_user_name'] ) );
			$user_first = $name[0];
			$user_last  = isset( $name[1] ) ? $name[1] : '';

			$args = array(
				'user_login'   => sanitize_text_field( $_POST['affwp_user_login'] ),
				'user_email'   => sanitize_text_field( $_POST['affwp_user_email'] ),
				'user_pass'    => sanitize_text_field( $_POST['affwp_user_pass'] ),
				'display_name' => $user_first . ' ' . $user_last,
				'first_name'   => $user_first,
				'last_name'    => $user_last
			);

			$user_id = wp_insert_user( $args );
		
			$this->log_user_in( $user_id, sanitize_text_field( $_POST['affwp_user_login'] ) );

		} else {

			$user_id = get_current_user_id();

		}

		$affiliate_id = affiliate_wp()->affiliates->add( array( 'status' => $status, 'user_id' => $user_id ) );

		if( affiliate_wp()->settings->get( 'registration_notifications' ) ) {
			affiliate_wp()->emails->notification( 'registration', array( 'affiliate_id' => $affiliate_id ) );
		}

	}

	private function log_user_in( $user_id = 0, $user_login = '', $remember = false ) {

		$user = get_userdata( $user_id );
		if( ! $user )
			return;

		wp_set_auth_cookie( $user_id, $remember );
		wp_set_current_user( $user_id, $user_login );
		do_action( 'wp_login', $user_login, $user );

	}

	private function add_error( $error_id, $message = '' ) {
		$this->errors[ $error_id ] = $message;
	}

	public function print_errors() {

		if( empty( $this->errors ) ) {
			return;
		}

		echo '<div class="affwp_errors">';

		foreach( $this->errors as $error_id => $error ) {

			echo '<p class="affwp_error">' . esc_html( $error ) . '</p>';

		}

		echo '</div>';

	}

}