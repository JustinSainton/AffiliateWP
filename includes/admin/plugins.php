<?php
/**
 * Admin Plugins
 *
 * @package     AffiliateWP
 * @subpackage  Admin/Plugins
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.8
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Plugins row action links
 *
 * @author Tunbosun Ayinla
 * @since 1.0
 * @param array $links already defined action links
 * @param string $file plugin file path and name being processed
 * @return array $links
 */
function affwp_plugin_action_links( $links, $file ) {
	$settings_link = '<a href="' . admin_url( 'admin.php?page=affiliate-wp-settings' ) . '">' . esc_html__( 'General Settings', 'affiliate-wp' ) . '</a>';
	if ( $file == 'affiliate-wp/affiliate-wp.php' )
		array_unshift( $links, $settings_link );

	return $links;
}
add_filter( 'plugin_action_links', 'affwp_plugin_action_links', 10, 2 );


/**
 * Plugin row meta links
 *
 * @author Tunbosun Ayinla
 * @since 1.0
 * @param array $input already defined meta links
 * @param string $file plugin file path and name being processed
 * @return array $input
 */
function affwp_plugin_row_meta( $input, $file ) {
	if ( $file != 'affiliate-wp/affiliate-wp.php' )
		return $input;

	$links = array(
		'<a href="' . admin_url( 'index.php?page=affwp-getting-started' ) . '">' . esc_html__( 'Getting Started', 'affiliate-wp' ) . '</a>'
	);

	$input = array_merge( $input, $links );

	return $input;
}
add_filter( 'plugin_row_meta', 'affwp_plugin_row_meta', 10, 2 );