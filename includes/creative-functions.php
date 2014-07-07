<?php

/**
 * Retrieves the creative object
 *
 * @since 1.1.4
 * @return object
 */
function affwp_get_creative( $creative ) {

	if ( is_object( $creative ) && isset( $creative->creative_id ) ) {
		$creative_id = $creative->affiliate_id;
	} elseif( is_numeric( $creative ) ) {
		$creative_id = absint( $creative );
	} else {
		return false;
	}

	return affiliate_wp()->creatives->get( $creative_id );
}

/**
 * Adds a new creative to the database
 *
 * @since 1.1.4
 * @return bool
 */
function affwp_add_creative( $data = array() ) {

	$args = array(
		'name'   => ! empty( $data['name'] ) ? sanitize_text_field( $data['name'] ) : __( 'Creative', 'affiliate-wp' ),
		'url'    => ! empty( $data['url'] ) ? esc_url( $data['url'] ) : get_site_url(),
		'text'   => ! empty( $data['text'] ) ? sanitize_text_field( $data['text'] ) : get_bloginfo( 'name' ),
		'image'  => ! empty( $data['image'] ) ? esc_url( $data['image'] ) : '',
		'status' => ! empty( $data['status'] ) ? sanitize_text_field( $data['status'] ) : '',	
	);

	if ( affiliate_wp()->creatives->add( $args ) ) {

		if ( ! empty( $_POST['affwp_action'] ) && is_admin() ) {
			wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-creatives&affwp_notice=creative_added' ) ); exit;
		}

		return true;
	}

	return false;

}

/**
 * Updates a creative
 *
 * @since 1.1.4
 * @return bool
 */
function affwp_update_creative( $data = array() ) {

	if ( empty( $data['creative_id'] ) ) {
		return false;
	}

	$args         = array();
	$creative_id  = absint( $data['creative_id'] );

	$args['name']   = ! empty( $data['name'] ) ? sanitize_text_field( $data['name'] ) : __( 'Creative', 'affiliate-wp' );
	$args['url']    = ! empty( $data['url'] ) ? sanitize_text_field( $data['url'] ) : get_site_url();
	$args['text']   = ! empty( $data['text'] ) ? sanitize_text_field( $data['text'] ) : get_bloginfo( 'name' );
	$args['image']  = ! empty( $data['image'] ) ? sanitize_text_field( $data['image'] ) : '';
	$args['status'] = ! empty( $data['status'] ) ? sanitize_text_field( $data['status'] ) : '';

	if ( affiliate_wp()->creatives->update( $creative_id, $args ) ) {

		if ( ! empty( $_POST['affwp_action'] ) ) {
			// This is an update call from the edit screen
			wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-creatives&action=edit_creative&affwp_notice=creative_updated&creative_id=' . $creative_id ) ); exit;
		}

		return true;

	}

	return false;

}