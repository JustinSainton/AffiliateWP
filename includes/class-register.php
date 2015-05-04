<?php

class Affiliate_WP_Register {

	private $errors;

	/**
	 * Get things started
	 *
	 * @since 1.0
	 */
	public function __construct() {

		add_action( 'affwp_affiliate_register', array( $this, 'process_registration' ) );
		add_action( 'user_register', array( $this, 'auto_register_user_as_affiliate' ) );

	}

	/**
	 * Register Form
	 *
	 * @since 1.2
	 * @global $affwp_register_redirect
	 * @param string $redirect Redirect page URL
	 * @return string Register form
	*/
	public function register_form( $redirect = '' ) {
		global $affwp_register_redirect;

		if ( empty( $redirect ) ) {
			$redirect = affiliate_wp()->tracking->get_current_page_url();
		}

		$affwp_register_redirect = $redirect;

		ob_start();

		affiliate_wp()->templates->get_template_part( 'register' );

		return apply_filters( 'affwp_register_form', ob_get_clean() );

	}
	
	/**
	 * Process registration form submission
	 *
	 * @since 1.0
	 */
	public function process_registration( $data ) {

		if ( ! isset( $_POST['affwp_register_nonce'] ) || ! wp_verify_nonce( $_POST['affwp_register_nonce'], 'affwp-register-nonce' ) ) {
			return;
		}

		do_action( 'affwp_pre_process_register_form' );

		if ( ! is_user_logged_in() ) {

			// Loop through required fields and show error message
			foreach ( $this->required_fields() as $field_name => $value ) {
				if ( empty( $_POST[ $field_name ] ) ) {
					$this->add_error( $value['error_id'], $value['error_message'] );
				}
			}

			if ( username_exists( $data['affwp_user_login'] ) ) {
				$this->add_error( 'username_unavailable', __( 'Username already taken', 'affiliate-wp' ) );
			}

			if ( ! validate_username( $data['affwp_user_login'] ) ) {
				if ( is_multisite() ) {
					$this->add_error( 'username_invalid', __( 'Invalid username. Only lowercase letters (a-z) and numbers are allowed', 'affiliate-wp' ) );
				} else {
					$this->add_error( 'username_invalid', __( 'Invalid username', 'affiliate-wp' ) );
				}
			}

			if ( email_exists( $data['affwp_user_email'] ) ) {
				$this->add_error( 'email_unavailable', __( 'Email address already taken', 'affiliate-wp' ) );
			}

			if ( empty( $data['affwp_user_email'] ) || ! is_email( $data['affwp_user_email'] ) ) {
				$this->add_error( 'email_invalid', __( 'Invalid email', 'affiliate-wp' ) );
			}

			if ( ! empty( $data['affwp_payment_email'] ) && $data['affwp_payment_email'] != $data['affwp_user_email'] && ! is_email( $data['affwp_payment_email'] ) ) {
				$this->add_error( 'payment_email_invalid', __( 'Invalid payment email', 'affiliate-wp' ) );
			}

			if ( ( ! empty( $_POST['affwp_user_pass'] ) && empty( $_POST['affwp_user_pass2'] ) ) || ( $_POST['affwp_user_pass'] !== $_POST['affwp_user_pass2'] ) ) {
				$this->add_error( 'password_mismatch', __( 'Passwords do not match', 'affiliate-wp' ) );
			}

		}

		$terms_of_use = affiliate_wp()->settings->get( 'terms_of_use' );
		if ( ! empty( $terms_of_use ) && empty( $_POST['affwp_tos'] ) ) {
			$this->add_error( 'empty_tos', __( 'Please agree to our terms of use', 'affiliate-wp' ) );
		}

		if ( ! empty( $_POST['affwp_honeypot'] ) ) {
			$this->add_error( 'spam', __( 'Nice try honey bear, don\'t touch our honey', 'affiliate-wp' ) );
		}

		if ( affwp_is_affiliate() ) {
			$this->add_error( 'already_registered', __( 'You are already registered as an affiliate', 'affiliate-wp' ) );
		}

		do_action( 'affwp_process_register_form' );

		// only log the user in if there are no errors
		if ( empty( $this->errors ) ) {
			$this->register_user();


			$redirect = apply_filters( 'affwp_register_redirect', $data['affwp_redirect'] );

			if ( $redirect ) {
				wp_redirect( $redirect ); exit;
			}
			
		}

	}

