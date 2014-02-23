<?php

function affwp_get_affiliate( $affiliate ) {
	
	if( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	return affiliate_wp()->affiliates->get( $affiliate_id );
}

function affwp_get_affiliate_status( $affiliate ) {

	if( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	return affiliate_wp()->affiliates->get_column( 'status', $affiliate_id );
}

function affwp_set_affiliate_status( $affiliate, $status = '' ) {

	if( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	return affiliate_wp()->affiliates->update( $affiliate_id, array( 'status' => $status ) );
}

function affwp_delete_affiliate( $affiliate ) {

	if( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	// TODO: also delete all referrals and visits here

	return affiliate_wp()->affiliates->delete( $affiliate_id );
}


function affwp_get_affiliate_earnings( $affiliate ) {

	if( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	return affiliate_wp()->affiliates->get_column( 'earnings', $affiliate_id );
}

function affwp_get_affiliate_referral_count( $affiliate ) {

	if( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	return affiliate_wp()->affiliates->get_column( 'referrals', $affiliate_id );
}

function affwp_get_affiliate_visit_count( $affiliate ) {

	if( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	return absint( affiliate_wp()->affiliates->get_column( 'visits', $affiliate_id ) );
}

function affwp_affiliate_graph( $affiliate_id = 0 ) {

	// outputs a graph of the affiliate's earnings, referral, and visit stats

	$data = array();

	$data[ __( 'Earnings', 'affiliate-wp' ) ] = array(
		array( 1, 5 ),
		array( 3, 8 ),
		array( 10, 2 )
	);

	$data[ __( 'Referrals', 'affiliate-wp' ) ] = array(
		array( 4, 12 ),
		array( 2, 8 ),
		array( 12, 8 )
	);

	$data[ __( 'Visits', 'affiliate-wp' ) ] = array(
		array( 1, 7 ),
		array( 2, 10 ),
		array( 8, 8 )
	);

	$graph = new Affiliate_WP_Graph( $data );
	$graph->display();


}