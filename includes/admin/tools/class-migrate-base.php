<?php

class Affiliate_WP_Migrate_Base {
	
	public function __construct() { }

	public function process( $step = 1, $part = '' ) {


	}

	public function step_forward() {

		$step = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
		$part = isset( $_GET['part'] ) ? $_GET['part'] : 'affiliates';

		$step++;
		$redirect  = add_query_arg( array(
			'page' => 'affiliate-wp-migrate',
			'type' => 'affiliates-pro',
			'part' => $part,
			'step' => $step
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;

	}

	public function finish() {
		wp_redirect( admin_url( 'admin.php?page=affiliate-wp' ) ); exit;
	}

}