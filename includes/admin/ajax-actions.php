<?php

// retrieves a list of users via live search
function affwp_search_users() {

	if( empty( $_POST['user_name'] ) ) {
		die( '-1' );
	}

	if( ! current_user_can( 'manage_affiliates' ) ) {
		die( '-1' );
	}

	$search_query = htmlentities2( trim( $_POST['user_name'] ) );

	do_action( 'affwp_pre_search_users', $search_query );

	$found_users = get_users( array(
			'number' => 9999,
			'search' => $search_query . '*'
		)
	);

	if( $found_users ) {
		$user_list = '<ul>';
		foreach( $found_users as $user ) {
			$user_list .= '<li><a href="#" data-id="' . esc_attr( $user->ID ) . '" data-login="' . esc_attr( $user->user_login ) . '">' . esc_html( $user->user_login ) . '</a></li>';
		}
		$user_list .= '</ul>';

		echo json_encode( array( 'results' => $user_list, 'id' => 'found' ) );

	} else {
		echo json_encode( array( 'results' => '<p>' . __( 'No users found', 'affiliate-wp' ) . '</p>', 'id' => 'fail' ) );
	}

	die();
}
add_action( 'wp_ajax_affwp_search_users', 'affwp_search_users' );