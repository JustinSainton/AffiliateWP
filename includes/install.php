<?php

function affiliate_wp_install() {

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

	// Create affiliate caps
	$roles = new Affiliate_WP_Capabilities;
	$roles->add_caps();

	update_option( 'affwp_is_installed', '1' );
	// send to welcome page here

}
register_activation_hook( AFFILIATEWP_PLUGIN_FILE, 'affiliate_wp_install' );

function affiliate_wp_check_if_installed() {
	// this is mainly for network activated installs
	if( ! get_option( 'affwp_is_installed' ) ) {
		affiliate_wp_install();
	}
}
add_action( 'admin_init', 'affiliate_wp_check_if_installed' );