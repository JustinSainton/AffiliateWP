<?php

class Affiliate_WP_Admin_Menu {
	

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'register_menus' ) );
	}

	public function register_menus() {
		// TODO: Add custom capability
		add_menu_page( __( 'Affiliates', 'affiliate-wp' ), __( 'Affiliates', 'affiliate-wp' ), 'manage_options', 'affiliate-wp', 'affwp_affiliates_admin' );
		add_submenu_page( 'affiliate-wp', __( 'Referrals', 'affiliate-wp' ), __( 'Referrals', 'affiliate-wp' ), 'manage_options', 'affiliate-wp-referrals', 'affwp_referrals_admin' );
		add_submenu_page( 'affiliate-wp', __( 'Visits', 'affiliate-wp' ), __( 'Visits', 'affiliate-wp' ), 'manage_options', 'affiliate-wp-visits', 'affwp_visits_admin' );
		add_submenu_page( 'affiliate-wp', __( 'Settings', 'affiliate-wp' ), __( 'Settings', 'affiliate-wp' ), 'manage_options', 'affiliate-wp-settings', '__return_null' );
		add_submenu_page( 'affiliate-wp', __( 'Reports', 'affiliate-wp' ), __( 'Reports', 'affiliate-wp' ), 'manage_options', 'affiliate-wp-reports', '__return_null' );

	}

}
$affiliatewp_menu = new Affiliate_WP_Admin_Menu;