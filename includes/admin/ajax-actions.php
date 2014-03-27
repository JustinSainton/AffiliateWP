<?php

// retrieves a list of users via live search
function affwp_search_users() {

	if( empty( $_POST['user_name'] ) ) {
		die( '-1' );
	}

	$search_query = trim( $_POST['user_name'] );

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
		echo json_encode( array( 'msg' => __( 'No users found', 'rcp' ), 'results' => 'none', 'id' => 'fail' ) );
	}

	die();
}
add_action( 'wp_ajax_affwp_search_users', 'affwp_search_users' );