<?php

class Affiliate_WP_DB {

	public $table_name;

	public $version;

	public $primary_key;

	public function __construct() {

		global $wpdb;

		$this->table_name  = $wpdb->prefix . 'affiliate_wp_affiliates';
		$this->primary_key = 'affiliate_id';
		$this->version     = '1.0';

	}


	public function get_columns() {
		return array(
			'affiliate_id' => '%d',
			'user_id'      => '%d',
			'status'       => '%s',
			'earnings'     => '%s',
			'referrals'    => '%d',
			'visits'       => '%d',
		);
	}

	public function get_column_defaults() {
		return array(
			'user_id'  => get_current_user_id()
		);
	}

	/**
	 * Retrieve affiliates from the database
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function get_affiliates( $args = array() ) {

		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'user_id' => 0,
			'status'  => ''
		);

		$args  = wp_parse_args( $args, $defaults );

		$where = '';

		// affiliates for specific users
		if( ! empty( $args['user_id'] ) ) {

			if( is_array( $args['user_id'] ) ) {
				$user_ids = implode( ',', $args['user_id'] );
			} else {
				$user_ids = intval( $args['user_id'] );
			}	

			$where .= "WHERE `user_id` IN( {$user_ids} ) ";

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
	
	public function get( $row_id ) {
		global $wpdb;
		return $wpdb->get_row( "SELECT * FROM $this->table_name WHERE affiliate_id = $row_id;" );
	}

	public function get_by( $column, $row_id ) {
		global $wpdb;
		return $wpdb->get_row( "SELECT * FROM $this->table_name WHERE $column = $row_id;" );
	}

	public function get_column( $column, $row_id ) {
		global $wpdb;
		return $wpdb->get_var( "SELECT $column FROM $this->table_name WHERE $this->primary_key = $row_id;" );
	}

	public function add( $data = array() ) {
		return $this->insert( $data, 'affiliate' );
	}
	
	public function insert( $data, $type = 'affiliate' ) {
		global $wpdb;

		// Set default values
		$data = wp_parse_args( $data, $this->get_column_defaults() );

		do_action( 'affwp_pre_insert_' . $type, $data );

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		$wpdb->insert( $this->table_name, $data, $column_formats );

		do_action( 'affwp_post_insert_' . $type, $wpdb->insert_id, $data );

		return $wpdb->insert_id;
	}

	public function update( $row_id, $data = array() ) {
		global $wpdb;        

		// Row ID must be positive integer
		$row_id = absint( $row_id );     
		if( empty( $row_id ) )
			return false;

		// Initialise column format array
		$column_formats = $this->get_columns();

		// Force fields to lower case
		$data = array_change_key_case ( $data );

		// White list columns
		$data = array_intersect_key( $data, $column_formats );

		// Reorder $column_formats to match the order of columns given in $data
		$data_keys = array_keys( $data );
		$column_formats = array_merge( array_flip( $data_keys ), $column_formats );

		if ( false === $wpdb->update( $this->table_name, $data, array( $this->primary_key => $row_id ), $column_formats ) ) {
			return false;
		}

		return true;
	}

	public function delete( $row_id = 0 ) {

		global $wpdb;

		// Row ID must be positive integer
		$row_id = absint( $row_id );     
		if( empty( $row_id ) )
			return false;

		if ( false === $wpdb->query( $wpdb->prepare( "DELETE FROM $this->table_name WHERE $this->primary_key = %d", $row_id ) ) ) {
			return false;
		}

		return true;
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

		if( ! empty( $args['status'] ) ) {

			if( is_array( $args['status'] ) ) {
				$where .= " WHERE `status` IN(" . implode( ',', $args['status'] ) . ") ";
			} else {
				$where .= " WHERE `status` = '" . $args['status'] . "' ";
			}

		}

		$cache_key = md5( 'affwp_affiliates_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'affiliates' );
		
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
		`affiliate_id` bigint(20) NOT NULL AUTO_INCREMENT,
		`user_id` bigint(20) NOT NULL,
		`status` tinytext NOT NULL,
		`earnings` mediumtext NOT NULL,
		`referrals` bigint(20) NOT NULL,
		`visits` bigint(20) NOT NULL,
		PRIMARY KEY  (affiliate_id),
		KEY user_id (user_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

}