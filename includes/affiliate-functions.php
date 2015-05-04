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
 * Retrieves the username of the specified affiliate
 *
 * If no affiliate ID is given, it will check the currently logged in affiliate
 *
 * @since 1.6
 * @return string username if affiliate exists, boolean false otherwise
 */
function affwp_get_affiliate_username( $affiliate_id = 0 ) {

	if ( ! is_user_logged_in() && empty( $affiliate_id ) ) {
		return false;
	}

	if ( empty( $affiliate_id ) ) {
		$affiliate_id = affwp_get_affiliate_id();
	}

	$affiliate = affwp_get_affiliate( $affiliate_id );

	if ( $affiliate ) {
		$user_info = get_userdata( $affiliate->user_id );
		
		if ( $user_info ) {
			$username  = esc_html( $user_info->user_login );
			return esc_html( $username );
		}
		
	}

	return false;

}

/**
 * Determines whether or not the affiliate is active
 *
 * If no affiliate ID is given, it will check the currently logged in affiliate
 *
 * @since 1.6
 * @return int
 */
function affwp_is_active_affiliate( $affiliate_id = 0 ) {

	if ( empty( $affiliate_id ) ) {
		$affiliate_id = affwp_get_affiliate_id();
	}

	if ( 'active' == affwp_get_affiliate_status( $affiliate_id ) ) {
		return true;
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

	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'status' => $status ), '', 'affiliate' ) ) {

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
 * Determine if an affiliate has a custom rate
 *
 * @since 1.5
 * @return bool
 */
function affwp_affiliate_has_custom_rate( $affiliate_id = 0 ) {

	$ret = (bool) affiliate_wp()->affiliates->get_column( 'rate', $affiliate_id );

	return apply_filters( 'affwp_affiliate_has_custom_rate', $ret, $affiliate_id );
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
	$affiliate_rate      = affiliate_wp()->affiliates->get_column( 'rate', $affiliate_id );

	if ( ! empty( $affiliate_rate_type ) && ! empty( $affiliate_rate ) ) {

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
 * Retrieves the affiliate's user_login
 *
 * @since 1.6
 * @return string
 */
function affwp_get_affiliate_login( $affiliate ) {

	global $wpdb;

	if ( is_object( $affiliate ) && isset( $affiliate->affiliate_id ) ) {
		$affiliate_id = $affiliate->affiliate_id;
	} elseif ( is_numeric( $affiliate ) ) {
		$affiliate_id = absint( $affiliate );
	} else {
		return false;
	}

	$affiliate = affwp_get_affiliate( $affiliate_id );
	$user_id   = affiliate_wp()->affiliates->get_column( 'user_id', $affiliate_id );
	$login     = $wpdb->get_var( $wpdb->prepare( "SELECT user_login FROM $wpdb->users WHERE ID = '%d'", $user_id ) );

	if ( $login ) {

		return $login;

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
	
		$user_id   = affwp_get_affiliate_user_id( $affiliate_id );
		$referrals = affiliate_wp()->referrals->get_referrals( array( 'affiliate_id' => $affiliate_id, 'number' => -1 ) );
		$visits    = affiliate_wp()->visits->get_visits( array( 'affiliate_id' => $affiliate_id, 'number' => -1 ) );

		foreach( $referrals as $referral ) {
			affiliate_wp()->referrals->delete( $referral->referral_id );
		}

		foreach( $visits as $visit ) {
			affiliate_wp()->visits->delete( $visit->visit_id );
		}

		delete_user_meta( $user_id, 'affwp_referral_notifications' );
		delete_user_meta( $user_id, 'affwp_promotion_method' );

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
	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'earnings' => $earnings ), '', 'affiliate' ) ) {
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
	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'earnings' => $earnings ), '', 'affiliate' ) ) {

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

	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'referrals' => $referrals ), '', 'affiliate' ) ) {

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
	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'referrals' => $referrals ), '', 'affiliate' ) ) {

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

	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'visits' => $visits ), '', 'affiliate' ) ) {

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

	if ( affiliate_wp()->affiliates->update( $affiliate_id, array( 'visits' => $visits ), '', 'affiliate' ) ) {

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
			'status'        => 'pending',
			'rate'          => ! empty( $data['rate'] ) ? sanitize_text_field( $data['rate'] ) : '',
			'rate_type'     => ! empty( $data['rate_type' ] ) ? sanitize_text_field( $data['rate_type'] ) : '',
			'payment_email' => ! empty( $data['payment_email'] ) ? sanitize_text_field( $data['payment_email'] ) : ''
		);

		$affiliate_id = affiliate_wp()->affiliates->add( $args );

		if ( $affiliate_id ) {

			$status = affiliate_wp()->settings->get( 'require_approval' ) ? 'pending' : 'active';
			affwp_set_affiliate_status( $affiliate_id, $status );

			return $affiliate_id;
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
	$user_id      = empty( $affiliate->user_id ) ? absint( $data['user_id'] ) : $affiliate->user_id;

	$args['account_email'] = ! empty( $data['account_email' ] ) && is_email( $data['account_email' ] ) ? sanitize_text_field( $data['account_email'] ) : '';
	$args['payment_email'] = ! empty( $data['payment_email' ] ) && is_email( $data['payment_email' ] ) ? sanitize_text_field( $data['payment_email'] ) : '';
	$args['rate']          = ! empty( $data['rate' ] )      ? sanitize_text_field( $data['rate'] )      : 0;
	$args['rate_type']     = ! empty( $data['rate_type' ] ) ? sanitize_text_field( $data['rate_type'] ) : '';
	$args['user_id']       = $user_id;

	if ( affiliate_wp()->affiliates->update( $affiliate_id, $args, '', 'affiliate' ) ) {

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
		affiliate_wp()->affiliates->update( $affiliate_id, array( 'payment_email' => $data['payment_email'] ), '', 'affiliate' );
	}

	do_action( 'affwp_update_affiliate_profile_settings', $data );

	if ( ! empty( $_POST['affwp_action'] ) ) {
		wp_redirect( add_query_arg( 'affwp_notice', 'profile-updated' ) ); exit;
	}
}

/**
 * Builds an affiliate's referral URL
 * Used by creatives, referral URL generator and [affiliate_referral_url] shortcode
 *
 * @since  1.6
 * @return string
 * @param  $args array of arguments. $base_url, $format, $pretty
 */
function affwp_get_affiliate_referral_url( $args = array() ) {

	$defaults = array(
		'pretty' => '',
		'format' => '',
	);

	$args = wp_parse_args( $args, $defaults );

	// get affiliate ID if passed in
	$affiliate_id = isset( $args['affiliate_id'] ) ? $args['affiliate_id'] : '';

	// get format, username or id
	$format = isset( $args['format'] ) ? $args['format'] : affwp_get_referral_format();

	// pretty URLs
	if ( ! empty( $args['pretty'] ) && 'yes' == $args['pretty'] ) {
		// pretty URLS explicitly turned on
		$pretty = true;
	} elseif ( ( ! empty( $args['pretty'] ) && 'no' == $args['pretty'] ) || false === $args['pretty'] ) {
		// pretty URLS explicitly turned off
		$pretty = false;
	} else {
		// pretty URLs set from admin
		$pretty = affwp_is_pretty_referral_urls();
	} 

	// get base URL
	if ( isset( $args['base_url'] ) ) {
		$base_url = trailingslashit( $args['base_url'] );
	} else {
		$base_url = affwp_get_affiliate_base_url();
	}

	// the format value, either affiliate's ID or username
	$format_value = affwp_get_referral_format_value( $format, $affiliate_id );

	// set up URLs
	$pretty_urls     = trailingslashit( $base_url ) . trailingslashit( affiliate_wp()->tracking->get_referral_var() ) . $format_value;
	$non_pretty_urls = add_query_arg( affiliate_wp()->tracking->get_referral_var(), $format_value, trailingslashit( $base_url ) );
	
	if ( $pretty ) {
		$referral_url = $pretty_urls;
	} else {
		$referral_url = $non_pretty_urls;
	}

	return $referral_url;

}

/**
 * Gets the base URL that is then displayed in the Page URL input field of the affiliate area
 *
 * @since 1.6
 * @return string
 */
function affwp_get_affiliate_base_url() {

	if( isset( $_GET['url'] ) ) {

		$base_url = trailingslashit( urldecode( $_GET['url'] ) );

	} else {
		
		$base_url = home_url( '/' );

	}

	return apply_filters( 'affwp_affiliate_referral_url_base', $base_url );

}