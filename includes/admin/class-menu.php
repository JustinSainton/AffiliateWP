<?php

class Affiliate_WP_Admin_Menu {
	

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
	}

	public function register_menus() {
		// TODO: Add custom capability
		add_menu_page( __( 'Affiliates', 'affiliate-wp' ), __( 'Affiliates', 'affiliate-wp' ), 'manage_options', 'affiliatewp', '__return_null' );
	}

}
$affiliatewp_menu = new Affiliate_WP_Admin_Menu;