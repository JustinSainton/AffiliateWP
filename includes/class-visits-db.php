<?php

class Affiliate_WP_Visits_DB extends Affiliate_WP_DB {
	
	public $table_name;

	public $version;

	public function __construct() {

		global $wpdb;

		$this->table_name = $wpdb->prefix . 'affiliate_wp_visits'
	}

	public function create_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->referrals->table_name . " (
		`visit_id` bigint(20) NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) NOT NULL default '0',
		`ip` smalltext NOT NULL,
		`reference` varchar(20) NOT NULL default '0',
		`date` datetime NOT NULL default '0000-00-00 00:00:00',
		UNIQUE KEY visit_id (visit_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( "affiliate_wp_visits_db_version", $this->version );
	}

	public function get_columns() {
		return array(
			'visit_id' => '%d',
			'user_id'     => '%d',
			'ip'          => '%s',
			'reference'   => '%s',
			'date'        => '%s',
		);
	}

}