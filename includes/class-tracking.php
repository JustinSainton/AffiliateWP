<?php

class Affiliate_WP_Tracking {

	private $referral_var;

	private $expiration_time;

	/**
	 * Get things started
	 *
	 * @since 1.0
	 */
	public function __construct() {

		$this->set_expiration_time();
		$this->set_referral_var();

		/*
		 * Referrals are tracked via javascript by default
		 * This fails on sites that have jQuery errors, so a fallback method is available
		 * With the fallback, the template_redirect action is used
		 */

		if( ! $this->use_fallback_method() ) {

			add_action( 'wp_head', array( $this, 'header_scripts' ) );
			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
			add_action( 'wp_ajax_nopriv_affwp_track_visit', array( $this, 'track_visit' ) );
			add_action( 'wp_ajax_affwp_track_visit', array( $this, 'track_visit' ) );

		} else {

			add_action( 'template_redirect', array( $this, 'fallback_track_visit' ), -9999 );

		}

		add_action( 'wp_ajax_nopriv_affwp_track_conversion', array( $this, 'track_conversion' ) );
		add_action( 'wp_ajax_affwp_track_conversion', array( $this, 'track_conversion' ) );

	}

	/**
	 * Output header scripts
	 *
	 * @since 1.0
	 */
	public function header_scripts() {
?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {

			var cookie = $.cookie( 'affwp_ref' );
			var ref = affwp_get_query_vars()["<?php echo $this->get_referral_var(); ?>"];

			// If a referral var is present and a referral cookie is not already set
			if( ref && ! cookie ) {

				var cookie_value = ref;

				// Set the cookie and expire it after 24 hours
				$.cookie( 'affwp_ref', cookie_value, { expires: <?php echo $this->get_expiration_time(); ?>, path: '/' } );

				// Fire an ajax request to log the hit
				$.ajax({
					type: "POST",
					data: {
						action: 'affwp_track_visit',
						affiliate: ref,
						url: document.URL,
						referrer: document.referrer
					},
					url: affwp_scripts.ajaxurl,
					success: function (response) {
						$.cookie( 'affwp_ref_visit_id', response, { expires: <?php echo $this->get_expiration_time(); ?>, path: '/' } );
					}

				}).fail(function (response) {
					if ( window.console && window.console.log ) {
						console.log( response );
					}
				}).done(function (response) {
				});

			}

			function affwp_get_query_vars() {
				var vars = [], hash;
				var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
				for(var i = 0; i < hashes.length; i++) {
					hash = hashes[i].split('=');
					vars.push(hash[0]);
					vars[hash[0]] = hash[1];
				}
				return vars;
			}
		});
		</script>
<?php
	}

	/**
	 * Output the conversion tracking script
	 *
	 * @since 1.0
	 */
	public function conversion_script( $args = array() ) {

		$defaults = array(
			'amount'      => '',
			'description' => '',
			'context'     => '',
			'reference'   => ''
		);

		$args = wp_parse_args( $args, $defaults );

		if( empty( $args['amount'] ) && ! empty( $_REQUEST['amount'] ) ) {
			// Allow the amount to be passed via a query string or post request
			$args['amount'] = affwp_sanitize_amount( sanitize_text_field( urldecode( $_REQUEST['amount'] ) ) );
		}

		if( empty( $args['reference'] ) && ! empty( $_REQUEST['reference'] ) ) {
			// Allow the reference to be passed via a query string or post request
			$args['reference'] = sanitize_text_field( $_REQUEST['reference'] );
		}

		if( empty( $args['context'] ) && ! empty( $_REQUEST['context'] ) ) {
			$args['context'] = sanitize_text_field( $_REQUEST['context'] );
		}

		if( empty( $args['description'] ) && ! empty( $_REQUEST['description'] ) ) {
			$args['description'] = sanitize_text_field( $_REQUEST['description'] );
		}

		if( empty( $args['status'] ) && ! empty( $_REQUEST['status'] ) ) {
			$args['status'] = sanitize_text_field( $_REQUEST['status'] );
		}

		$md5 = md5( $args['amount'] . $args['description'] . $args['reference'] . $args['context'] . $args['status'] );

?>
		<script type="text/javascript">
		jQuery(document).ready(function($) {

			var ref   = $.cookie( 'affwp_ref' );
			var visit = $.cookie( 'affwp_ref_visit_id' );

			// If a referral var is present and a referral cookie is not already set
			if( ref && visit ) {

				// Fire an ajax request to log the hit
				$.ajax({
					type: "POST",
					data: {
						action      : 'affwp_track_conversion',
						affiliate   : ref,
						amount      : '<?php echo $args["amount"]; ?>',
						status      : '<?php echo $args["status"]; ?>',
						description : '<?php echo $args["description"]; ?>',
						context     : '<?php echo $args["context"]; ?>',
						reference   : '<?php echo $args["reference"]; ?>',
						md5         : '<?php echo $md5; ?>'
					},
					url: affwp_scripts.ajaxurl,
					success: function (response) {
						if ( window.console && window.console.log ) {
							console.log( response );
						}
					}

				}).fail(function (response) {
					if ( window.console && window.console.log ) {
						console.log( response );
					}
				}).done(function (response) {
				});

			}

		});
		</script>
<?php
	}

	/**
	 * Load JS files
	 *
	 * @since 1.0
	 */
	public function load_scripts() {
		wp_enqueue_script( 'jquery-cookie', AFFILIATEWP_PLUGIN_URL . 'assets/js/jquery.cookie.js', array( 'jquery' ), '1.4.0' );
		wp_localize_script( 'jquery-cookie', 'affwp_scripts', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}

	/**
	 * Record referral visit via ajax
	 *
	 * @since 1.0
	 */
	public function track_visit() {

		$affiliate_id = absint( $_POST['affiliate'] );

		if( $this->is_valid_affiliate( $affiliate_id ) ) {

			// Store the visit in the DB
			$visit_id = affiliate_wp()->visits->add( array(
				'affiliate_id' => $affiliate_id,
				'ip'           => $this->get_ip(),
				'url'          => sanitize_text_field( $_POST['url'] ),
				'referrer'     => sanitize_text_field( $_POST['referrer'] )
			) );

			echo $visit_id; exit;

		} else {

			die( '-2' );

		}

	}

	/**
	 * Record referral conversion via ajax
	 *
	 * This is called anytime a referred visitor lands on a success page, defined by the [affiliate_conversion_script] short code
	 *
	 * @since 1.0
	 */
	public function track_conversion() {

		$affiliate_id = absint( $_POST['affiliate'] );

		if( $this->is_valid_affiliate( $affiliate_id ) ) {

			$md5 = md5( $_POST['amount'] . $_POST['description'] . $_POST['reference'] . $_POST['context'] . $_POST['status'] );

			if( $md5 !== $_POST['md5'] ) {
				die( '-3' ); // The args were modified
			}

			if( affiliate_wp()->referrals->get_by( 'visit_id', $this->get_visit_id() ) ) {
				die( '-4' ); // This visit has already generated a referral
			}

			$status = ! empty( $_POST['status'] ) ? $_POST['status'] : 'unpaid';

			// Store the visit in the DB
			$referal_id = affiliate_wp()->referrals->add( array(
				'affiliate_id' => $affiliate_id,
				'amount'       => affwp_calc_referral_amount( sanitize_text_field( urldecode( $_POST['amount'] ) ), $affiliate_id ),
				'status'       => $status,
				'description'  => sanitize_text_field( $_POST['description'] ),
				'context'      => sanitize_text_field( $_POST['context'] ),
				'reference'    => sanitize_text_field( $_POST['reference'] ),
				'visit_id'     => $this->get_visit_id()
			) );

			affiliate_wp()->visits->update( $this->get_visit_id(), array( 'referral_id' => $referal_id ) );

			echo $referal_id; exit;

		} else {

			die( '-2' );

		}

	}

	/**
	 * Record referral visit via template_redirect
	 *
	 * @since 1.0
	 */
	public function fallback_track_visit() {

		$ref = ! empty( $_GET[ $this->get_referral_var() ] ) ? absint( $_GET[ $this->get_referral_var() ] ) : false;

		if( empty( $ref ) ) {
			return;
		}

		if( $this->is_valid_affiliate( $ref ) && ! $this->get_visit_id() ) {

			$this->set_affiliate_id( $ref );

			// Store the visit in the DB
			$visit_id = affiliate_wp()->visits->add( array(
				'affiliate_id' => $ref,
				'ip'           => $this->get_ip(),
				'url'          => $this->get_current_page_url(),
				'referrer'     => ! empty( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : ''
			) );

			$this->set_visit_id( $visit_id );

		}

	}

	/**
	 * Get the referral variable
	 *
	 * @since 1.0
	 */
	public function get_referral_var() {
		return $this->referral_var;
	}

	/**
	 * Set the referral variable
	 *
	 * @since 1.0
	 */
	public function set_referral_var() {
		$var = affiliate_wp()->settings->get( 'referral_var', 'ref' );
		$this->referral_var = apply_filters( 'affwp_referral_var', $var );
	}

	/**
	 * Set the cookie expiration time
	 *
	 * @since 1.0
	 */
	public function set_expiration_time() {
		// Default time is 1 day
		$days = affiliate_wp()->settings->get( 'cookie_exp', 1 );
		$this->expiration_time = apply_filters( 'affwp_cookie_expiration_time', $days );
	}

	/**
	 * Get the cookie expiration time in days
	 *
	 * @since 1.0
	 */
	public function get_expiration_time() {
		return $this->expiration_time;
	}

	/**
	 * Determine if current visit was referred
	 *
	 * @since 1.0
	 */
	public function was_referred() {
		return isset( $_COOKIE['affwp_ref'] ) && $this->is_valid_affiliate( $_COOKIE['affwp_ref'] );
	}

	/**
	 * Get the visit ID
	 *
	 * @since 1.0
	 */
	public function get_visit_id() {
		return ! empty( $_COOKIE['affwp_ref_visit_id'] ) ? absint( $_COOKIE['affwp_ref_visit_id'] ) : false;
	}

	/**
	 * Set the visit ID
	 *
	 * @since 1.0
	 */
	public function set_visit_id( $visit_id = 0 ) {
		setcookie( 'affwp_ref_visit_id', $visit_id, strtotime( '+' . $this->get_expiration_time() . ' days' ), COOKIEPATH, COOKIE_DOMAIN );
	}

	/**
	 * Get the referring affiliate ID
	 *
	 * @since 1.0
	 */
	public function get_affiliate_id() {
		return ! empty( $_COOKIE['affwp_ref'] ) ? absint( $_COOKIE['affwp_ref'] ) : false;
	}

	/**
	 * Set the referring affiliate ID
	 *
	 * @since 1.0
	 */
	public function set_affiliate_id( $affiliate_id = 0 ) {
		setcookie( 'affwp_ref', $affiliate_id, strtotime( '+' . $this->get_expiration_time() . ' days' ), COOKIEPATH, COOKIE_DOMAIN );
	}

	/**
	 * Check if it is a valid affiliate
	 *
	 * @since 1.0
	 */
	public function is_valid_affiliate( $affiliate_id = 0 ) {

		if( empty( $affiliate_id ) ) {
			$affiliate_id = $this->get_affiliate_id();
		}

		$is_self = is_user_logged_in() && get_current_user_id() == affiliate_wp()->affiliates->get_column( 'user_id', $affiliate_id );

		$active = 'active' == affwp_get_affiliate_status( $affiliate_id );

		$valid  = affiliate_wp()->affiliates->affiliate_exists( $affiliate_id );

		return ! empty( $valid ) && ! $is_self && $active;
	}

	/**
	 * Get the visitor's IP address
	 *
	 * @since 1.0
	 */
	public function get_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return apply_filters( 'affwp_get_ip', $ip );
	}

	/**
	 * Get the current page URL
	 *
	 * @since 1.0
	 * @global $post
	 * @return string $page_url Current page URL
	 */
	function get_current_page_url() {
		global $post;

		if ( is_front_page() ) {

			$page_url = home_url();

		} else {

			$page_url = 'http';

			if ( isset( $_SERVER["HTTPS"] ) && $_SERVER["HTTPS"] == "on" ) {
				$page_url .= "s";
			}

			$page_url .= "://";

			if ( $_SERVER["SERVER_PORT"] != "80" ) {
				$page_url .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
			} else {
				$page_url .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
			}

		}

		return apply_filters( 'affwp_get_current_page_url', $page_url );
	}

	/**
	 * Determine if we need to use the fallback tracking method
	 *
	 * @since 1.0
	 */
	public function use_fallback_method() {

		$use_fallback = affiliate_wp()->settings->get( 'tracking_fallback', false );

		return apply_filters( 'affwp_use_fallback_tracking_method', $use_fallback );
	}

}