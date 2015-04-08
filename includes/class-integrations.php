<?php

class Affiliate_WP_Integrations {

	public function __construct() {

		$this->load();

	}

	public function get_integrations() {

		return apply_filters( 'affwp_integrations', array(
			'edd'            => 'Easy Digital Downloads',
			'formidablepro'  => 'Formidable Pro',
			'gravityforms'   => 'Gravity Forms',
			'exchange'       => 'iThemes Exchange',
			'jigoshop'       => 'Jigoshop',
			'marketpress'    => 'MarketPress',
			'membermouse'    => 'MemberMouse',
			'memberpress'    => 'MemberPress',
			'ninja-forms'    => 'Ninja Forms',
			'pmp'            => 'Paid Memberships Pro',
			'rcp'            => 'Restrict Content Pro',
			'shopp'	         => 'Shopp',
			'sproutinvoices' => 'Sprout Invoices',
			'woocommerce'    => 'WooCommerce',
			'wpec'           => 'WP e-Commerce',
		) );

	}

	public function get_enabled_integrations() {
		return affiliate_wp()->settings->get( 'integrations', array() );
	}

	public function load() {

		// Load each enabled integrations
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/integrations/class-base.php';

		$enabled = apply_filters( 'affwp_enabled_integrations', $this->get_enabled_integrations() );

		do_action( 'affwp_integrations_load' );

		foreach( $enabled as $filename => $integration ) {

			if( file_exists( AFFILIATEWP_PLUGIN_DIR . 'includes/integrations/class-' . $filename . '.php' ) ) {
				require_once AFFILIATEWP_PLUGIN_DIR . 'includes/integrations/class-' . $filename . '.php';
			}

		}

		do_action( 'affwp_integrations_loaded' );

	}

}
