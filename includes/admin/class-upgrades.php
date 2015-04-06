<?php

class Affiliate_WP_Upgrades {

	private $upgraded = false;

	public function __construct() {

		add_action( 'admin_init', array( $this, 'init' ), -9999 );

	}

	public function init() {

		$version = get_option( 'affwp_version' );
		if ( empty( $version ) ) {
			$version = '1.0.6'; // last version that didn't have the version option set
		}

		if ( version_compare( $version, '1.1', '<' ) ) {
			$this->v11_upgrades();
		}

		if ( version_compare( $version, '1.2.1', '<' ) ) {
			$this->v121_upgrades();
		}

		if ( version_compare( $version, '1.3', '<' ) ) {
			$this->v13_upgrades();
		}

		if ( version_compare( $version, '1.6', '<' ) ) {
			$this->v16_upgrades();
		}

		// If upgrades have occurred
		if ( $this->upgraded ) {
			update_option( 'affwp_version_upgraded_from', $version );
			update_option( 'affwp_version', AFFILIATEWP_VERSION );
		}

	}
	
	/**
	 * Perform database upgrades for version 1.1 
	 *
	 * @access  public
	 * @since   1.1
	*/
	private function v11_upgrades() {

		@affiliate_wp()->affiliates->create_table();

		$this->upgraded = true;

	}

	/**
	 * Perform database upgrades for version 1.2.1
	 *
	 * @access  public
	 * @since   1.2.1
	*/
	private function v121_upgrades() {

		@affiliate_wp()->creatives->create_table();

		$this->upgraded = true;

	}

	/**
	 * Perform database upgrades for version 1.3
	 *
	 * @access  public
	 * @since   1.3
	 */
	private function v13_upgrades() {

		@affiliate_wp()->creatives->create_table();

		// Clear rewrite rules
		flush_rewrite_rules();

		$this->upgraded = true;

	}

	/**
	 * Perform database upgrades for version 1.6
	 *
	 * @access  public
	 * @since   1.6
	 */
	private function v16_upgrades() {

		@affiliate_wp()->affiliate_meta->create_table();
		@affiliate_wp()->referrals->create_table();

		$this->upgraded = true;

	}


}
new Affiliate_WP_Upgrades;