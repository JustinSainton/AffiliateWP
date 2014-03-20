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
			'referral_id'  => '%d',
			'url'          => '%s',
			'ip'           => '%s',
			'date'         => '%s',
		);
	}

	public function get_column_defaults() {
		return array(
			'affiliate_id' => 0,
			'date'         => date( 'Y-m-d H:i:s' )
		);
	}

	/**
	 * Retrieve visits from the database
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function get_visits( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'number'       => 20,
			'offset'       => 0,
			'affiliate_id' => 0,
			'referral_id'  => 0,
		);

		$args  = wp_parse_args( $args, $defaults );

		$where = '';

		// visits for specific affiliates
		if( ! empty( $args['affiliate_id'] ) ) {

			if( is_array( $args['affiliate_id'] ) ) {
				$affiliate_ids = implode( ',', $args['affiliate_id'] );
			} else {
				$affiliate_ids = intval( $args['affiliate_id'] );
			}	

			$where .= "WHERE `affiliate_id` IN( {$affiliate_ids} ) ";

		}

		// visits for specific referral
		if( ! empty( $args['referral_id'] ) ) {

			if( is_array( $args['referral_id'] ) ) {
				$referral_ids = implode( ',', $args['referral_id'] );
			} else {
				$referral_ids = intval( $args['referral_id'] );
			}	

			$where .= "WHERE `referral_id` IN( {$referral_ids} ) ";

		}

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  $this->table_name $where LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ) );

	}

	public function add( $data = array() ) {
		$visit_id = $this->insert( $data, 'visit' );
		
		affiliate_wp()->affiliates->bump_visits( $data['affiliate_id'] );

		return $visit_id;
	}

	/**
	 * Count the total number of visits in the database
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function count( $args = array() ) {

		global $wpdb;

		$where = '';

		// visits for specific affiliate
		if( ! empty( $args['affiliate_id'] ) ) {

			if( is_array( $args['affiliate_id'] ) ) {
				$affiliate_ids = implode( ',', $args['affiliate_id'] );
			} else {
				$affiliate_ids = intval( $args['affiliate_id'] );
			}	

			$where .= "WHERE `affiliate_id` IN( {$affiliate_ids} ) ";

		}

		// visits for specific referral
		if( ! empty( $args['referral_id'] ) ) {

			if( is_array( $args['referral_id'] ) ) {
				$referral_ids = implode( ',', $args['referral_id'] );
			} else {
				$referral_ids = intval( $args['referral_id'] );
			}	

			$where .= "WHERE `referral_id` IN( {$referral_ids} ) ";

		}

		$cache_key   = md5( 'affwp_visits_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'visits' );
		
		if( $count === false ) {
			$count = $wpdb->get_var( "SELECT COUNT(referral_id) FROM " . $this->table_name . "{$where};" );
			wp_cache_set( $cache_key, $count, 'visits' );
		}

		return $count;

	}


	public function create_table() {

		global $wpdb;

		if( $wpdb->get_var( "show tables like '{$this->table_name}'" ) == $this->table_name )
			return;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		`visit_id` bigint(20) NOT NULL AUTO_INCREMENT,
		`affiliate_id` bigint(20) NOT NULL,
		`referral_id` bigint(20) NOT NULL,
		`url` mediumtext NOT NULL,
		`ip` tinytext NOT NULL,
		`date` datetime NOT NULL,
		PRIMARY KEY  (visit_id),
		KEY affiliate_id (affiliate_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}