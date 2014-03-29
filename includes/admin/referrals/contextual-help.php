<?php
/**
 * Contextual Help
 *
 * @package     Affiliate WP
 * @subpackage  Admin/Referrals
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */

// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;
//print_r( get_current_screen() );
/**
 * Payments contextual help.
 *
 * @access      private
 * @since       1.4
 * @return      void
 */
function affwp_referrals_contextual_help() {

	$screen = get_current_screen();

	if ( $screen->id != 'affiliates_page_affiliate-wp-referrals' )
		return;

	$sidebar_text = '<p><strong>' . __( 'For more information:', 'affiliate-wp' ) . '</strong></p>';
	$sidebar_text .= '<p>' . sprintf( __( 'Visit the <a href="%s">documentation</a> on the Affiliate WP website.', 'affiliate-wp' ), esc_url( 'http://affiliatewp.com/documentation/' ) ) . '</p>';
	$sidebar_text .= '<p>' . sprintf( __( '<a href="%s">Post an issue</a> on <a href="%s">GitHub</a>.', 'affiliate-wp' ), esc_url( 'https://github.com/affiliatewp/AffiliateWP/issues' ), esc_url( 'https://github.com/affiliatewp/AffiliateWP' )  ) . '</p>';

	$screen->set_help_sidebar( $sidebar_text );

	$screen->add_help_tab( array(
		'id'	    => 'affwp-referrals-overview',
		'title'	    => __( 'Overview', 'affiliate-wp' ),
		'content'	=>
			'<p>' . __( "This screen provides access to your site's referral history.", 'affiliate-wp' ) . '</p>'
	) );

	$screen->add_help_tab( array(
		'id'	    => 'affwp-referrals-search',
		'title'	    => __( 'Searching Referrals', 'affiliate-wp' ),
		'content'	=>
			'<p>' . __( 'Referrals can be searched in several different ways:', 'affiliate-wp' ) . '</p>' .
			'<ul>
				<li>' . __( 'You can enter the referral\'s ID number', 'affiliate-wp' ) . '</li>
				<li>' . __( 'You can enter the referral reference prefixed by \'ref:\'', 'affiliate-wp' ) . '</li>
				<li>' . __( 'You can enter the referral context prefixed by \'context:\'', 'affiliate-wp' ) . '</li>
				<li>' . __( 'You can enter the affiliate\'s ID number prefixed by \'affiliate:\'', 'affiliate-wp' ) . '</li>
			</ul>'
	) );

	do_action( 'affwp_referrals_contextual_help', $screen );
}
add_action( 'load-affiliates_page_affiliate-wp-referrals', 'affwp_referrals_contextual_help' );
