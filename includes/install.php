<?php

function affiliate_wp_install() {

	affiliate_wp()->affiliates->create_table();
	affiliate_wp()->referrals->create_table();
	affiliate_wp()->visits->create_table();

	// send to welcome page here

}
register_activation_hook( AFFILIATEWP_PLUGIN_FILE, 'affiliate_wp_install' );

function affiliate_wp_check_if_installed() {
	// this is mainly for network activated installs
	if( ! get_option( 'affwp_is_installed' ) ) {
	//	affiliate_wp_install();
	}
}
add_action( 'admin_init', 'affiliate_wp_check_if_installed' );