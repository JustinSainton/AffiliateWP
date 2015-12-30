<?php

// retrieves a list of users via live search
function affwp_search_users() {

	if ( empty( $_POST['search'] ) ) {
		die( '-1' );
	}

	if ( ! current_user_can( 'manage_affiliates' ) ) {
		die( '-1' );
	}

	$search_query = htmlentities2( trim( $_POST['search'] ) );

	do_action( 'affwp_pre_search_users', $search_query );

	$args = array();

	if ( isset( $_POST['status'] ) ) {
		$status = mb_strtolower( htmlentities2( trim( $_POST['status'] ) ) );

		switch ( $status ) {
			case 'none':
				$affiliates = affiliate_wp()->affiliates->get_affiliates(
					array(
						'number' => 9999,
					)
				);
				$args = array( 'exclude' => array_map( 'absint', wp_list_pluck( $affiliates, 'user_id' ) ) );
				break;
			case 'any':
				$affiliates = affiliate_wp()->affiliates->get_affiliates(
					array(
						'number' => 9999,
					)
				);
				$args = array( 'include' => array_map( 'absint', wp_list_pluck( $affiliates, 'user_id' ) ) );
				break;
			default:
				$affiliates = affiliate_wp()->affiliates->get_affiliates(
					array(
						'number' => 9999,
						'status' => $status,
					)
				);
				$args = array( 'include' => array_map( 'absint', wp_list_pluck( $affiliates, 'user_id' ) ) );
		}
	}

	//make sure we filter the search columns so they only include the columns we want to search
	//this filter was exposed by WordPress in WP 3.6.0
	add_filter(
		'user_search_columns',
		function( $search_columns, $search, WP_User_Query $WP_User_Query ) {
			return array( 'user_login', 'display_name', 'user_email' );
		},
		10,
		3
	);

	//add search string to args
	$args['search'] = '*' . mb_strtolower( htmlentities2( trim( $_POST['search'] ) ) ) . '*';
	
	//get users matching search
	$found_users = get_users( $args );

	if ( $found_users ) {
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