	/**
	 * Register Form Required Fields
	 *
	 * @access      public
	 * @since       1.1.4
	 * @return      array
	 */
	public function required_fields() {
		$required_fields = array(
			'affwp_user_name' 	=> array(
				'error_id'      => 'empty_name',
				'error_message' => __( 'Please enter your name', 'affiliate-wp' )
			),
			'affwp_user_login' 	=> array(
				'error_id'      => 'empty_username',
				'error_message' => __( 'Invalid username', 'affiliate-wp' )
			),
			'affwp_user_url' 	=> array(
				'error_id'      => 'invalid_url',
				'error_message' => __( 'Please enter a website URL', 'affiliate-wp' )
			),
			'affwp_user_pass' 	=> array(
				'error_id'      => 'empty_password',
				'error_message' => __( 'Please enter a password', 'affiliate-wp' )
			)
		);

		return apply_filters( 'affwp_register_required_fields', $required_fields );
	}

	/**
	 * Register the affiliate / user
	 *
	 * @since 1.0
	 */
	private function register_user() {

		$status = affiliate_wp()->settings->get( 'require_approval' ) ? 'pending' : 'active';

		if ( ! is_user_logged_in() ) {

			$name       = explode( ' ', sanitize_text_field( $_POST['affwp_user_name'] ) );
			$user_first = $name[0];
			$user_last  = isset( $name[1] ) ? $name[1] : '';

			$args = array(
				'user_login'    => sanitize_text_field( $_POST['affwp_user_login'] ),
				'user_email'    => sanitize_text_field( $_POST['affwp_user_email'] ),
				'user_pass'     => sanitize_text_field( $_POST['affwp_user_pass'] ),
				'display_name'  => $user_first . ' ' . $user_last,
				'first_name'    => $user_first,
				'last_name'     => $user_last
			);

			$user_id = wp_insert_user( $args );

		} else {

			$user_id = get_current_user_id();
			$user    = (array) get_userdata( $user_id );
			$args    = (array) $user['data'];

		}

		// promotion method
		$promotion_method = isset( $_POST['affwp_promotion_method'] ) ? sanitize_text_field( $_POST['affwp_promotion_method'] ) : '';
		
		if ( $promotion_method ) {
			update_user_meta( $user_id, 'affwp_promotion_method', $promotion_method );
		}

		// website URL
		$website_url = isset( $_POST['affwp_user_url'] ) ? sanitize_text_field( $_POST['affwp_user_url'] ) : '';

		if ( $website_url ) {
			wp_update_user( array( 'ID' => $user_id, 'user_url' => $website_url ) );
		}

		$affiliate_id = affwp_add_affiliate( array( 
			'status'        => $status,
			'user_id'       => $user_id,
			'payment_email' => ! empty( $_POST['affwp_payment_email'] ) ? sanitize_text_field( $_POST['affwp_payment_email'] ) : ''
		) );

		if ( ! is_user_logged_in() ) {
			$this->log_user_in( $user_id, sanitize_text_field( $_POST['affwp_user_login'] ) );
		}

		do_action( 'affwp_register_user', $affiliate_id, $status, $args );
	}

	/**
	 * Log the user in
	 *
	 * @since 1.0
	 */
	private function log_user_in( $user_id = 0, $user_login = '', $remember = false ) {

		$user = get_userdata( $user_id );
		if ( ! $user )
			return;

		wp_set_auth_cookie( $user_id, $remember );
		wp_set_current_user( $user_id, $user_login );
		do_action( 'wp_login', $user_login, $user );

	}

	/**
	 * Register a user as an affiliate during user registration
	 *
	 * @since 1.1
	 * @return bool
	 */
	public function auto_register_user_as_affiliate( $user_id = 0 ) {

		if ( ! affiliate_wp()->settings->get( 'auto_register' ) ) {
			return;
		}

		if ( did_action( 'process_registration' ) ) {
			return;
		}

		affwp_add_affiliate( array( 'user_id' => $user_id ) );

	}

	/**
	 * Register a submission error
	 *
	 * @since 1.0
	 */
	public function add_error( $error_id, $message = '' ) {
		$this->errors[ $error_id ] = $message;
	}

	/**
	 * Print errors
	 *
	 * @since 1.0
	 */
	public function print_errors() {

		if ( empty( $this->errors ) ) {
			return;
		}

		echo '<div class="affwp-errors">';

		foreach( $this->errors as $error_id => $error ) {

			echo '<p class="affwp-error">' . esc_html( $error ) . '</p>';

		}

		echo '</div>';

	}

	/**
	 * Get errors
	 *
	 * @since 1.1
	 * @return array
	 */
	public function get_errors() {

		if ( empty( $this->errors ) ) {
			return array();
		}

		return $this->errors;

	}


}