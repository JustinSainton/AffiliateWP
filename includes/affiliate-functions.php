<?php

function affwp_is_affiliate() {
	return (bool) affwp_get_affiliate_id();
}

function affwp_get_affiliate_id( $user_id = 0 ) {

	if( ! is_user_logged_in() && empty( $user_id ) ) {
		return false;
	}

	if( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$affiliate = affiliate_wp()->affiliates->get_by( 'user_id', $user_id );

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


	if( affiliate_wp()->affiliates->update( $affiliate_id, array( 'status' => $status ) ) ){

		if ( ! empty( $_REQUEST['action'] ) ) {

			wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-affiliates&affwp_notice=affiliate_'.$status ) ); exit;
		}

		return true;
	}

}

function affwp_get_affiliate_rate( $affiliate_id = 0 ) {

	// default rate
	$rate = affiliate_wp()->settings->get( 'referral_rate', 20 );

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

function affwp_get_affiliate_email( $affiliate ) {

	global $wpdb;

	if( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	$table_name = $wpdb->prefix . 'affiliate_wp_affiliates';

	$user_id = $wpdb->get_var( $wpdb->prepare( "SELECT user_id FROM $table_name WHERE affiliate_id = '%d'", $affiliate_id ) );

	$email = $wpdb->get_var( $wpdb->prepare( "SELECT user_email FROM $wpdb->users WHERE ID = '%d'", $uder_id ) );

	if( $email ) {

		return $email;

	}

	return false;

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


function affwp_get_affiliate_earnings( $affiliate, $formatted = false ) {

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

	if( $formatted ) {

		$earnings = affwp_currency_filter( $earnings );

	}

	return $earnings;
}

function affwp_get_affiliate_unpaid_earnings( $affiliate, $formatted = false ) {

	if( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	$referrals = affiliate_wp()->referrals->get_referrals( array( 'affiliate_id' => $affiliate_id, 'status' => 'unpaid' ) );
	$earnings = 0;

	if( ! empty( $earnings ) ) {

		foreach( $referrals as $referral ) {

			$earnings += $referral->amount;

		}
	}

	if( $formatted ) {

		$earnings = affwp_currency_filter( $earnings );

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
		$alltime = get_option( 'affwp_alltime_earnings' );
		$alltime += $amount;
		update_option( 'affwp_alltime_earnings', $alltime );

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
	if( $earnings < 0 ) {
		$earnings = 0;
	}
	if( affiliate_wp()->affiliates->update( $affiliate_id, array( 'earnings' => $earnings ) ) ) {

		$alltime = get_option( 'affwp_alltime_earnings' );
		$alltime -= $amount;
		if( $alltime < 0 ) {
			$alltime = 0;
		}
		update_option( 'affwp_alltime_earnings', $alltime );

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
	if( $referrals < 0 ) {
		$referrals = 0;
	}
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

	$visits = affiliate_wp()->affiliates->get_column( 'visits', $affiliate_id );

	if( $visits < 0 ) {
		$visits = 0;
	}

	return absint( $visits );
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

	if( $visits < 0 ) {
		$visits = 0;
	}

	if( affiliate_wp()->affiliates->update( $affiliate_id, array( 'visits' => $visits ) ) ) {

		return $visits;

	} else {

		return false;

	}

}

function affwp_get_affiliate_conversion_rate( $affiliate ) {

	if( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	$rate = 0;

	$referrals = affwp_get_affiliate_referral_count( $affiliate_id );
	$visits    = affwp_get_affiliate_visit_count( $affiliate_id );
	if( $referrals > 0 ) {
		$rate = round( $visits / $referrals, 2 );
	}

	return apply_filters( 'affwp_get_affiliate_conversion_rate', $rate . '%', $affiliate_id );

}

function affwp_add_affiliate( $data = array() ) {

	if( empty( $data['user_id'] ) ) {

		return false;

	}

	$user_id = absint( $data['user_id'] );

	if( ! affiliate_wp()->affiliates->get_by( 'user_id', $user_id ) ) {

		$args = array(
			'user_id' => $user_id,
			'rate'    => ! empty( $data['rate'] ) ? sanitize_text_field( $data['rate'] ) : ''
		);

		if( affiliate_wp()->affiliates->add( $args ) ) {

			if ( ! empty( $_POST['affwp_action'] ) ) {

				wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-affiliates&affwp_notice=affiliate_added' ) ); exit;
			}

			return true;
		}

	}

	return false;

}

function affwp_update_affiliate( $data = array() ) {

	if ( empty( $data['affiliate_id'] ) ) {

		return false;

	}

	$args         = array();
	$affiliate_id = absint( $data['affiliate_id'] );

	$args['rate'] = ! empty( $data['rate' ] ) ? sanitize_text_field( $data['rate'] ) : 0;

	if ( affiliate_wp()->affiliates->update( $affiliate_id, $args ) ) {

		if ( ! empty( $_POST['affwp_action'] ) ) {
			// This is an update call from the edit screen
			wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-affiliates&action=edit_affiliate&affwp_notice=affiliate_updated&affiliate_id=' . $affiliate_id ) ); exit;
		}

		return true;

	}

	return false;

}