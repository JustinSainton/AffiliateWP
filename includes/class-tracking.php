<?php

class Affiliate_WP_Tracking {

	private $referral_var;

	private $expiration_time;

	public function __construct() {

		$this->set_expiration_time();
		$this->set_referral_var();

		add_action( 'wp_head', array( $this, 'header_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'load_scripts' ) );
		add_action( 'wp_ajax_nopriv_affwp_track_visit', array( $this, 'track_visit' ) );
		add_action( 'wp_ajax_affwp_track_visit', array( $this, 'track_visit' ) );

	}

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
						url: document.URL
					},
					url: affwp_scripts.ajaxurl,
					success: function (response) {
						console.log( 'success' );
						console.log( response )
						$.cookie( 'affwp_ref_visit_id', response, { expires: <?php echo $this->get_expiration_time(); ?>, path: '/' } );
					}

				}).fail(function (response) {
					console.log( 'failed' );
					console.log( response );
				}).done(function (response) {
					console.log( 'done' );
					console.log( response );
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

	public function load_scripts() {
		wp_enqueue_script( 'jquery-cookie', AFFILIATEWP_PLUGIN_URL . 'assets/js/jquery.cookie.js', array( 'jquery' ), '1.4.0' );
		wp_localize_script( 'jquery-cookie', 'affwp_scripts', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
	}

	public function track_visit() {

		$affiliate_id = absint( $_POST['affiliate'] );

		if( $this->is_valid_affiliate( $affiliate_id ) ) {

			// Store the visit in the DB
			$visit_id = affiliate_wp()->visits->add( array(
				'affiliate_id' => $affiliate_id,
				'ip'           => affiliate_wp()->get_ip(),
				'url'          => sanitize_text_field( $_POST['url'] )
			) );

			echo $visit_id; exit;

		} else {

			die( '-2' );

		}

	}

	public function get_referral_var() {
		return $this->referral_var;
	}

	public function set_referral_var() {
		$this->referral_var = apply_filters( 'affwp_referral_var', 'ref' );
	}

	public function set_expiration_time() {
		// Default time is 1 day
		$this->expiration_time = apply_filters( 'affwp_cookie_expiration_time', 1 );
	}

	public function get_expiration_time() {
		return $this->expiration_time;
	}

	public function was_referred() {
		return isset( $_COOKIE['affwp_ref'] );
	}

	public function get_visit_id() {
		return absint( $_COOKIE['affwp_ref_visit_id'] );
	}

	public function get_affiliate_id() {
		return absint( $_COOKIE['affwp_ref'] );
	}

	public function is_valid_affiliate( $affiliate_id = 0 ) {

		if( empty( $affiliate_id ) ) {
			$affiliate_id = $this->get_affiliate_id();
		}

		if( is_user_logged_in() ) {

			$is_self = get_current_user_id() == affiliate_wp()->affiliates->get_column( 'user_id', $affiliate_id );

		}

		$valid = affiliate_wp()->affiliates->get_column( 'affiliate_id', $affiliate_id );

		return ! empty( $valid ) && ! $is_self;
	}

}