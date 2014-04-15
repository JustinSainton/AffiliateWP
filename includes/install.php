<?php

function affiliate_wp_install() {

	// Create affiliate caps
	$roles = new Affiliate_WP_Capabilities;
	$roles->add_caps();

	affiliate_wp()->affiliates->create_table();
	affiliate_wp()->referrals->create_table();
	affiliate_wp()->visits->create_table();

	if ( ! get_option( 'affwp_is_installed' ) ) {
		$affiliate_area = wp_insert_post(
			array(
				'post_title'     => __( 'Affiliate Area', 'affiliate-wp' ),
				'post_content'   => '[affiliate_area]',
				'post_status'    => 'publish',
				'post_author'    => 1,
				'post_type'      => 'page',
				'comment_status' => 'closed'
			)
		);

		$options = affiliate_wp()->settings->get_all();
		$options['affiliates_page'] = $affiliate_area;
		update_option( 'affwp_settings', $options );

	}

	update_option( 'affwp_is_installed', '1' );
	
	// Bail if activating from network, or bulk
	if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
		return;
	}

	// Add the transient to redirect
	set_transient( '_affwp_activation_redirect', true, 30 );

}
register_activation_hook( AFFILIATEWP_PLUGIN_FILE, 'affiliate_wp_install' );

function affiliate_wp_check_if_installed() {

	global $wp_roles;

	if ( class_exists('WP_Roles') ) {
		if ( ! isset( $wp_roles ) ) {
			$wp_roles = new WP_Roles();
		}
	}

	// Check that our capabilities are present
	if( ! empty( $wp_roles->roles['administrator'] ) ) {

		$caps = $wp_roles->roles['administrator']['capabilities'];
		if( ! in_array( 'view_affiliate_reports', $caps ) ) {

			$wp_roles->add_cap( 'administrator', 'view_affiliate_reports' );
			$wp_roles->add_cap( 'administrator', 'export_affiliate_data' );
			$wp_roles->add_cap( 'administrator', 'manage_affiliate_options' );
			$wp_roles->add_cap( 'administrator', 'manage_affiliates' );
			$wp_roles->add_cap( 'administrator', 'manage_referrals' );
			$wp_roles->add_cap( 'administrator', 'manage_visits' );

		}

	}

	//echo '<pre>'; print_R( $wp_roles ); echo '</pre>'; exit;
	// this is mainly for network activated installs
	if( ! get_option( 'affwp_is_installed' ) ) {
		affiliate_wp_install();
	}
}
add_action( 'admin_init', 'affiliate_wp_check_if_installed' );