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
 * Get a list of available email templates
 *
 * @since 1.6
 * @return array
 */
function affwp_get_email_templates() {
	return affiliate_wp()->emails->get_templates();
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
	$email_tags = affiliate_wp()->emails->get_tags();

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
 * Email template tag: name
 * The affiliate's name
 *
 * @param int $affiliate_id
 * @return string name
 */
function affwp_email_tag_name( $affiliate_id = 0 ) {
	return affiliate_wp()->affiliates->get_affiliate_name( $affiliate_id );
}


/**
 * Email template tag: username
 * The affiliate's username on the site
 *
 * @param int $affiliate_id
 * @return string username
 */
function affwp_email_tag_user_name( $affiliate_id = 0 ) {
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
function affwp_email_tag_user_email( $affiliate_id = 0 ) {
	return affwp_get_affiliate_email( $affiliate_id );
}


/**
 * Email template tag: website
 * The affiliate's website
 *
 * @param int $affiliate_id
 * @return string website
 */
function affwp_email_tag_website( $affiliate_id = 0 ) {
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
function affwp_email_tag_promo_method( $affiliate_id = 0 ) {
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
function affwp_email_tag_amount( $affiliate_id = 0, $referral ) {
	return html_entity_decode( affwp_currency_filter( $referral->amount ), ENT_COMPAT, 'UTF-8' );
}


/**
 * Email template tag: sitename
 * Your site name
 *
 * @return string sitename
 */
function affwp_email_tag_site_name() {
	return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
}

/**
 * Email template tag: referral URL
 * Affiliate's referral URL
 *
 * @return string referral_url
 */
function affwp_email_tag_referral_url( $affiliate_id = 0 ) {
	return affwp_get_affiliate_referral_url( array( 'affiliate_id' => $affiliate_id ) );
}

/**
 * Email template tag: affiliate ID
 * Affiliate's ID
 *
 * @return int affiliate ID
 */
function affwp_email_tag_affiliate_id( $affiliate_id = 0 ) {
	return $affiliate_id;
}

