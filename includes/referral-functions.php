<?php


function affwp_get_referral( $referral ) {

	if( is_object( $referral ) && isset( $referral->referral_id ) ) {
		$referral_id = $referral->referral_id;
	} elseif( is_numeric( $referral ) ) {
		$referral_id = absint( $referral );
	} else {
		return false;
	}

	$referral = affiliate_wp()->referrals->get( $referral_id );

	if( ! empty( $referral->products ) ) {
		// products is a multidimensional array. Double unserialize is not a typo
		$referral->products = maybe_unserialize( maybe_unserialize( $referral->products ) );
	}

	return $referral;
}

function affwp_get_referral_status( $referral ) {

	if( is_object( $referral ) && isset( $referral->referral_id ) ) {
		$referral_id = $referral->referral_id;
	} elseif( is_numeric( $referral ) ) {
		$referral_id = absint( $referral );
	} else {
		return false;
	}

	return affiliate_wp()->referrals->get_column( 'status', $referral_id );
}

/**
 * Get the status label for a referral
 *
 * @since 1.6
 * @return string $label The localized version of the referral status
 */
function affwp_get_referral_status_label( $referral ) {

	$referral = affwp_get_referral( $referral );

	if( empty( $referral ) ) {
		return false;
	}

	$statuses = array(
		'paid'     => __( 'Paid', 'affiliate-wp' ),
		'unpaid'   => __( 'Unpaid', 'affiliate-wp' ),
		'rejected' => __( 'Rejected', 'affiliate-wp' ),
		'pending'  => __( 'Pending', 'affiliate-wp' ),
	);

	$label = array_key_exists( $referral->status, $statuses ) ? $statuses[ $referral->status ] : 'pending';

	return apply_filters( 'affwp_referral_status_label', $label, $referral );

}

function affwp_set_referral_status( $referral, $new_status = '' ) {

	if( is_object( $referral ) && isset( $referral->referral_id ) ) {
		$referral_id = $referral->referral_id;
	} elseif( is_numeric( $referral ) ) {
		$referral_id = absint( $referral );
		$referral    = affwp_get_referral( $referral_id );
	} else {
		return false;
	}

	$old_status = $referral->status;

	if( $old_status == $new_status ) {
		return false;
	}

	if( affiliate_wp()->referrals->update( $referral_id, array( 'status' => $new_status ), '', 'referral' ) ) {

		if( 'paid' == $new_status ) {

			affwp_increase_affiliate_earnings( $referral->affiliate_id, $referral->amount );
			affwp_increase_affiliate_referral_count( $referral->affiliate_id );

		} elseif ( 'unpaid' == $new_status && ( 'pending' == $old_status || 'rejected' == $old_status ) ) {

			// Update the visit ID that spawned this referral
			affiliate_wp()->visits->update( $referral->visit_id, array( 'referral_id' => $referral->referral_id ), '', 'visit' );

			do_action( 'affwp_referral_accepted', $referral->affiliate_id, $referral );

		} elseif( 'paid' != $new_status && 'paid' == $old_status ) {

			affwp_decrease_affiliate_earnings( $referral->affiliate_id, $referral->amount );
			affwp_decrease_affiliate_referral_count( $referral->affiliate_id );

		}

		do_action( 'affwp_set_referral_status', $referral_id, $new_status, $old_status );

		return true;
	}

	return false;

}

/**
 * Adds a new referral to the database
 *
 * @since 1.0
 * @return bool
 */
function affwp_add_referral( $data = array() ) {

	if( empty( $data['user_id'] ) && empty( $data['affiliate_id'] ) ) {
		return false;
	}

	if( empty( $data['affiliate_id'] ) ) {

		$user_id      = absint( $data['user_id'] );
		$affiliate_id = affiliate_wp()->affiliates->get_column_by( 'affiliate_id', 'user_id', $user_id );

		if( ! empty( $affiliate_id ) ) {

			$data['affiliate_id'] = $affiliate_id;

		} else {

			return false;

		}

	}

	$args = array(
		'affiliate_id' => absint( $data['affiliate_id'] ),
		'amount'       => ! empty( $data['amount'] )      ? sanitize_text_field( $data['amount'] )      : '',
		'description'  => ! empty( $data['description'] ) ? sanitize_text_field( $data['description'] ) : '',
		'reference'    => ! empty( $data['reference'] )   ? sanitize_text_field( $data['reference'] )   : '',
		'context'      => ! empty( $data['context'] )     ? sanitize_text_field( $data['context'] )     : '',
		'status'       => 'pending',
	);

	$referral_id = affiliate_wp()->referrals->add( $args );

	if( $referral_id ) {

		$status = ! empty( $data['status'] ) ? sanitize_text_field( $data['status'] ) : 'pending';

		affwp_set_referral_status( $referral_id, $status );

		return true;
	}

	return false;

}

function affwp_delete_referral( $referral ) {

	if( is_object( $referral ) && isset( $referral->referral_id ) ) {
		$referral_id = $referral->referral_id;
	} elseif( is_numeric( $referral ) ) {
		$referral_id = absint( $referral );
		$referral    = affwp_get_referral( $referral_id );
	} else {
		return false;
	}

	if( $referral && 'paid' == $referral->status ) {

		// This referral has already been paid, so decrease the affiliate's earnings
		affwp_decrease_affiliate_earnings( $referral->affiliate_id, $referral->amount );

		// Decrease the referral count
		affwp_decrease_affiliate_referral_count( $referral->affiliate_id );

	}

	if( affiliate_wp()->referrals->delete( $referral_id ) ) {

		do_action( 'affwp_delete_referral', $referral_id );

		return true;

	}

	return false;
}

function affwp_calc_referral_amount( $amount = '', $affiliate_id = 0, $reference = 0, $rate = '', $product_id = 0 ) {

	$has_custom = affwp_affiliate_has_custom_rate( $affiliate_id );

	if( $has_custom || empty( $rate ) ) {

		// If the affiliate has a custom rate set, use it. If no rate is specified, use the fallback
		$rate = affwp_get_affiliate_rate( $affiliate_id );

	}

	if( 'percentage' == affwp_get_affiliate_rate_type( $affiliate_id ) ) {

		$referral_amount = round( $amount * $rate, 2 );

	} else {

		$referral_amount = $rate;

	}

	if( $referral_amount < 0 ) {
		$referral_amount = 0;
	}

	return apply_filters( 'affwp_calc_referral_amount', $referral_amount, $affiliate_id, $amount, $reference, $product_id );
}

function affwp_count_referrals( $affiliate_id = 0, $status = array(), $date = array() ) {

	$args = array(
		'affiliate_id' => $affiliate_id,
		'status' => $status
	);

	if( ! empty( $date ) ) {
		$args['date'] = $date;
	}

	return affiliate_wp()->referrals->count( $args );

}