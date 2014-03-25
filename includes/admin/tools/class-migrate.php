<?php

class Affiliate_WP_Migrate {
	

	public function __construct() {

		add_action( 'affwp_migrate', array( $this, 'process_migration' ) );

	}

	public function process_migration() {

		if( empty( $_GET['type'] ) ) {
			return false;
		}

		$step = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;

		switch( $type ) {

			case 'affiliates-pro' :

				$part    = isset( $_GET['part'] ) ? $_GET['part'] : 'affiliates';

				$migrate = new Affiliate_WP_Migrate_Affiliates_Pro;

				$migrate->process( $step, $part );	

				break;

		}

	}

}
new Affiliate_WP_Migrate;