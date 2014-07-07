<?php

class Affiliate_WP_Creatives_DB extends Affiliate_WP_DB {

	public function __construct() {
		global $wpdb;

		if ( defined( 'AFFILIATE_WP_NETWORK_WIDE' ) && AFFILIATE_WP_NETWORK_WIDE ) {
			// Allows a single creatives table for the whole network
			$this->table_name  = 'affiliate_wp_creatives';
		} else {
			$this->table_name  = $wpdb->prefix . 'affiliate_wp_creatives';
		}
		$this->primary_key = 'creative_id';
		$this->version     = '1.0';
	}

	public function get_columns() {
		return array(
			'creative_id'  => '%d',
			'name'         => '%s',
			'url'          => '%s',
			'text'         => '%s',
			'image'        => '%s',
			'status'       => '%s',
			'date'         => '%s',
		);
	}

	public function get_column_defaults() {
		return array(
			'date' => date( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Retrieve creatives from the database
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function get_creatives( $args = array() ) {
		global $wpdb;

		$defaults = array(
			'number'      => 20,
			'offset'      => 0,
		);

		$args  = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where = '';

		// visits for specific affiliates
		if ( ! empty( $args['affiliate_id'] ) ) {

			if ( is_array( $args['affiliate_id'] ) ) {
				$affiliate_ids = implode( ',', $args['affiliate_id'] );
			} else {
				$affiliate_ids = intval( $args['affiliate_id'] );
			}

			$where .= "WHERE `affiliate_id` IN( {$affiliate_ids} ) ";

		}

		$cache_key = md5( 'affwp_creatives_' . serialize( $args ) );

		$visits = wp_cache_get( $cache_key, 'creatives' );
		
		if ( $visits === false ) {
			$visits = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  $this->table_name $where ORDER BY creative_id DESC LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ) );
			wp_cache_set( $cache_key, $visits, 'creatives', 3600 );
		}

		return $visits;

	}

	/**
	 * Add a new creative
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function add( $data = array() ) {

		$defaults = array(
			'status' => 'active',
			'date'   => current_time( 'mysql' ),
			'url'	=> '',
			'image' => '',
		);

		$args = wp_parse_args( $data, $defaults );

		$add  = $this->insert( $args, 'creative' );

		if ( $add ) {
			wp_cache_flush();

			do_action( 'affwp_insert_creative', $add );
			return $add;
		}

		return false;

	}

	/**
	 * Count the total number of creatives in the database
	 *
	 * @access  public
	 * @since   1.1.4
	*/
	public function count( $data = array() ) {
		// to do
	}

	public function create_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE {$this->table_name} (
			`creative_id` bigint(20) NOT NULL AUTO_INCREMENT,
			`name` tinytext NOT NULL,
			`url` varchar(255) NOT NULL,
			`text` tinytext NOT NULL,
			`image` varchar(255) NOT NULL,
			`status` tinytext NOT NULL,
			`date` datetime NOT NULL,
			PRIMARY KEY  (creative_id),
			KEY creative_id (creative_id)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}