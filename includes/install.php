<?php

function affiliate_wp_install() {

	affiliate_wp()->referrals->create_table();
	affiliate_wp()->visits->create_table();

	// send to welcome page here

}
register_activation_hook( AFFILIATEWP_PLUGIN_FILE, 'affiliate_wp_install' );