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

		if ( version_compare( $version, '1.7', '<' ) ) {
			$this->v17_upgrades();
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

	/**
	 * Perform database upgrades for version 1.7
	 *
	 * @access  public
	 * @since   1.7
	 */
	private function v17_upgrades() {

		$integrations = affiliate_wp()->settings->get( 'integrations', array() );

		if ( array_key_exists( 'gravityforms', $integrations ) ) {

			global $wpdb;

			$forms = $wpdb->get_results( "SELECT id FROM {$wpdb->prefix}rg_form" );

			if ( $forms ) {

				foreach ( $forms as $form ) {

					$meta = $wpdb->get_var(
						$wpdb->prepare(
							"SELECT display_meta FROM {$wpdb->prefix}rg_form_meta WHERE form_id = %d",
							$form->id
						)
					);

					$meta = json_decode( $meta );

					if ( isset( $meta->gform_allow_referrals ) ) {
						continue;
					}

					$meta->gform_allow_referrals = 1;

					$meta = json_encode( $meta );

					$wpdb->query(
						$wpdb->prepare(
							"UPDATE {$wpdb->prefix}rg_form_meta SET display_meta = %s WHERE form_id = %d",
							$meta,
							$form->id
						)
					);

				}

			}

		}

		$this->upgraded = true;

	}

}

new Affiliate_WP_Upgrades;
