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
			'affiliate_id'=> '%d',
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
			'affiliate_id' => 0,
			'date'         => date( 'Y-m-d H:i:s' )
		);
	}

	/**
	 * Retrieve referrals from the database
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function get_referrals( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'number'       => 20,
			'offset'       => 0,
			'affiliate_id' => 0,
			'status'       => ''
		);

		$args  = wp_parse_args( $args, $defaults );

		$where = '';

		// referrals for specific affiliates
		if( ! empty( $args['affiliate_id'] ) ) {

			if( is_array( $args['affiliate_id'] ) ) {
				$affiliate_ids = implode( ',', $args['affiliate_id'] );
			} else {
				$affiliate_ids = intval( $args['affiliate_id'] );
			}	

			$where .= "WHERE `affiliate_id` IN( {$affiliate_ids} ) ";

		}

		if( ! empty( $args['status'] ) ) {

			if( ! empty( $where ) ) {
				$where .= "`status` = '" . $args['status'] . "' ";
			} else {
				$where .= "WHERE `status` = '" . $args['status'] . "' ";
			}
		}

		return $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  $this->table_name $where LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ) );

	}

	public function add( $data = array() ) {
		return $this->insert( $data, 'referral' );
	}

	/**
	 * Count the total number of affiliates in the database
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function count( $args = array() ) {

		global $wpdb;

		$where = '';

		// referrals for specific affiliates
		if( ! empty( $args['affiliate_id'] ) ) {

			if( is_array( $args['affiliate_id'] ) ) {
				$affiliate_ids = implode( ',', $args['affiliate_id'] );
			} else {
				$affiliate_ids = intval( $args['affiliate_id'] );
			}	

			$where .= " WHERE `affiliate_id` IN( {$affiliate_ids} ) ";

		}

		if( ! empty( $args['status'] ) ) {

			if( empty( $where ) ) {
				$where .= " WHERE";
			}

			if( is_array( $args['status'] ) ) {
				$where .= " `status` IN(" . implode( ',', $args['status'] ) . ") ";
			} else {
				$where .= " `status` = '" . $args['status'] . "' ";
			}

		}



		if( ! empty( $args['date'] ) ) {

			if( is_array( $args['date'] ) ) {

				$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );
				$end   = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

				if( ! empty( $where ) ) {

					$where .= " AND `date` >= '{$start}' AND `date` <= '{$end}'";
				
				} else {
					
					$where .= " WHERE `date` >= '{$start}' AND `date` <= '{$end}'";
	
				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				if( empty( $where ) ) {
					$where .= " WHERE";
				}

				$where .= " $year = YEAR ( date ) AND $month = MONTH ( date ) AND $day = DAY ( date )";
			}

		}

		$cache_key = md5( 'affwp_referrals_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'referrals' );
		
		if( $count === false ) {
			$count = $wpdb->get_var( "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$where};" );
			wp_cache_set( $cache_key, $count, $this->table_name );
		}

		return $count;

	}

	public function create_table() {

		global $wpdb;

		if( $wpdb->get_var( "show tables like '{$this->table_name}'" ) == $this->table_name )
			return;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		`referral_id` bigint(20) NOT NULL AUTO_INCREMENT,
		`affiliate_id` bigint(20) NOT NULL,
		`description` longtext NOT NULL,
		`status` tinytext NOT NULL,
		`amount` mediumtext NOT NULL,
		`ip` tinytext NOT NULL,
		`currency` char(3) NOT NULL,
		`custom` longtext NOT NULL,
		`reference` varchar(20) NOT NULL,
		`date` datetime NOT NULL,
		PRIMARY KEY  (referral_id),
		KEY affiliate_id (affiliate_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}