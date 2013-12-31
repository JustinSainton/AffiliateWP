<?php

class Affiliate_WP_Referrals_DB extends Affiliate_WP_DB  {
	
	public $table_name;
	
	public $version;

	public function __construct() {

		global $wpdb;

		$this->table_name = $wpdb->prefix . 'affiliate_wp_referrals'
	}

	public function create_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->referrals->table_name . " (
		`referral_id` bigint(20) NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) NOT NULL default '0',
		`description` largetext NOT NULL,
		`status` smalltext NOT NULL default 'pending',
		`amount` mediumtext NOT NULL default '0',
		`ip` smalltext NOT NULL,
		`currency` char(3) NOT NULL,
		`custom` largetext NOT NULL,
		`reference` varchar(20) NOT NULL default '0',
		`date` datetime NOT NULL default '0000-00-00 00:00:00',
		UNIQUE KEY referral_id (referral_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( "affiliate_wp_visits_db_version", $this->version );
	}

	public function get_columns() {
		return array(
			'referral_id' => '%d',
			'user_id'     => '%d',
			'description' => '%s',
			'status'      => '%s',
			'amount'      => '%s',
			'ip'          => '%s',
			'currency'    => '%s',
			'custom'      => '%s',
			'reference'   => '%d',
			'date'        => '%s',
		);
	}

}