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
function affwp_add_email_tag( $tag, $description, $func ) {
	Affiliate_WP()->emails->add_tag( $tag, $description, $func );
}


/**
 * Remove an email tag
 *
 * @since 1.6
 * @param string $tag Email tag to remove
 * @return void
 */
function affwp_remove_email_tag( $tag ) {
	Affiliate_WP()->emails->remove_tag( $tag );
}


/**
 * Check if $tag is a registered email tag
 *
 * @since 1.6
 * @param string $tag Email tag that will be searched
 * @return bool True if exists, false otherwise
 */
function affwp_email_tag_exists( $tag ) {
	return Affiliate_WP()->emails->email_tag_exists( $tag );
}


/**
 * Get all email tags
 *
 * @since 1.6
 */
function affwp_get_email_tags() {
	return Affiliate_WP()->emails->get_tags();
}


/**
 * Get a formatted HTML list of all available tags
 *
 * @since 1.6
 * @return string $list HTML formated list
 */
function affwp_get_emails_tags_list() {
	// The list
	$list = '';

	// Get all tags
	$email_tags = affwpget_email_tags();

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
function affwp_do_email_tags( $content, $affiliate_id ) {
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
function affwp_load_email_tags() {
	do_action( 'affwp_add_email_tags' );
}
add_action( 'init', 'affwp_load_email_tags', -999 );


/**
 * Add default email template tags
 *
 * @since 1.6
 * @return void
 */
function affwp_setup_email_tags() {
	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => 'name',
			'description' => __( 'The full name of the affiliate', 'affiliate-wp' ),
			'function'    => 'affwp_email_tag_name'
		),
		array(
			'tag'         => 'username',
			'description' => __( 'The user name of the affiliate on the site', 'affiliate-wp' ),
			'function'    => 'affwp_email_tag_username'
		),
		array(
			'tag'         => 'user_email',
			'description' => __( 'The email address of the affiliate', 'affiliate-wp' ),
			'function'    => 'affwp_email_tag_user_email'
		),
		array(
			'tag'         => 'website',
			'description' => __( 'The website of the affiliate', 'affiliate-wp' ),
			'function'    => 'affwp_email_tag_website'
		),
		array(
			'tag'         => 'promo_method',
			'description' => __( 'The promo method used by the affiliate', 'affiliate-wp' ),
			'function'    => 'affwp_email_tag_promo_method'
		),
		array(
			'tag'         => 'login_url',
			'description' => __( 'The affiliate login URL to your website', 'affiliate-wp' ),
			'function'    => 'affwp_email_tag_login_url'
		),
		array(
			'tag'         => 'amount',
			'description' => __( 'The amount of a given referral', 'affiliate-wp' ),
			'function'    => 'affwp_email_tag_amount'
		),
		array(
			'tag'         => 'sitename',
			'description' => __( 'Your site name', 'affiliate-wp' ),
			'function'    => 'affwp_email_tag_sitename'
		)
	);

	$email_tags = apply_filters( 'affiliate_wp_email_tags', $email_tags );

	foreach( $email_tags as $email_tag ) {
		affwp_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'] );
	}
}
add_action( 'affwp_add_email_tags', 'affwp_setup_email_tags' );


/**
 * Email template tag: name
 * The affiliate's name
 *
 * @param int $affiliate_id
 * @return string name
 */
function affwp_email_tag_name( $affiliate_id ) {
	return affiliate_wp()->affiliates->get_affiliate_name( $args['affiliate_id'] );
}


/**
 * Email template tag: username
 * The affiliate's username on the site
 *
 * @param int $affiliate_id
 * @return string username
 */
function affwp_email_tag_username( $affiliate_id ) {
	$user_info = get_userdata( affwp_get_affiliate_user_id( $affiliate_id ) );

	return $user_info->user_login;
}


/**
 * Email template tag: user_email
 * The affiliate's email
 *
 * @param int $affiliate_id
 * @return string email
 */
function affwp_email_tag_user_email( $affiliate_id ) {
	return affwp_get_affiliate_email( $affiliate_id );
}


/**
 * Email template tag: website
 * The affiliate's website
 *
 * @param int $affiliate_id
 * @return string website
 */
function affwp_email_tag_website( $affiliate_id ) {
	$user_info = get_userdata( affwp_get_affiliate_user_id( $affiliate_id ) );

	return $user_info->user_url;
}


/**
 * Email template tag: promo_method
 * The affiliate promo method
 *
 * @param int $affiliate_id
 * @return string promo_method
 */
function affwp_email_tag_promo_method( $affiliate_id ) {
	return get_user_meta( affwp_get_affiliate_user_id( $affiliate_id ), 'affwp_promotion_method', true );
}


/**
 * Email template tag: login_url
 * The affiliate login URL
 *
 * @return string login_url
 */
function affwp_email_tag_login_url() {
	return esc_url( affiliate_wp()->login->get_login_url() );
}


/**
 * Email template tag: amount
 * The amount of an affiliate transaction
 *
 * @return string amount
 */
function affwp_email_tag_amount() {
	// How should we pass this given that $args isn't used by ANY other tag?
	return html_entity_decode( affwp_currency_filter( $args['amount'] ), ENT_COMPAT, 'UTF-8' );
}


/**
 * Email template tag: sitename
 * Your site name
 *
 * @return string sitename
 */
function affwp_email_tag_sitename() {
	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
}
