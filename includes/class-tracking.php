<?php

class Affiliate_WP_Tracking {

	private $referral_var;

	private $expiration_time;

	public $referral;

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

			add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
			add_action( 'wp_ajax_nopriv_affwp_track_visit', array( $this, 'track_visit' ) );
			add_action( 'wp_ajax_affwp_track_visit', array( $this, 'track_visit' ) );
			add_action( 'wp_ajax_affwp_get_affiliate_id', array( $this, 'ajax_get_affiliate_id_from_login' ) );
			add_action( 'wp_ajax_nopriv_affwp_get_affiliate_id', array( $this, 'ajax_get_affiliate_id_from_login' ) );

		} else {

			add_action( 'template_redirect', array( $this, 'fallback_track_visit' ), -9999 );

		}

		add_action( 'wp_head', array( $this, 'header_scripts' ) );
		add_action( 'wp_ajax_nopriv_affwp_track_conversion', array( $this, 'track_conversion' ) );
		add_action( 'wp_ajax_affwp_track_conversion', array( $this, 'track_conversion' ) );
		add_action( 'wp_ajax_affwp_check_js', array( $this, 'check_js' ) );
		add_action( 'wp_ajax_nopriv_affwp_check_js', array( $this, 'check_js' ) );

	}

	/**
	 * Output header scripts
	 *
	 * @since 1.0
	 */
	public function header_scripts() {
		$referral_credit_last = affiliate_wp()->settings->get( 'referral_credit_last', 0 );
?>
		<script type="text/javascript">
		var AFFWP = AFFWP || {};
		AFFWP.referral_var = '<?php echo $this->get_referral_var(); ?>';
		AFFWP.expiration = <?php echo $this->get_expiration_time(); ?>;
<?php if( 1 !== (int) get_option( 'affwp_js_works' ) )  : ?>
		jQuery(document).ready(function($) {
			// Check if JS is working properly. If it is, we update an update in the DB
			$.ajax({
				type: "POST",
				data: {
					action: 'affwp_check_js'
				},
				url: '<?php echo admin_url( "admin-ajax.php" ); ?>'
			});
		});
<?php endif; ?>
		AFFWP.referral_credit_last = <?php echo absint( $referral_credit_last ); ?>;
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
			'campaign'    => '',
			'reference'   => ''
		);

		$args = wp_parse_args( $args, $defaults );

		if( empty( $args['amount'] ) && ! empty( $_REQUEST['amount'] ) && 0 !== $args['amount'] ) {
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

		if( empty( $args['campaign'] ) && ! empty( $_REQUEST['campaign'] ) ) {
			$args['campaign'] = sanitize_text_field( $_REQUEST['campaign'] );
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
						campaign    : '<?php echo $args["campaign"]; ?>',
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

		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'jquery-cookie', AFFILIATEWP_PLUGIN_URL . 'assets/js/jquery.cookie' . $suffix . '.js', array( 'jquery' ), '1.4.0' );
		wp_enqueue_script( 'affwp-tracking', AFFILIATEWP_PLUGIN_URL . 'assets/js/tracking' . $suffix . '.js', array( 'jquery-cookie' ), AFFILIATEWP_VERSION );
		wp_localize_script( 'jquery-cookie', 'affwp_scripts', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}


	/**
	 * Record referral visit via ajax
	 *
	 * @since 1.0
	 */
	public function track_visit() {

		$affiliate_id = isset( $_POST['affiliate'] ) ? absint( $_POST['affiliate'] ) : '';

		if ( ! empty( $affiliate_id ) && $this->is_valid_affiliate( $affiliate_id ) ) {

			// Store the visit in the DB
			$visit_id = affiliate_wp()->visits->add( array(
				'affiliate_id' => $affiliate_id,
				'ip'           => $this->get_ip(),
				'url'          => sanitize_text_field( $_POST['url'] ),
				'campaign'     => ! empty( $_POST['campaign'] ) ? sanitize_text_field( $_POST['campaign'] ) : '',
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
	 * This is called anytime a referred visitor lands on a success page, defined by the [affiliate_conversion_script] shortcode
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
			$amount = sanitize_text_field( urldecode( $_POST['amount'] ) );

			if( 0 == $amount && affiliate_wp()->settings->get( 'ignore_zero_referrals' ) ) {
				die( '-5' ); // Ignore a zero amount referral
			}

			$amount = $amount > 0 ? affwp_calc_referral_amount( $amount, $affiliate_id ) : 0;

			// Store the visit in the DB
			$referral_id = affiliate_wp()->referrals->add( array(
				'affiliate_id' => $affiliate_id,
				'amount'       => $amount,
				'status'       => 'pending',
				'description'  => sanitize_text_field( $_POST['description'] ),
				'context'      => sanitize_text_field( $_POST['context'] ),
				'campaign'     => sanitize_text_field( $_POST['campaign'] ),
				'reference'    => sanitize_text_field( $_POST['reference'] ),
				'visit_id'     => $this->get_visit_id()
			) );

			affwp_set_referral_status( $referral_id, $status );

			affiliate_wp()->visits->update( $this->get_visit_id(), array( 'referral_id' => $referral_id ), '', 'visit' );

			echo $referral_id; exit;

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

		$affiliate_id = $this->referral;

		if( empty( $affiliate_id ) ) {

			$affiliate_id = $this->get_fallback_affiliate_id();

		}

		if( empty( $affiliate_id ) ) {
			return;
		}

		if( ! is_numeric( $affiliate_id ) ) {
			$affiliate_id = $this->get_affiliate_id_from_login( $affiliate_id );
		}

		$affiliate_id = absint( $affiliate_id );

		if( $this->is_valid_affiliate( $affiliate_id ) && ! $this->get_visit_id() ) {

			$this->set_affiliate_id( $affiliate_id );

			// Store the visit in the DB
			$visit_id = affiliate_wp()->visits->add( array(
				'affiliate_id' => $affiliate_id,
				'ip'           => $this->get_ip(),
				'url'          => $this->get_current_page_url(),
				'campaign'     => $this->get_campaign(),
				'referrer'     => ! empty( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : ''
			) );

			$this->set_visit_id( $visit_id );

		}

	}

	/**
	 * Get the affiliate ID when using fallback tracking method
	 *
	 * @since 1.7.11
	 */
	public function get_fallback_affiliate_id() {

		$affiliate_id = ! empty( $_GET[ $this->get_referral_var() ] ) ? $_GET[ $this->get_referral_var() ] : false;

		if( empty( $affiliate_id ) ) {

			$path = ! empty( $_SERVER['REQUEST_URI' ] ) ? $_SERVER['REQUEST_URI' ] : '';

			if( false !== strpos( $path, $this->get_referral_var() . '/' ) ) {
				$pieces = explode( '/', $path );
				$key    = array_search( $this->get_referral_var(), $pieces );
				if( $key ) {

					$key += 1;					
					$affiliate_id = isset( $pieces[ $key ] ) ? $pieces[ $key ] : false;

					// Look for affiliate ID by username
					if( intval( $affiliate_id ) < 1 || ! is_numeric( $affiliate_id ) ) {

						$user = get_user_by( 'login', sanitize_text_field( urldecode( $affiliate_id ) ) );

						if( $user ) {

							$affiliate_id = affwp_get_affiliate_id( $user->ID );

						} else {

							$affiliate_id = false;

						}

					}

				}

			}

		}

		return $affiliate_id;
	}

	/**
	 * Get the referral campaign
	 *
	 * @since 1.7
	 */
	public function get_campaign() {
		$campaign = isset( $_COOKIE['affwp_campaign'] ) ? sanitize_text_field( $_COOKIE['affwp_campaign'] ) : '';

		if( empty( $campaign ) ) {

			$campaign = isset( $_REQUEST['campaign'] ) ? sanitize_text_field( $_REQUEST['campaign'] ) : '';

		}
		return apply_filters( 'affwp_get_campaign', $campaign, $this );
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

		// Cannot permit cookies to go past 2038
		$max  = ( 2038 - date( 'Y' ) ) * 365; 
		$days = $days > $max ? $max : $days;

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
		$bool = isset( $_COOKIE['affwp_ref'] ) && $this->is_valid_affiliate( $_COOKIE['affwp_ref'] );
		return (bool) apply_filters( 'affwp_was_referred', $bool, $this );
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

		$affiliate_id = ! empty( $_COOKIE['affwp_ref'] ) ? $_COOKIE['affwp_ref'] : false;

		if( ! empty( $cookie ) ) {

			$affiliate_id = absint( $affiliate_id );

		}

		return apply_filters( 'affwp_tracking_get_affiliate_id', $affiliate_id );
	}

	/**
	 * Get the affiliate's ID from their user login
	 *
	 * @since 1.3
	 */
	public function get_affiliate_id_from_login( $login = '' ) {

		$affiliate_id = 0;

		if( ! empty( $login ) ) {

			$user = get_user_by( 'login', sanitize_text_field( urldecode( $login ) ) );

			if( $user ) {

				$affiliate_id = affwp_get_affiliate_id( $user->ID );

			}

		}

		return apply_filters( 'affwp_tracking_get_affiliate_id', $affiliate_id );

	}

	/**
	 * Get the affiliate's ID from their user login
	 *
	 * @since 1.3
	 */
	public function ajax_get_affiliate_id_from_login() {

		$success      = 0;
		$affiliate_id = 0;

		if( ! empty( $_POST['affiliate'] ) ) {

			$affiliate_id = $this->get_affiliate_id_from_login( $_POST['affiliate'] );

			if( ! empty( $affiliate_id ) ) {

				$success = 1;

			}

		}

		$return = array(
			'success'      => $success,
			'affiliate_id' => $affiliate_id
		);

		wp_send_json_success( $return );

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

		$ret       = false;
		$affiliate = affwp_get_affiliate( $affiliate_id );

		if( $affiliate ) {

			$is_self = is_user_logged_in() && get_current_user_id() == $affiliate->user_id;
			$active  = 'active' === $affiliate->status;
			$ret     = ! $is_self && $active;
		}

		return apply_filters( 'affwp_tracking_is_valid_affiliate', $ret, $affiliate_id );
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
		$js_works     = (int) get_option( 'affwp_js_works' );
		$use_fallback = $use_fallback || 1 !== $js_works;

		return apply_filters( 'affwp_use_fallback_tracking_method', $use_fallback );
	}

	/**
	 * Set whether JS works or not. This is called via ajax.
	 *
	 * @since 1.7
	 */
	public function check_js() {

		update_option( 'affwp_js_works', 1 );

		die( '1' );
	}

}