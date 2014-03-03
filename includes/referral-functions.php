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

function affwp_set_referral_status( $referral, $status = '' ) {

	if( is_object( $referral ) && isset( $referral->referral_id ) ) {
		$referral_id = $referral->referral_id;
	} elseif( is_numeric( $referral ) ) {
		$referral_id = absint( $referral );
	} else {
		return false;
	}

	return affiliate_wp()->referrals->update( $referral_id, array( 'status' => $status ) );
}

function affwp_delete_referral( $referral ) {

	if( is_object( $referral ) && isset( $referral->referral_id ) ) {
		$referral_id = $referral->referral_id;
	} elseif( is_numeric( $referral ) ) {
		$referral_id = absint( $referral );
	} else {
		return false;
	}

	return affiliate_wp()->referrals->delete( $referral_id );
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