<?php
/**
 * Uninstall AffiliateWP
 *
 * @package     AffiliateWP
 * @subpackage  Uninstall
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

// Load AffiliateWP file
include_once( 'affiliate-wp.php' );

global $wpdb, $wp_roles;

$affiliate_wp_settings = new Affiliate_WP_Settings;

if( $affiliate_wp_settings->get( 'uninstall_on_delete' ) ) {

	// Remove the affiliate area page
	wp_delete_post( $affiliate_wp_settings->get( 'affiliates_page' ) );

	// Remove all plugin settings
	delete_option( 'affwp_settings' );

	// Remove all capabilities and roles
	$caps = new Affiliate_WP_Capabilities;
	$caps->remove_caps();

	// Remove all database tables
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "affiliate_wp_affiliates" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "affiliate_wp_referrals" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "affiliate_wp_visits" );
	$wpdb->query( "DROP TABLE IF EXISTS " . $wpdb->prefix . "affiliate_wp_creatives" );

}
