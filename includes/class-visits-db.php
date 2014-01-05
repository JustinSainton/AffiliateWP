<?php

class Affiliate_WP_Visits_DB extends Affiliate_WP_DB {
	
	public $table_name;

	public $version;

	public $primary_key;

	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'affiliate_wp_visits';
		$this->primary_key = 'visit_id';
		$this->version     = '1.0';
	}


	public function get_columns() {
		return array(
			'visit_id'     => '%d',
			'affiliate_id' => '%d',
			'ip'           => '%s',
			'reference'    => '%d',
			'date'         => '%s',
		);
	}

	public function get_column_defaults() {
		return array(
			'affiliate_id' => 0,
			'date'         => date( 'Y-m-d H:i:s' )
		);
	}

	public function add( $data = array() ) {
		return $this->insert( $data, 'visit' );
	}

	public function create_table() {

		global $wpdb;

		if( $wpdb->get_var( "show tables like '{$this->table_name}'" ) == $this->table_name )
			return;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		`visit_id` bigint(20) NOT NULL AUTO_INCREMENT,
		`affiliate_id` bigint(20) NOT NULL,
		`ip` tinytext NOT NULL,
		`referral_id` bigint(20) NOT NULL,
		`date` datetime NOT NULL,
		PRIMARY KEY  (visit_id),
		KEY affiliate_id (affiliate_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}