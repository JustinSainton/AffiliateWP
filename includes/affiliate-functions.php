<?php

/**
 * Determines if the specified user ID is an affiliate.
 *
 * If no user ID is given, it will check the currently logged in user
 *
 * @since 1.0
 * @return bool
 */
function affwp_is_affiliate( $user_id = 0 ) {
	return (bool) affwp_get_affiliate_id( $user_id );
}

/**
 * Retrieves the affiliate ID of the specified user
 *
 * If no user ID is given, it will check the currently logged in user
 *
 * @since 1.0
 * @return int
 */
function affwp_get_affiliate_id( $user_id = 0 ) {

	if ( ! is_user_logged_in() && empty( $user_id ) ) {
		return false;
	}

	if ( empty( $user_id ) ) {
		$user_id = get_current_user_id();
	}

	$affiliate = affiliate_wp()->affiliates->get_by( 'user_id', $user_id );

	if ( $affiliate ) {
		return $affiliate->affiliate_id;
	}

	return false;

}

/**
 * Retrieves an affiliate's user ID
 *
 * @since 1.0
 * @return bool
 */
function affwp_get_affiliate_user_id( $affiliate ) {

	if ( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif ( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	return affiliate_wp()->affiliates->get_column( 'user_id', $affiliate_id );

}

/**
 * Retrieves the affiliate object
 *
 * @since 1.0
 * @return object
 */
function affwp_get_affiliate( $affiliate ) {

	if ( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif ( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	return affiliate_wp()->affiliates->get( $affiliate_id );
}

/**
 * Retrieves the affiliate's status
 *
 * @since 1.0
 * @return string
 */
function affwp_get_affiliate_status( $affiliate ) {

	if ( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif ( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	return affiliate_wp()->affiliates->get_column( 'status', $affiliate_id );
}

/**
 * Sets the status for an affiliate
 *
 * @since 1.0
 * @return bool
 */
function affwp_set_affiliate_status( $affiliate, $status = '' ) {

	if ( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif ( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	$old_status = affiliate_wp()->affiliates->get_column( 'status', $affiliate_id );

	do_action( 'affwp_set_affiliate_status', $affiliate_id, $status, $old_status );

	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'status' => $status ) ) ) {

		return true;
	}

}

/**
 * Retrieves the referral rate for an affiliate
 *
 * @since 1.0
 * @return float
 */
function affwp_get_affiliate_rate( $affiliate_id = 0, $formatted = false ) {

	// default rate
	$rate = affiliate_wp()->settings->get( 'referral_rate', 20 );

	$affiliate_rate = affiliate_wp()->affiliates->get_column( 'rate', $affiliate_id );

	if ( ! empty( $affiliate_rate ) ) {

		$rate = $affiliate_rate;

	}

	$type = affwp_get_affiliate_rate_type( $affiliate_id );

	if ( 'percentage' == $type ) {

		// Sanitize the rate and ensure it's in the proper format
		if ( $rate > 1 ) {
			$rate = $rate / 100;
		}

	}

	$rate = apply_filters( 'affwp_get_affiliate_rate', $rate, $affiliate_id, $type );

	// If rate should be formatted, format it based on the type
	if ( $formatted ) {

		switch( $type ) {

			case 'percentage' :

				$rate = $rate * 100 . '%';

				break;

			case 'flat' :

				$rate = affwp_currency_filter( $rate );

				break;

			default :

				break;

		}

	}

	return $rate;
}

/**
 * Retrieves the referral rate type for an affiliate
 *
 * Either "flat" or "percentage"
 *
 * @since 1.1
 * @return string
 */
function affwp_get_affiliate_rate_type( $affiliate_id = 0 ) {

	// Allowed types
	$types = affwp_get_affiliate_rate_types();

	// default rate
	$type = affiliate_wp()->settings->get( 'referral_rate_type', 'percentage' );

	$affiliate_rate_type = affiliate_wp()->affiliates->get_column( 'rate_type', $affiliate_id );

	if ( ! empty( $affiliate_rate_type ) ) {

		$type = $affiliate_rate_type;

	}

	if ( ! array_key_exists( $type, $types ) ) {
		$type = 'percentage';
	}

	return apply_filters( 'affwp_get_affiliate_rate_type', $type, $affiliate_id );

}

/**
 * Retrieves an array of allowed affiliate rate types
 *
 * @since 1.1
 * @return array
 */
function affwp_get_affiliate_rate_types() {

	// Allowed types
	$types = array(
		'percentage' => __( 'Percentage (%)', 'affiliate-wp' ),
		'flat'       => sprintf( __( 'Flat %s', 'affiliate-wp' ), affwp_get_currency() )
	);

	return apply_filters( 'affwp_get_affiliate_rate_types', $types );

}

/**
 * Retrieves the affiliate's email address
 *
 * @since 1.0
 * @return string
 */
function affwp_get_affiliate_email( $affiliate ) {

	global $wpdb;

	if ( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif ( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	$affiliate   = affwp_get_affiliate( $affiliate_id );

	if ( ! empty( $affiliate->payment_email ) && is_email( $affiliate->payment_email ) ) {
		$email   = $affiliate->payment_email;
	} else {
		$user_id = affiliate_wp()->affiliates->get_column( 'user_id', $affiliate_id );
		$email   = $wpdb->get_var( $wpdb->prepare( "SELECT user_email FROM $wpdb->users WHERE ID = '%d'", $user_id ) );
	}

	if ( $email ) {

		return $email;

	}

	return false;

}

/**
 * Deletes an affiliate
 *
 * @since 1.0
 * @param $delete_data bool
 * @return bool
 */
function affwp_delete_affiliate( $affiliate, $delete_data = false ) {

	if ( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif ( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	if( $delete_data ) {
	
		$referrals = affiliate_wp()->referrals->get_referrals( array( 'affiliate_id' => $affiliate_id, 'number' => -1 ) );
		$visits    = affiliate_wp()->visits->get_visits( array( 'affiliate_id' => $affiliate_id, 'number' => -1 ) );

		foreach( $referrals as $referral ) {
			affiliate_wp()->referrals->delete( $referral->referral_id );
		}

		foreach( $visits as $visit ) {
			affiliate_wp()->visits->delete( $visit->visit_id );
		}

	}

	return affiliate_wp()->affiliates->delete( $affiliate_id );

}

/**
 * Retrieves the total paid earnings for an affiliate
 *
 * @since 1.0
 * @return float
 */
function affwp_get_affiliate_earnings( $affiliate, $formatted = false ) {

	if ( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif ( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	$earnings = affiliate_wp()->affiliates->get_column( 'earnings', $affiliate_id );

	if ( empty( $earnings ) ) {

		$earnings = 0;

	}

	if ( $formatted ) {

		$earnings = affwp_currency_filter( $earnings );

	}

	return $earnings;
}

/**
 * Retrieves the total unpaid earnings for an affiliate
 *
 * @since 1.0
 * @return float
 */
function affwp_get_affiliate_unpaid_earnings( $affiliate, $formatted = false ) {

	if ( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif ( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	$referrals = affiliate_wp()->referrals->get_referrals( array( 'affiliate_id' => $affiliate_id, 'status' => 'unpaid', 'number' => -1 ) );
	$earnings = 0;

	if ( ! empty( $referrals ) ) {


		foreach( $referrals as $referral ) {

			$earnings += $referral->amount;

		}
	}

	if ( $formatted ) {

		$earnings = affwp_currency_filter( $earnings );

	}

	return $earnings;
}

/**
 * Increases an affiliate's total paid earnings by the specified amount
 *
 * @since 1.0
 * @return float|bool
 */
function affwp_increase_affiliate_earnings( $affiliate_id = 0, $amount = '' ) {

	if ( empty( $affiliate_id ) ) {
		return false;
	}

	if ( empty( $amount ) || floatval( $amount ) <= 0 ) {
		return false;
	}

	$earnings = affwp_get_affiliate_earnings( $affiliate_id );
	$earnings += $amount;
	$earnings = round( $earnings, 2 );
	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'earnings' => $earnings ) ) ) {
		$alltime = get_option( 'affwp_alltime_earnings' );
		$alltime += $amount;
		update_option( 'affwp_alltime_earnings', $alltime );

		return $earnings;

	} else {

		return false;

	}

}

/**
 * Decreases an affiliate's total paid earnings by the specified amount
 *
 * @since 1.0
 * @return float|bool
 */
function affwp_decrease_affiliate_earnings( $affiliate_id = 0, $amount = '' ) {

	if ( empty( $affiliate_id ) ) {
		return false;
	}

	if ( empty( $amount ) || floatval( $amount ) <= 0 ) {
		return false;
	}

	$earnings = affwp_get_affiliate_earnings( $affiliate_id );
	$earnings -= $amount;
	$earnings = round( $earnings, 2 );
	if ( $earnings < 0 ) {
		$earnings = 0;
	}
	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'earnings' => $earnings ) ) ) {

		$alltime = get_option( 'affwp_alltime_earnings' );
		$alltime -= $amount;
		if ( $alltime < 0 ) {
			$alltime = 0;
		}
		update_option( 'affwp_alltime_earnings', $alltime );

		return $earnings;

	} else {

		return false;

	}

}

/**
 * Retrieves the number of paid referrals for an affiliate
 *
 * @since 1.0
 * @return int
 */
function affwp_get_affiliate_referral_count( $affiliate ) {

	if ( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif ( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	return affiliate_wp()->affiliates->get_column( 'referrals', $affiliate_id );
}

/**
 * Increases an affiliate's total paid referrals by 1
 *
 * @since 1.0
 * @return float|bool
 */
function affwp_increase_affiliate_referral_count( $affiliate_id = 0 ) {

	if ( empty( $affiliate_id ) ) {
		return false;
	}

	$referrals = affwp_get_affiliate_referral_count( $affiliate_id );
	$referrals += 1;

	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'referrals' => $referrals ) ) ) {

		return $referrals;

	} else {

		return false;

	}

}

/**
 * Decreases an affiliate's total paid referrals by 1
 *
 * @since 1.0
 * @return float|bool
 */
function affwp_decrease_affiliate_referral_count( $affiliate_id = 0 ) {

	if ( empty( $affiliate_id ) ) {
		return false;
	}

	$referrals = affwp_get_affiliate_referral_count( $affiliate_id );
	$referrals -= 1;
	if ( $referrals < 0 ) {
		$referrals = 0;
	}
	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'referrals' => $referrals ) ) ) {

		return $referrals;

	} else {

		return false;

	}

}

/**
 * Retrieves an affiliate's total visit count
 *
 * @since 1.0
 * @return int
 */
function affwp_get_affiliate_visit_count( $affiliate ) {

	if ( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif ( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	$visits = affiliate_wp()->affiliates->get_column( 'visits', $affiliate_id );

	if ( $visits < 0 ) {
		$visits = 0;
	}

	return absint( $visits );
}

/**
 * Increases an affiliate's total visit count by 1
 *
 * @since 1.0
 * @return int
 */
function affwp_increase_affiliate_visit_count( $affiliate_id = 0 ) {

	if ( empty( $affiliate_id ) ) {
		return false;
	}

	$visits = affwp_get_affiliate_visit_count( $affiliate_id );
	$visits += 1;

	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'visits' => $visits ) ) ) {

		return $visits;

	} else {

		return false;

	}

}

/**
 * Decreases an affiliate's total visit count by 1
 *
 * @since 1.0
 * @return float|bool
 */
function affwp_decrease_affiliate_visit_count( $affiliate_id = 0 ) {

	if ( empty( $affiliate_id ) ) {
		return false;
	}

	$visits = affwp_get_affiliate_visit_count( $affiliate_id );
	$visits -= 1;

	if ( $visits < 0 ) {
		$visits = 0;
	}

	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'visits' => $visits ) ) ) {

		return $visits;

	} else {

		return false;

	}

}

