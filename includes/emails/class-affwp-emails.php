<?php
/**
 * Emails
 *
 * This class handles all emails sent through AffiliateWP
 *
 * @package     AffiliateWP
 * @subpackage  Classes/Emails
 * @copyright   Copyright (c) 2015, Pippin Williamson
 * @license     http://opensource.org/license/gpl-2.1.php GNU Public License
 * @since       1.6
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Affiliate_WP_Emails class
 *
 * @since 1.6
 */
class Affiliate_WP_Emails {


	/**
	 * Holds the from address
	 *
	 * @since 1.6
	 */
	private $from_address;


	/**
	 * Holds the from name
	 *
	 * @since 1.6
	 */
	private $from_name;


	/**
	 * Holds the email content type
	 *
	 * @since 1.6
	 */
	private $content_type;


	/**
	 * Holds the email headers
	 *
	 * @since 1.6
	 */
	private $headers;


	/**
	 * Whether to send email in HTML
	 *
	 * @since 1.6
	 */
	private $html = true;


	/**
	 * The email template to use
	 *
	 * @since 1.6
	 */
	private $template;


	/**
	 * The header text for the email
	 *
	 * @since 1.6
	 */
	private $heading = '';


	/**
	 * Container for storing all tags
	 *
	 * @since 1.6
	 */
	private $tags;

	/**
	 * Affiliate ID
	 *
	 * @since 1.6
	 */
	private $affiliate_id;

	/**
	 * Get things going
	 *
	 * @since 1.6
	 * @return void
	 */
	public function __construct() {

		if( 'none' === $this->get_template() ) {
			$this->html = false;
		}

		add_action( 'affwp_email_send_before', array( $this, 'send_before' ) );
		add_action( 'affwp_email_send_after', array( $this, 'send_after' ) );
	}


	/**
	 * Set a property
	 *
	 * @since 1.6
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this->$key = $value;
	}


	/**
	 * Get the email from name
	 *
	 * @since 1.6
	 * @return string The email from name
	 */
	public function get_from_name() {
		global $affwp_options;

		if( ! $this->from_name ) {
			$this->from_name = affiliate_wp()->settings->get( 'from_name', get_bloginfo( 'name' ) );
		}

		return apply_filters( 'affwp_email_from_name', wp_specialchars_decode( $this->from_name ), $this );
	}


	/**
	 * Get the email from address
	 *
	 * @since 1.6
	 * @return string The email from address
	 */
	public function get_from_address() {
		if( ! $this->from_address ) {
			$this->from_address = affiliate_wp()->settings->get( 'from_email', get_option( 'admin_email' ) );
		}

		return apply_filters( 'affwp_email_from_address', $this->from_address, $this );
	}


	/**
	 * Get the email content type
	 *
	 * @since 1.6
	 * @return string The email content type
	 */
	public function get_content_type() {
		if( ! $this->content_type && $this->html ) {
			$this->content_type = apply_filters( 'affwp_email_default_content_type', 'text/html', $this );
		} elseif( ! $this->html ) {
			$this->content_type = 'text/plain';
		}

		return apply_filters( 'affwp_email_content_type', $this->content_type, $this );
	}


	/**
	 * Get the email headers
	 *
	 * @since 1.6
	 * @return string The email headers
	 */
	public function get_headers() {
		if( ! $this->headers ) {
			$this->headers  = "From: {$this->get_from_name()} <{$this->get_from_address()}>\r\n";
			$this->headers .= "Reply-To: {$this->get_from_address()}\r\n";
			$this->headers .= "Content-Type: {$this->get_content_type()}; charset=utf-8\r\n";
		}

		return apply_filters( 'affwp_email_headers', $this->headers, $this );
	}


	/**
	 * Retrieve email templates
	 *
	 * @since 1.6
	 * @return array The email templates
	 */
	public function get_templates() {
		$templates = array(
			'default' => __( 'Default Template', 'affiliate-wp' ),
			'none'	=> __( 'No template, plain text only', 'affiliate-wp' )
		);

		return apply_filters( 'affwp_email_templates', $templates );
	}


	/**
	 * Get the enabled email template
	 *
	 * @since 1.6
	 * @return string|null
	 */
	public function get_template() {
		if( ! $this->template ) {
			$this->template = affiliate_wp()->settings->get( 'email_template', 'default' );
		}

		return apply_filters( 'affwp_email_template', $this->template );
	}


