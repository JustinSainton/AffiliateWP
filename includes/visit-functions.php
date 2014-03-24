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