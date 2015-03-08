<?php
/**
 * Email functions
 *
 * @package AffiliateWP\Emails\Functions
 * @since 1.6
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


/**
 * Add an email tag
 *
 * @since 1.6
 * @param string $tag Email tag to be replaced in email
 * @param string $description The description of the tag
 * @param callable $func Hook to run when email tag is found
 * @return void
 */
function affiliate_wp_add_email_tag( $tag, $description, $func ) {
	Affiliate_WP()->emails->add_tag( $tag, $description, $func );
}


/**
 * Remove an email tag
 *
 * @since 1.6
 * @param string $tag Email tag to remove
 * @return void
 */
function affiliate_wp_remove_email_tag( $tag ) {
	Affiliate_WP()->emails->remove_tag( $tag );
}


/**
 * Check if $tag is a registered email tag
 *
 * @since 1.6
 * @param string $tag Email tag that will be searched
 * @return bool True if exists, false otherwise
 */
function affiliate_wp_email_tag_exists( $tag ) {
	return Affiliate_WP()->emails->email_tag_exists( $tag );
}


/**
 * Get all email tags
 *
 * @since 1.6
 */
function affiliate_wp_get_email_tags() {
	return Affiliate_WP()->emails->get_tags();
}


/**
 * Get a formatted HTML list of all available tags
 *
 * @since 1.6
 * @return string $list HTML formated list
 */
function affiliate_wp_get_emails_tags_list() {
	// The list
	$list = '';

	// Get all tags
	$email_tags = affiliate_wp_get_email_tags();

	// Check
	if( count( $email_tags ) > 0 ) {
		foreach( $email_tags as $email_tag ) {
			$list .= '{' . $email_tag['tag'] . '} - ' . $email_tag['description'] . '<br />';
		}
	}

	// Return the list
	return $list;
}


/**
 * Search content for email tags and filter them
 *
 * @since 1.6
 * @param string $content Content to search for email tags
 * @param int $affiliate_id The affiliate ID
 * @return string $content The filtered content
 */
function affiliate_wp_do_email_tags( $content, $affiliate_id ) {
	// Replace all tags
	$content = Affiliate_WP()->emails->do_tags( $content, $affiliate_id );

	return $content;
}


/**
 * Load email tags
 *
 * @since 1.6
 * @return void
 */
function affiliate_wp_load_email_tags() {
	do_action( 'affiliate_wp_add_email_tags' );
}
add_action( 'init', 'affiliate_wp_load_email_tags', -999 );


/**
 * Add default email template tags
 *
 * @since 1.6
 * @return void
 */
function affiliate_wp_setup_email_tags() {
	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => 'name',
			'description' => __( 'The first name of the affiliate', 'affiliate-wp' ),
			'function'    => 'affiliate_wp_email_tag_first_name'
		),
		array(
			'tag'         => 'fullname',
			'description' => __( 'The full name of the affiliate', 'affiliate-wp' ),
			'function'    => 'affiliate_wp_email_tag_full_name'
		),
		array(
			'tag'         => 'username',
			'description' => __( 'The user name of the affiliate on the site', 'affiliate-wp' ),
			'function'    => 'affiliate_wp_email_tag_username'
		),
		array(
			'tag'         => 'user_email',
			'description' => __( 'The email address of the affiliate', 'affiliate-wp' ),
			'function'    => 'affiliate_wp_email_tag_user_email'
		),
		array(
			'tag'         => 'website',
			'description' => __( 'The website of the affiliate', 'affiliate-wp' ),
			'function'    => 'affiliate_wp_email_tag_website'
		),
		array(
			'tag'         => 'promo_method',
			'description' => __( 'The promo method used by the affiliate', 'affiliate-wp' ),
			'function'    => 'affiliate_wp_email_tag_promo_method'
		),
		array(
			'tag'         => 'login_url',
			'description' => __( 'The affiliate login URL to your website', 'affiliate-wp' ),
			'function'    => 'affiliate_wp_email_tag_login_url'
		),
		array(
			'tag'         => 'amount',
			'description' => __( 'The amount of a given referral', 'affiliate-wp' ),
			'function'    => 'affiliate_wp_email_tag_amount'
		),
		array(
			'tag'         => 'sitename',
			'description' => __( 'Your site name', 'affiliate-wp' ),
			'function'    => 'affiliate_wp_email_tag_sitename'
		),
		array(
			'tag'         => 'ip_address',
			'description' => __( 'The IP address of the affiliate', 'affiliate-wp' ),
			'function'    => 'affiliate_wp_email_tag_ip_address'
		)
	);

	$email_tags = apply_filters( 'affiliate_wp_email_tags', $email_tags );

	foreach( $email_tags as $email_tag ) {
		affiliate_wp_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'] );
	}
}
add_action( 'affiliate_wp_add_email_tags', 'affiliate_wp_setup_email_tags' );
