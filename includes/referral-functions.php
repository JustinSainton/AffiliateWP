<?php


function affwp_get_referral( $referral ) {

	if( is_object( $referral ) && isset( $referral->referral_id ) ) {
		$referral_id = $referral->referral_id;
	} elseif( is_numeric( $referral ) ) {
		$referral_id = absint( $referral );
	} else {
		return false;
	}

	return affiliate_wp()->referrals->get( $referral_id );
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

	if( affiliate_wp()->referrals->update( $referral_id, array( 'status' => $new_status ) ) ) {
		
		if( 'paid' == $new_status ) {

			affwp_increase_affiliate_earnings( $referral->affiliate_id, $referral->amount );
			affwp_increase_affiliate_referral_count( $referral->affiliate_id );

		} elseif ( 'unpaid' == $new_status && ( 'pending' == $old_status || 'rejected' == $old_status ) ) {

			// Update the visit ID that spawned this referral
			affiliate_wp()->visits->update( $referral->visit_id, array( 'referral_id' => $referral->referral_id ) );

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

	if( empty( $data['user_id'] ) ) {

		return false;

	}

	$user_id   = absint( $data['user_id'] );
	$affiliate = affiliate_wp()->affiliates->get_by( 'user_id', $user_id );

	if( $affiliate ) {

		$args = array(
			'affiliate_id' => $affiliate->affiliate_id,
			'amount'       => ! empty( $data['amount'] )      ? sanitize_text_field( $data['amount'] )      : '',
			'description'  => ! empty( $data['description'] ) ? sanitize_text_field( $data['description'] ) : '',
			'reference'    => ! empty( $data['reference'] )   ? sanitize_text_field( $data['reference'] )   : '',
			'context'      => ! empty( $data['context'] )     ? sanitize_text_field( $data['context'] )     : '',
			'status'       => ! empty( $data['status'] )      ? sanitize_text_field( $data['status'] )      : ''
		);

		if( affiliate_wp()->referrals->add( $args ) ) {

			return true;
		}

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

	if( 'paid' == $referral->status ) {

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

	if( empty( $rate ) ) {

		$rate = affwp_get_affiliate_rate( $affiliate_id );

	}

	if( 'percentage' == affwp_get_affiliate_rate_type( $affiliate_id ) ) {

		$referral_amount = round( $amount * $rate, 2 );

	} else {

		$referral_amount = $rate;

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