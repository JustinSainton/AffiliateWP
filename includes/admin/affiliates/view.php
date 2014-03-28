<?php

$affiliate_id = isset( $_GET['affiliate_id'] ) ? absint( $_GET['affiliate_id'] ) : 0;

?>
<div class="wrap">
	<h2><?php printf( __( 'Affiliate: #%d', 'affiliate-wp' ), $affiliate_id ); ?></h2>

	<h3><?php _e( 'Earnings', 'affiliate-wp' ); ?></h3>

	<div id="affwp-affiliate-report">

		<div class="affwp-total"><?php printf( __( 'Total earnings: %s' ), affwp_get_affiliate_earnings( $affiliate_id ) ); ?></div>
		<div class="affwp-total"><?php printf( __( 'Total referrals: %s' ), affwp_get_affiliate_referral_count( $affiliate_id ) ); ?></div>
		<div class="affwp-total"><?php printf( __( 'Total visits: %s' ), affwp_get_affiliate_visit_count( $affiliate_id ) ); ?></div>

	</div>

</div>