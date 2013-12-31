<?php

class Affiliate_WP_Referrals_DB extends Affiliate_WP_DB  {
	
	public $db_version;

	public $table_name;

	public function __construct() {

		global $wpdb;

		$this->table_name = $wpdb->prefix . 'affiliate_wp_referrals'
	}

	public function get_columns() {
		return array(
			'referral_id' => '%d',
			'user_id'     => '%d',
			'date'        => '%s'
		);
	}

}