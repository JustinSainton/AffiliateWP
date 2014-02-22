<?php

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

function affwp_was_referred() {
	
}

function affwp_get_referring_affiliate_id() {

}

function affwp_get_affiliate() {

}