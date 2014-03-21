<?php

class Affiliate_WP_Integrations {
	
	public function __construct() {

		$this->includes();

	}

	public function includes() {

		// Load each enabled integrations
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/integrations/class-base.php';

		$enabled = array( 'edd' );

		foreach( $enabled as $integration ) {

			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/integrations/class-' . $integration . '.php';
	
		}

	}

}
new Affiliate_WP_Integrations;