<?php

/**
 *  Prevents OptimizeMember from intefering with our ajax user search
 *  
 *  @since 1.6.2
 *  @return void
 */
function affwp_optimize_member_user_query( $search_term = '' ) {

	remove_action( 'pre_user_query', 'c_ws_plugin__optimizemember_users_list::users_list_query', 10 );

}
add_action( 'affwp_pre_search_users', 'affwp_optimize_member_user_query' );