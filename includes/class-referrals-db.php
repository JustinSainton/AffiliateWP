<?php

class Affiliate_WP_Referrals_DB extends Affiliate_WP_DB  {
	
	public $table_name;
	
	public $version;

	public $primary_key;

	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'affiliate_wp_referrals';
		$this->primary_key = 'referral_id';
		$this->version     = '1.0';

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

	public function get_column_defaults() {
		return array(
			'user_id'  => get_current_user_id(),
			'date'     => date( 'Y-m-d H:i:s' )
		)
	}

	public function create_table() {

		global $wpdb;

		if( $wpdb->get_var( "show tables like '{$this->table_name}'" ) == $this->table_name )
			return;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		`referral_id` bigint(20) NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) NOT NULL,
		`description` longtext NOT NULL,
		`status` tinytext NOT NULL,
		`amount` mediumtext NOT NULL,
		`ip` tinytext NOT NULL,
		`currency` char(3) NOT NULL,
		`custom` longtext NOT NULL,
		`reference` varchar(20) NOT NULL,
		`date` datetime NOT NULL,
		PRIMARY KEY  (referral_id),
		KEY user_id (user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}