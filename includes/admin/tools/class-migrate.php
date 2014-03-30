<?php

class Affiliate_WP_Migrate {


	public function __construct() {

		add_action( 'affwp_migrate', array( $this, 'process_migration' ) );

	}

	public function process_migration() {

		if( empty( $_REQUEST['type'] ) ) {
			return false;
		}

		$step = isset( $_REQUEST['step'] ) ? absint( $_REQUEST['step'] ) : 1;
		$type = isset( $_REQUEST['type'] ) ? sanitize_text_field( $_REQUEST['type'] ) : false;

		if( ! $type ) {

			wp_redirect( admin_url() ); exit;

		}

		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/class-migrate-base.php';

		switch( $type ) {

			case 'affiliates-pro' :

				require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/class-migrate-affiliates-pro.php';

				$part = isset( $_REQUEST['part'] ) ? $_REQUEST['part'] : false;

				if( empty( $part ) ) {
					wp_redirect( admin_url() ); exit;
				}

				$migrate = new Affiliate_WP_Migrate_Affiliates_Pro;

				$migrate->process( $step, $part );

				break;

		}

	}

}
new Affiliate_WP_Migrate;