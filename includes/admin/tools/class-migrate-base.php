<?php

class Affiliate_WP_Migrate_Base {
	
	public $this->type;

	public function __construct() { }

	public function process( $step = 1, $part = '' ) {


	}

	public function step_forward( $step = 1, $part = '' ) {

		$step++;
		$redirect          = add_query_arg( array(
			'page'         => 'affiliate-wp-migrate',
			'type'         => $this->type,
			'part'         => $part,
			'step'         => $step
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;

	}

	public function finish() {
		wp_redirect( admin_url( 'admin.php?page=affiliate-wp' ) ); exit;
	}

}