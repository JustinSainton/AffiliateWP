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
		'name'        => ! empty( $data['name'] ) ? sanitize_text_field( $data['name'] ) : __( 'Creative', 'affiliate-wp' ),
		'description' => ! empty( $data['description'] ) ? sanitize_text_field( $data['description'] ) : '',
		'url'         => ! empty( $data['url'] ) ? esc_url( $data['url'] ) : get_site_url(),
		'text'        => ! empty( $data['text'] ) ? sanitize_text_field( $data['text'] ) : get_bloginfo( 'name' ),
		'image'       => ! empty( $data['image'] ) ? esc_url( $data['image'] ) : '',
		'status'      => ! empty( $data['status'] ) ? sanitize_text_field( $data['status'] ) : '',	
	);

	if ( affiliate_wp()->creatives->add( $args ) ) {

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

	$args['name']         = ! empty( $data['name'] ) ? sanitize_text_field( $data['name'] ) : __( 'Creative', 'affiliate-wp' );
	$args['description']  = ! empty( $data['description'] ) ? sanitize_text_field( $data['description'] ) : '';
	$args['url']          = ! empty( $data['url'] ) ? sanitize_text_field( $data['url'] ) : get_site_url();
	$args['text']         = ! empty( $data['text'] ) ? sanitize_text_field( $data['text'] ) : get_bloginfo( 'name' );
	$args['image']        = ! empty( $data['image'] ) ? sanitize_text_field( $data['image'] ) : '';
	$args['status']       = ! empty( $data['status'] ) ? sanitize_text_field( $data['status'] ) : '';

	if ( affiliate_wp()->creatives->update( $creative_id, $args, '', 'creative' ) ) {
		return true;
	}

	return false;

}

/**
 * Deletes a creative
 *
 * @since 1.2
 * @param $delete_data bool
 * @return bool
 */
function affwp_delete_creative( $creative ) {

	if ( is_object( $creative ) && isset( $creative->creative_id ) ) {
		$creative_id = $creative->creative_id;
	} elseif ( is_numeric( $creative ) ) {
		$creative_id = absint( $creative );
	} else {
		return false;
	}

	return affiliate_wp()->creatives->delete( $creative_id );
}

/**
 * Sets the status for a creative
 *
 * @since 1.0
 * @return bool
 */
function affwp_set_creative_status( $creative, $status = '' ) {

	if ( is_object( $creative ) && isset( $creative->creative_id ) ) {
		$creative_id = $creative->creative_id;
	} elseif ( is_numeric( $creative ) ) {
		$creative_id = absint( $creative );
	} else {
		return false;
	}

	$old_status = affiliate_wp()->creatives->get_column( 'status', $creative_id );

	do_action( 'affwp_set_creative_status', $creative_id, $status, $old_status );

	if ( affiliate_wp()->creatives->update( $creative_id, array( 'status' => $status ), '', 'creative' ) ) {
		return true;
	}

}