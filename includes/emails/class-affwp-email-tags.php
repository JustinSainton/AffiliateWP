<?php
/**
 * API for creating email template tags
 *
 * Email tags are wrapped in { }
 *
 * To replace tags in content, use: affiliate_wp_do_email_tags( $content, $affiliate_id );
 *
 * To add tags, use: affiliate_wp_add_email_tag( $tag, $description, $func ). Be sure to
 * wrap affiliate_wp_add_email_tag() in a function hooked to the 'affiliate_wp_email_tags' action
 *
 * @package AffiliateWP\Emails\Tags
 * @since 1.6
 */


// Exit if accessed directly
if( ! defined( 'ABSPATH' ) ) exit;


class Affiliate_WP_Email_Tags {


	/**
	 * Container for storing all tags
	 *
	 * @since 1.6
	 */
	private $tags;


	/**
	 * Affiliate ID
	 *
	 * @since 1.6
	 */
	private $affiliate_id;


	/**
	 * Add an email tag
	 *
	 * @since 1.6
	 * @param string $tag Email tag to be replaced in email
	 * @param string $description The description of the tag
	 * @param callable $func Hook to run when email tag is found
	 */
	public function add( $tag, $description, $func ) {
		if( is_callable( $func ) ) {
			$this->tags[$tag] = array(
				'tag'         => $tag,
				'description' => $description,
				'func'        => $func
			);
		}
	}


	/**
	 * Remove an email tag
	 *
	 * @since 1.6
	 * @param string $tag Email tag to remove hook from
	 */
	public function remove( $tag ) {
		unset( $this->tags[$tag] );
	}


	/**
	 * Check if $tag is a registered email tag
	 *
	 * @since 1.6
	 * @param string $tag Email tag that will be searched
	 */
	public function email_tag_exists( $tag ) {
		return array_key_exists( $tag, $this->tags );
	}


	/**
	 * Returns a list of all email tags
	 *
	 * @since 1.6
	 */
	public function get_tags() {
		return $this->tags;
	}


	/**
	 * Search content for email tags and filter email tags through their hooks
	 *
	 * @since 1.6
	 * @param string $content Content to search for email tags
	 * @param int $affiliate_id The affiliate ID
	 */
	public function do_tags( $content, $affiliate_id ) {
		// Make sure there's at least one tag
		if( empty( $this->tags ) || ! is_array( $this->tags ) ) {
			return $content;
		}

		$this->affiliate_id = $affiliate_id;

		$new_content = preg_replace_callback( "/{([A-z0-9\-\_]+)}/s", array( $this, 'do_tag' ), $content );

		$this->affiliate_id = null;

		return $new_content;
	}


	/**
	 * Do a specific tag. This function should not be used. Please use affiliate_wp_do_email_tags instead.
	 *
	 * @since 1.6
	 * @param $m Message
	 */
	public function do_tag( $m ) {
		// Get tag
		$tag = $m[1];

		// Return tag if not set
		if( ! $this->email_tag_exists( $tag ) ) {
			return $m[0];
		}

		return call_user_func( $this->tags[$tag]['func'], $this->affiliate_id, $tag );
	}
}


/**
 * Add an email tag
 *
 * @since 1.6
 * @param string $tag Email tag to be replaced in email
 * @param string $description The description of the tag
 * @param callable $func Hook to run when email tag is found
 */
function affiliate_wp_add_email_tag( $tag, $description, $func ) {
	Affiliate_WP()->email_tags->add( $tag, $description, $func );
}


/**
 * Remove an email tag
 *
 * @since 1.6
 * @param string $tag Email tag to remove
 */
function affiliate_wp_remove_email_tag( $tag ) {
	Affiliate_WP()->email_tags->remove( $tag );
}


/**
 * Check if $tag is a registered email tag
 *
 * @since 1.6
 * @param string $tag Email tag that will be searched
 */
function affiliate_wp_email_tag_exists( $tag ) {
	return Affiliate_WP()->email_tags->email_tag_exists( $tag );
}


/**
 * Get all email tags
 *
 * @since 1.6
 */
function affiliate_wp_get_email_tags() {
	return Affiliate_WP()->email_tags->get_tags();
}


/**
 * Get a formatted HTML list of all available tags
 *
 * @since 1.6
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
 */
function affiliate_wp_do_email_tags( $content, $affiliate_id ) {
	// Replace all tags
	$content = Affiliate_WP()->email_tags->do_tags( $content, $affiliate_id );

	return $content;
}


/**
 * Load email tags
 *
 * @since 1.6
 */
function affiliate_wp_load_email_tags() {
	do_action( 'affiliate_wp_add_email_tags' );
}
add_action( 'init', 'affiliate_wp_load_email_tags', -999 );


/**
 * Add default email template tags
 *
 * @since 1.6
 */
function affiliate_wp_setup_email_tags() {
	// Setup default tags array
	$email_tags = array(
		array(
			'tag'         => '',
			'description' => '',
			'function'    => ''
		)
	);

	$email_tags = apply_filters( 'affiliate_wp_email_tags', $email_tags );

	foreach( $email_tags as $email_tag ) {
		affiliate_wp_add_email_tag( $email_tag['tag'], $email_tag['description'], $email_tag['function'] );
	}
}
add_action( 'affiliate_wp_add_email_tags', 'affiliate_wp_setup_email_tags' );
