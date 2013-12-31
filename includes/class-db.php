<?php

// General wrapper class for DB classes

class Affiliate_WP_DB {
	
	public $db_version;

	public function __construct() {


	}

	public function get( $table, $column ) {

	}

	public function set( $table, $data ) {

	}

	public function create_tables() {

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->referrals->table_name . " (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT,
		`name` tinytext NOT NULL,
		`description` longtext NOT NULL,
		`duration` smallint NOT NULL,
		`duration_unit` tinytext NOT NULL,
		`price` tinytext NOT NULL,
		`fee` tinytext NOT NULL,
		`list_order` mediumint NOT NULL,
		`level` mediumint NOT NULL,
		`status` tinytext NOT NULL,
		`role` tinytext NOT NULL,
		UNIQUE KEY id (id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		$sql = "CREATE TABLE " . $this->visits->table_name . " (
		`id` mediumint(9) NOT NULL AUTO_INCREMENT,
		`name` tinytext NOT NULL,
		`description` longtext NOT NULL,
		`duration` smallint NOT NULL,
		`duration_unit` tinytext NOT NULL,
		`price` tinytext NOT NULL,
		`fee` tinytext NOT NULL,
		`list_order` mediumint NOT NULL,
		`level` mediumint NOT NULL,
		`status` tinytext NOT NULL,
		`role` tinytext NOT NULL,
		UNIQUE KEY id (id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		add_option( "rcp_db_version", $rcp_db_version );
	}

}