/**
 * Retrieves the affiliate's conversion rate
 *
 * @since 1.0
 * @return float
 */
function affwp_get_affiliate_conversion_rate( $affiliate ) {

	if ( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif ( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	$rate = 0;

	$referrals = affiliate_wp()->referrals->count( array( 'affiliate_id' => $affiliate_id, 'status' => array( 'paid', 'unpaid' ) ) );
	$visits    = affwp_get_affiliate_visit_count( $affiliate_id );
	if ( $visits > 0 ) {
		$rate = round( ( $referrals / $visits ) * 100, 2 );
	}

	return apply_filters( 'affwp_get_affiliate_conversion_rate', $rate . '%', $affiliate_id );

}

/**
 * Adds a new affiliate to the database
 *
 * @since 1.0
 * @return bool
 */
function affwp_add_affiliate( $data = array() ) {

	if ( empty( $data['user_id'] ) ) {

		return false;

	}

	$user_id = absint( $data['user_id'] );

	if ( ! affiliate_wp()->affiliates->get_by( 'user_id', $user_id ) ) {

		$args = array(
			'user_id'       => $user_id,
			'status'        => affiliate_wp()->settings->get( 'require_approval' ) ? 'pending' : 'active',
			'rate'          => ! empty( $data['rate'] ) ? sanitize_text_field( $data['rate'] ) : '',
			'payment_email' => ! empty( $data['payment_email'] ) ? sanitize_text_field( $data['payment_email'] ) : ''
		);

		if ( affiliate_wp()->affiliates->add( $args ) ) {

			return true;
		}

	}

	return false;

}

/**
 * Updates an affiliate
 *
 * @since 1.0
 * @return bool
 */
function affwp_update_affiliate( $data = array() ) {

	if ( empty( $data['affiliate_id'] ) ) {
		return false;
	}

	$args         = array();
	$affiliate_id = absint( $data['affiliate_id'] );
	$affiliate    = affwp_get_affiliate( $affiliate_id );
	$user_id      = $affiliate->user_id;

	$args['account_email'] = ! empty( $data['account_email' ] ) && is_email( $data['account_email' ] ) ? sanitize_text_field( $data['account_email'] ) : '';
	$args['payment_email'] = ! empty( $data['payment_email' ] ) && is_email( $data['payment_email' ] ) ? sanitize_text_field( $data['payment_email'] ) : '';
	$args['rate']          = ! empty( $data['rate' ] )      ? sanitize_text_field( $data['rate'] )      : 0;
	$args['rate_type']     = ! empty( $data['rate_type' ] ) ? sanitize_text_field( $data['rate_type'] ) : '';

	if ( affiliate_wp()->affiliates->update( $affiliate_id, $args ) ) {

		// update affiliate's account email
		if( wp_update_user( array( 'ID' => $user_id, 'user_email' => $args['account_email'] ) ) ) {

			return true;
		
		}

	}

	return false;

}

/**
 * Updates an affiliate's profile settings
 *
 * @since 1.0
 * @return bool
 */
function affwp_update_profile_settings( $data = array() ) {

	if ( ! is_user_logged_in() ) {
		return false;
	}

	if ( empty( $data['affiliate_id'] ) ) {
		return false;
	}

	if ( affwp_get_affiliate_id() != $data['affiliate_id'] && ! current_user_can( 'manage_affiliates' ) ) {

		return false;
	}

	$affiliate_id = absint( $data['affiliate_id'] );
	$user_id      = affwp_get_affiliate_user_id( $affiliate_id );

	if ( ! empty( $data['referral_notifications'] ) ) {

		update_user_meta( $user_id, 'affwp_referral_notifications', '1' );

	} else {

		delete_user_meta( $user_id, 'affwp_referral_notifications' );

	}

	if ( ! empty( $data['payment_email'] ) && is_email( $data['payment_email'] ) ) {
		affiliate_wp()->affiliates->update( $affiliate_id, array( 'payment_email' => $data['payment_email'] ) );
	}

	do_action( 'affwp_update_affiliate_profile_settings', $data );

	if ( ! empty( $_POST['affwp_action'] ) ) {
		wp_redirect( add_query_arg( 'affwp_notice', 'profile-updated' ) ); exit;
	}
}