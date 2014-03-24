<?php

function affwp_is_affiliate() {
	return (bool) affwp_get_affiliate_id();
}

function affwp_get_affiliate_id() {

	if( ! is_user_logged_in() ) {
		return false;
	}

	$affiliate = affiliate_wp()->affiliates->get_by( 'user_id', get_current_user_id() );
	
	if( $affiliate ) {
		return $affiliate->affiliate_id;
	}

	return false;

}

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

function affwp_get_affiliate_rate( $affiliate_id = 0 ) {

	// default rate
	$rate = affiliate_wp()->settings->get( 'referral_rate', 30 );

	$affiliate_rate = affiliate_wp()->affiliates->get_column( 'rate', $affiliate_id );

	if( ! empty( $affiliate_rate ) ) {

		$rate = $affiliate_rate;

	}

	// Sanitize the rate and ensure it's in the proper format
	if( $rate > 1 ) {
		$rate = $rate / 100;
	}

	return apply_filters( 'affwp_get_affiliate_rate', $rate, $affiliate_id );
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

	$earnings = affiliate_wp()->affiliates->get_column( 'earnings', $affiliate_id );

	if( empty( $earnings ) ) {

		$earnings = 0;

	}

	return $earnings;
}

function affwp_increase_affiliate_earnings( $affiliate_id = 0, $amount = '' ) {

	if( empty( $affiliate_id ) ) {
		return false;
	}

	if( empty( $amount ) || floatval( $amount ) <= 0 ) {
		return false;
	}

	$earnings = affwp_get_affiliate_earnings( $affiliate_id );
	$earnings += $amount;
	$earnings = round( $earnings, 2 );
	if( affiliate_wp()->affiliates->update( $affiliate_id, array( 'earnings' => $earnings ) ) ) {

		return $earnings;

	} else {

		return false;

	}

}

function affwp_decrease_affiliate_earnings( $affiliate_id = 0, $amount = '' ) {

	if( empty( $affiliate_id ) ) {
		return false;
	}

	if( empty( $amount ) || floatval( $amount ) <= 0 ) {
		return false;
	}

	$earnings = affwp_get_affiliate_earnings( $affiliate_id );
	$earnings -= $amount;
	$earnings = round( $earnings, 2 );
	if( affiliate_wp()->affiliates->update( $affiliate_id, array( 'earnings' => $earnings ) ) ) {

		return $earnings;

	} else {

		return false;

	}

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

function affwp_increase_affiliate_referral_count( $affiliate_id = 0 ) {

	if( empty( $affiliate_id ) ) {
		return false;
	}

	$referrals = affwp_get_affiliate_referral_count( $affiliate_id );
	$referrals += 1;

	if( affiliate_wp()->affiliates->update( $affiliate_id, array( 'referrals' => $referrals ) ) ) {

		return $referrals;

	} else {

		return false;

	}

}

function affwp_decrease_affiliate_referral_count( $affiliate_id = 0 ) {

	if( empty( $affiliate_id ) ) {
		return false;
	}

	$referrals = affwp_get_affiliate_referral_count( $affiliate_id );
	$referrals -= 1;

	if( affiliate_wp()->affiliates->update( $affiliate_id, array( 'referrals' => $referrals ) ) ) {

		return $referrals;

	} else {

		return false;

	}

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

function affwp_increase_affiliate_visit_count( $affiliate_id = 0 ) {

	if( empty( $affiliate_id ) ) {
		return false;
	}

	$visits = affwp_get_affiliate_visit_count( $affiliate_id );
	$visits += 1;

	if( affiliate_wp()->affiliates->update( $affiliate_id, array( 'visits' => $visits ) ) ) {

		return $visits;

	} else {

		return false;

	}

}

function affwp_decrease_affiliate_visit_count( $affiliate_id = 0 ) {

	if( empty( $affiliate_id ) ) {
		return false;
	}

	$visits = affwp_get_affiliate_visit_count( $affiliate_id );
	$visits -= 1;

	if( affiliate_wp()->affiliates->update( $affiliate_id, array( 'visits' => $visits ) ) ) {

		return $visits;

	} else {

		return false;

	}

}

function affwp_add_affiliate( $data = array() ) {

	if( empty( $data['user_id'] ) ) {

		return false;

	}

	$user_id = absint( $data['user_id'] );

	if( ! affiliate_wp()->affiliates->get_by( 'user_id', $user_id ) ) {

		if( affiliate_wp()->affiliates->add( array( 'user_id' => $user_id ) ) ) {

			return true;

		}

	}

	return false;

}

function affwp_update_affiliate( $data = array() ) {

	if( empty( $data['affiliate_id'] ) ) {

		return false;

	}

	$args         = array();
	$affiliate_id = absint( $data['affiliate_id'] );

	if( ! empty( $data['rate'] ) ) {

		$args['rate'] = sanitize_text_field( $data['rate'] );

	}

	if( affiliate_wp()->affiliates->update( $affiliate_id, $args ) ) {

		return true;

	}

	return false;

}

function affwp_affiliate_graph( $affiliate_id = 0 ) {

	// outputs a graph of the affiliate's earnings, referral, and visit stats

	$dates = affwp_get_report_dates();

	$start = $dates['year'] . '-' . $dates['m_start'] . '-' . $dates['day'] . ' 00:00:00';
	$end   = $dates['year_end'] . '-' . $dates['m_end'] . '-' . $dates['day_end'] . ' 23:59:59';

	$args = array(
		'number' => -1,
		'date'   => array(
			'start' => $start,
			'end'   => $end
		)
	);

	//echo '<pre>'; print_r( $args ); echo '</pre>';

	$referrals = affiliate_wp()->referrals->get_referrals( $args );

	//echo '<pre>'; print_r( $referrals ); echo '</pre>';

	/*
	$data = array();

	$data[ __( 'Earnings', 'affiliate-wp' ) ] = array(
		array( 1, 5 ),
		array( 3, 8 ),
		array( 10, 2 )
	);


	$data[ __( 'Visits', 'affiliate-wp' ) ] = array(
		array( 1, 7 ),
		array( 2, 10 ),
		array( 8, 8 )
	);*/

	$data = array(
		array( 4, 12 ),
		array( 2, 8 ),
		array( 12, 8 )
	);

	$graph = new Affiliate_WP_Graph;

	$graph->add_line( 'referrals', __( 'Referrals', 'affiliate-wp' ), $data );

//	$graph->set( 'data', $data );

	$graph->display();


}