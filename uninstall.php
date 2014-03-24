<?php
/**
 * Uninstall Affiliate WP
 *
 * @package     Affiliate WP
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Load EDD file
include_once( 'affiliate-wp.php' );

global $wpdb, $wp_roles;

if( affiliate_wp()->settings->get( 'uninstall_on_delete' ) ) {

	// Remove the affiliate area page
	wp_delete_post( affiliate_wp()->settings->get( 'affiliates_page' ) );

	// Remove all plugin settings
	delete_option( 'affwp_settings' );

	// Remove all capabilities and roles
	affiliate_wp()->roles->remove_caps();
	affiliate_wp()->roles->remove_roles();

	// Remove all database tables
	$wpdb->query( "DROP TABLE $wpdb->affiliate_wp_affiliates" );
	$wpdb->query( "DROP TABLE $wpdb->affiliate_wp_referrals" );
	$wpdb->query( "DROP TABLE $wpdb->affiliate_wp_visits" );

}
