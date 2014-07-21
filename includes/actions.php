<?php

/**
 * Hooks AffiliateWP actions, when present in the $_REQUEST superglobal. Every affwp_action
 * present in $_REQUEST is called using WordPress's do_action function. These
 * functions are called on init.
 *
 * @since 1.0
 * @return void
*/
function affwp_do_actions() {
	if ( isset( $_REQUEST['affwp_action'] ) ) {
		do_action( 'affwp_' . $_REQUEST['affwp_action'], $_REQUEST );
	}
}
add_action( 'init', 'affwp_do_actions' );

// Process an affiliate creation from the admin screen
add_action( 'affwp_add_affiliate', 'affwp_add_affiliate' );

// Process an affiliate update from the admin screen
add_action( 'affwp_update_affiliate', 'affwp_update_affiliate' );

// Process a referral creation from the admin screen
add_action( 'affwp_add_referral', 'affwp_add_referral' );

// Process affiliate notification settings
add_action( 'affwp_update_profile_settings', 'affwp_update_profile_settings' );

// Process a creative creation from the admin screen
add_action( 'affwp_add_creative', 'affwp_add_creative' );

// Process an creative update from the admin screen
add_action( 'affwp_update_creative', 'affwp_update_creative' );

// Process creative deletions
add_action( 'affwp_delete_creatives', 'affwp_process_creative_deletion' );