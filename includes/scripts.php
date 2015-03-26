<?php

/**
 *  Determines whether the current admin page is an AffiliateWP admin page.
 *  
 *  Only works after the `wp_loaded` hook, & most effective 
 *  starting on `admin_menu` hook.
 *  
 *  @since 1.0
 *  @return bool True if AffiliateWP admin page.
 */
function affwp_is_admin_page() {

	if ( ! is_admin() || ! did_action( 'wp_loaded' ) ) {
		$ret = false;
	}
	
	if( ! isset( $_GET['page'] ) ) {
		$ret = false;
	}

	$page  = isset( $_GET['page'] ) ? $_GET['page'] : '';
	$pages = array(
		'affiliate-wp',
		'affiliate-wp-affiliates',
		'affiliate-wp-referrals',
		'affiliate-wp-visits',
		'affiliate-wp-creatives',
		'affiliate-wp-reports',
		'affiliate-wp-tools',
		'affiliate-wp-settings',
		'affwp-getting-started',
		'affwp-what-is-new',
		'affwp-credits'
	);
		
	$ret = in_array( $page, $pages );
	
	return apply_filters( 'affwp_is_admin_page', $ret );
}

/**
 *  Load the admin scripts
 *  
 *  @since 1.0
 *  @return void
 */
function affwp_admin_scripts() {

	if( ! affwp_is_admin_page() ) {
		return;
	}

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	wp_enqueue_script( 'affwp-admin', AFFILIATEWP_PLUGIN_URL . 'assets/js/admin' . $suffix . '.js', array( 'jquery' ), AFFILIATEWP_VERSION );
	wp_localize_script( 'affwp-admin', 'affwp_vars', array(
		'post_id'       => isset( $post->ID ) ? $post->ID : null,
		'affwp_version' => AFFILIATEWP_VERSION,
		'currency_sign' => affwp_currency_filter(''),
		'currency_pos'  => affiliate_wp()->settings->get( 'currency_position', 'before' ),
		'confirm'       => __( 'Are you sure you want to generate the payout file? All included referrals will be marked as Paid.', 'affiliate-wp' ),
	));

	// only enqueue for creatives page
	if ( isset( $_GET['action'] ) && ( $_GET['action'] == 'add_creative' || $_GET['action'] == 'edit_creative' ) ) {
		wp_enqueue_media();
	}

	wp_enqueue_script( 'jquery-ui-datepicker' );
}
add_action( 'admin_enqueue_scripts', 'affwp_admin_scripts' );

/**
 *  Load the admin styles
 *  
 *  @since 1.0
 *  @return void
 */
function affwp_admin_styles() {

	$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Dashicons and our main admin CSS need to be on all pages for the menu icon	
	wp_enqueue_style( 'dashicons' );
	wp_enqueue_style( 'affwp-admin', AFFILIATEWP_PLUGIN_URL . 'assets/css/admin' . $suffix . '.css', AFFILIATEWP_VERSION );
	
	if( ! affwp_is_admin_page() ) {
		return;
	}

	// jQuery UI styles are loaded on our admin pages only 
	$ui_style = ( 'classic' == get_user_option( 'admin_color' ) ) ? 'classic' : 'fresh';
	wp_enqueue_style( 'jquery-ui-css', AFFILIATEWP_PLUGIN_URL . 'assets/css/jquery-ui-' . $ui_style . '.min.css' );
}
add_action( 'admin_enqueue_scripts', 'affwp_admin_styles' );

/**
 *  Load the frontend scripts and styles
 *  
 *  @since 1.0
 *  @return void
 */
function affwp_frontend_scripts_and_styles() {

	global $post;

	if ( ! is_object( $post ) ) {
		return;
	}

	if ( has_shortcode( $post->post_content, 'affiliate_area' ) || apply_filters( 'affwp_force_frontend_scripts', false ) ) {
		
		$suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

		wp_enqueue_script( 'affwp-frontend', AFFILIATEWP_PLUGIN_URL . 'assets/js/frontend' . $suffix . '.js', array( 'jquery' ), AFFILIATEWP_VERSION );
		wp_localize_script( 'affwp-frontend', 'affwp_vars', array(
			'affwp_version'         => AFFILIATEWP_VERSION,
			'permalinks'            => get_option( 'permalink_structure' ),
			'pretty_affiliate_urls' => affwp_is_pretty_referral_urls(),
			'currency_sign'         => affwp_currency_filter(''),
			'currency_pos'          => affiliate_wp()->settings->get( 'currency_position', 'before' ),
		));
		wp_enqueue_style( 'affwp-forms', AFFILIATEWP_PLUGIN_URL . 'assets/css/forms' . $suffix . '.css', AFFILIATEWP_VERSION );
		wp_enqueue_style( 'dashicons' );
	}

}
add_action( 'wp_enqueue_scripts', 'affwp_frontend_scripts_and_styles' );

/**
 *  Load the frontend creative styles for the [affiliate_creative] and [affiliate_creatives] shortcodes
 *  
 *  @since 1.1.4
 *  @return void
 */
function affwp_frontend_creative_styles() {
	global $post;

	if ( ! is_object( $post ) ) {
		return;
	}

	if ( has_shortcode( $post->post_content, 'affiliate_creative' ) || has_shortcode( $post->post_content, 'affiliate_creatives' ) || apply_filters( 'affwp_force_frontend_scripts', false ) ) { ?>
		<style>.affwp-creative{margin-bottom: 4em;}</style>
	<?php }
}
add_action( 'wp_head', 'affwp_frontend_creative_styles' );