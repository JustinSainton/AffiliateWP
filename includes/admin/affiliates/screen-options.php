<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add per page screen option to the Affiliates list table
 *
 * @since 1.7
 */
function affwp_affiliates_screen_options() {

	$screen = get_current_screen();

	if ( $screen->id !== 'affiliates_page_affiliate-wp-affiliates' ) {
		return;
	}

	add_screen_option(
		'per_page',
		array(
			'label'   => __( 'Number of affiliates per page:', 'affiliate-wp' ),
			'option'  => 'affwp_edit_affiliates_per_page',
			'default' => 30,
		)
	);

	do_action( 'affwp_affiliates_screen_options', $screen );

}
add_action( 'load-affiliates_page_affiliate-wp-affiliates', 'affwp_affiliates_screen_options' );

/**
 * Per page screen option value for the Affiliates list table
 *
 * @since  1.7
 * @param  bool|int $status
 * @param  string   $option
 * @param  mixed    $value
 * @return mixed
 */
function affwp_affiliates_set_screen_option( $status, $option, $value ) {

	if ( 'affwp_edit_affiliates_per_page' === $option ) {
		return $value;
	}

	return $status;

}
add_filter( 'set-screen-option', 'affwp_affiliates_set_screen_option', 10, 3 );
