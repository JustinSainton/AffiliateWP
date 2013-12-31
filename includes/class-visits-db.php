<?php

class Affiliate_WP_Visits_DB extends Affiliate_WP_DB {
	
	public $db_version;

	public $table_name;

	public function __construct() {

		global $wpdb;

		$this->table_name = $wpdb->prefix . 'affiliate_wp_visits'
	}

	public function get_columns() {
		return array(
			'referral_id' => '%d',
			'user_id'     => '%d',
			'date'        => '%s'
		);
	}

}