	/**
	 * Get the header text for the email
	 *
	 * @since 1.6
	 * @return string The header text
	 */
	public function get_heading() {
		return apply_filters( 'affwp_email_heading', $this->heading );
	}


	/**
	 * Build the email
	 *
	 * @since 1.6
	 * @param string $message The email message
	 * @return string
	 */
	public function build_email( $message ) {
		if( false === $this->html ) {
			return apply_filters( 'affwp_email_message', wp_strip_all_tags( $message ), $this );
		}

		$message = $this->text_to_html( $message );
		
		ob_start();

		affiliate_wp()->templates->get_template_part( 'emails/header', $this->get_template(), true );

		/**
		 * Hooks into the email header
		 *
		 * @since 1.6
		 */
		do_action( 'affwp_email_header', $this );

		if( has_action( 'affwp_email_template_' . $this->get_template() ) ) {
			/**
			 * Hooks into the email template
			 *
			 * @since 1.6
			 */
			do_action( 'affwp_email_template_' . $this->get_template() );
		} else {
			affiliate_wp()->templates->get_template_part( 'emails/body', $this->get_template(), true );
		}

		/**
		 * Hooks into the email body
		 *
		 * @since 1.6
		 */
		do_action( 'affwp_email_body', $this );

		affiliate_wp()->templates->get_template_part( 'emails/footer', $this->get_template(), true );

		/**
		 * Hooks into the email footer
		 *
		 * @since 1.6
		 */
		do_action( 'affwp_email_footer', $this );

		$body	= ob_get_clean();

		return apply_filters( 'affwp_email_message', $message, $this );
	}


	/**
	 * Setup a notification
	 *
	 * @since 1.6
	 * @param string $type The type of notification to send
	 * @param array $args
	 */
	public function notification( $type = '', $args = array() ) {
		if( empty( $type ) ) {
			return false;
		}

		switch( $type ) {
			case 'registration':
				$email            = apply_filters( 'affwp_registration_admin_email', get_option( 'admin_email' ) );
				$user_info        = get_userdata( affwp_get_affiliate_user_id( $args['affiliate_id'] ) );
				$user_url         = $user_info->user_url;
				$promotion_method = get_user_meta( affwp_get_affiliate_user_id( $args['affiliate_id'] ), 'affwp_promotion_method', true );

				$subject  = affiliate_wp()->settings->get( 'registration_subject', __( 'New Affiliate Registration', 'affiliate-wp' ) );
				$message  = affiliate_wp()->settings->get( 'registration_email', false );

				if( ! $message ) {
					$message  = __( 'A new affiliate has registered on your site, ', 'affiliate-wp' ) . home_url() . "\n\n";
					$message .= sprintf( __( 'Name: %s', 'affiliate-wp' ), $args['name'] ) . "\n\n";

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

				$subject = apply_filters( 'affwp_registration_subject', $subject, $args );
				$message = apply_filters( 'affwp_registration_email', $message, $args );

				break;
			case 'application_accepted':
				$email   = affwp_get_affiliate_email( $args['affiliate_id'] );
				$subject = affiliate_wp()->settings->get( 'accepted_subject', __( 'Affiliate Application Accepted', 'affiliate-wp' ) );
				$message = affiliate_wp()->settings->get( 'accepted_email', false );

				if( ! $message ) {
					$message  = sprintf( __( 'Contratulations %s!', 'affiliate-wp' ), affiliate_wp()->affiliates->get_affiliate_name( $args['affiliate_id'] ) ) . "\n\n";
					$message .= sprintf( __( 'Your affiliate application on %s has been accepted!', 'affiliate-wp' ), home_url() ) . "\n\n";
					$message .= sprintf( __( 'Log into your affiliate area at %s', 'affiliate-wp' ), affiliate_wp()->login->get_login_url() ) . "\n\n";
				}
				
				$subject = apply_filters( 'affwp_application_accepted_subject', $subject, $args );
				$message = apply_filters( 'affwp_application_accepted_email', $message, $args );

				break;
			case 'new_referral':
				$email   = affwp_get_affiliate_email( $args['affiliate_id'] );
				$subject = affiliate_wp()->settings->get( 'referral_subject', __( 'Referral Awarded!', 'affiliate-wp' ) );
				$message = affiliate_wp()->settings->get( 'referral_email', false );
				$amount  = html_entity_decode( affwp_currency_filter( $args['amount'] ), ENT_COMPAT, 'UTF-8' );

				if( ! $message ) {
					$message  = sprintf( __( 'Congratulations %s!', 'affiliate-wp' ), affiliate_wp()->affiliates->get_affiliate_name( $args['affiliate_id'] ) ) . "\n\n";
					$message .= sprintf( __( 'You have been awarded a new referral of %s on %s!', 'affiliate-wp' ), $amount, home_url() ) . "\n\n";
					$message .= sprintf( __( 'log into your affiliate area to view your earnings or disable these notifications: %s', 'affiliate-wp' ), affiliate_wp()->login->get_login_url() ) . "\n\n";
				}

				$subject = apply_filters( 'affwp_new_referral_subject', $subject, $args );
				$message = apply_filters( 'affwp_new_referral_email', $message, $args );

				break;
		}

		$this->send( $email, $subject, $message );
	}


	/**
	 * Send the email
	 *
	 * @since 1.6
	 * @param string $to The To address
	 * @param string $subject The subject line of the email
	 * @param string $message The body of the email
	 * @param string|array $attachments Attachments to the email
	 */
	public function send( $to, $subject, $message, $attachments = '' ) {
		if( ! did_action( 'init' ) && ! did_action( 'admin_init' ) ) {
			_doing_it_wrong( __FUNCTION__, __( 'You cannot send emails with AffWP_Emails until init/admin_init has been reached', 'affiliate-wp' ), null );
			return false;
		}

		/**
		 * Hooks before email is sent
		 *
		 * @since 1.6
		 */
		do_action( 'affwp_email_send_before', $this );

		$message = $this->build_email( $message );

		$attachments = apply_filters( 'affwp_email_attachments', $attachments, $this );

		$sent = wp_mail( $to, $subject, $message, $this->get_headers(), $attachments );

		/**
		 * Hooks after the email is sent
		 *
		 * @since 1.6
		 */
		do_action( 'affwp_email_send_after', $this );

		return $sent;
	}


	/**
	 * Add filters/actions before the email is sent
	 *
	 * @since 1.6
	 */
	public function send_before() {
		add_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		add_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		add_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );
	}


