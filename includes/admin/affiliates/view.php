<?php

$affiliate_id = isset( $_GET['affiliate_id'] ) ? absint( $_GET['affiliate_id'] ) : 0;

?>
<div class="wrap">
	<h2><?php printf( __( 'Affiliate: #%d', 'affiliate-wp' ), $affiliate_id ); ?></h2>

	<h3><?php _e( 'Earnings', 'affiliate-wp' ); ?></h3>

	<h3><?php _e( 'Referrals', 'affiliate-wp' ); ?></h3>
	
	<h3><?php _e( 'Visits', 'affiliate-wp' ); ?></h3>

</div>