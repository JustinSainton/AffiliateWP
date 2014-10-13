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

// Process affiliate notification settings
add_action( 'affwp_update_profile_settings', 'affwp_update_profile_settings' );