	/**
	 * Remove filters/actions after the email is sent
	 *
	 * @since 1.6
	 */
	public function send_after() {
		remove_filter( 'wp_mail_from', array( $this, 'get_from_address' ) );
		remove_filter( 'wp_mail_from_name', array( $this, 'get_from_name' ) );
		remove_filter( 'wp_mail_content_type', array( $this, 'get_content_type' ) );

		// Reset heading to an empty string
		$this->heading = '';
	}


	/**
	 * Converts text formatted HTML. This is primarily for turning line breaks into <p> and <br/> tags.
	 *
	 * @since 1.6
	 */
	public function text_to_html( $message ) {
		if( 'text/html' === $this->content_type || true === $this->html ) {
			$message = wpautop( $message );
		}

		return $message;
	}

	/**
	 * Add an email tag
	 *
	 * @since 1.6
	 * @param string $tag Email tag to be replaced in email
	 * @param string $description The description fo the tag
	 * @param callable $func Hook to run when email tag is found
	 * @return void
	 */
	public function add_tag( $tag, $description, $func ) {
		if( is_callable( $func ) ) {
			$this->tags[$tag] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
		}
	}


	/**
	 * Remove an email tag
	 *
	 * @since 1.6
	 * @param string $tag Email tag to remove
	 * @return void
	 */
	public function remove_tag( $tag ) {
		unset( $this->tags[$tag] );
	}


	/**
	 * Check if $tag is a registered email tag
	 *
	 * @since 1.6
	 * @param string $tag Email tag that will be searched
	 * @return bool True if exists, false otherwise
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	}


	/**
	 * Returns a list of all email tags
	 *
	 * @since 1.6
	 */
	public function get_tags() {
		return $this->tags;
	}


	/**
	 * Search content for email tags and filter email tags through their hooks
	 *
	 * @since 1.6
	 * @param string $content Content to search for email tags
	 * @param int $affiliate_id The affiliate ID
	 * @return string $content Filtered content
	 */
	public function do_tags( $content, $affiliate_id ) {
		// Make sure there's at least one tag
		if( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		$this->affiliate_id = $affiliate_id;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->affiliate_id = null;

		return $new_content;
	}


	/**
	 * Do a specific tag. This function should not be used. Please use affiliate_wp_do_email_tags instead.
	 *
	 * @since 1.6
	 * @param $m Message
	 */
	public function do_tag( $m ) {
		// Get tag
		$tag = $m[1];

		// Return tag if not set
		if( ! $this->email_tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[$tag]['func'], $this->affiliate_id, $tag );
	}
}
