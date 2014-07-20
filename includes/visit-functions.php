<?php

function affwp_count_visits( $affiliate_id = 0, $date = array() ) {

	$args = array(
		'affiliate_id' => $affiliate_id,
	);

	if( ! empty( $date ) ) {
		$args['date'] = $date;
	}

	return affiliate_wp()->visits->count( $args );

}

/**
 * Deletes a visit record
 *
 * @since 1.2
 * @return bool
 */
function affwp_delete_visit( $visit ) {

	if( is_object( $visit ) && isset( $visit->visit_id ) ) {
		$visit_id = $visit->visit_id;
	} elseif( is_numeric( $visit ) ) {
		$visit_id = absint( $visit );
	} else {
		return false;
	}

	// Decrease the visit count
	affwp_decrease_affiliate_visit_count( $visit_id );

	if( affiliate_wp()->visits->delete( $visit_id ) ) {

		do_action( 'affwp_delete_visit', $visit_id );

		return true;

	}

	return false;
}