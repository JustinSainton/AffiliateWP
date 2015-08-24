<?php

class Affiliate_WP_Creatives_DB extends Affiliate_WP_DB {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.2
	*/
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

	/**
	 * Database columns
	 *
	 * @access  public
	 * @since   1.2
	*/
	public function get_columns() {
		return array(
			'creative_id'  => '%d',
			'name'         => '%s',
			'description'  => '%s',
			'url'          => '%s',
			'text'         => '%s',
			'image'        => '%s',
			'status'       => '%s',
			'date'         => '%s',
		);
	}

	/**
	 * Default column values
	 *
	 * @access  public
	 * @since   1.2
	*/
	public function get_column_defaults() {
		return array(
			'date' => date( 'Y-m-d H:i:s' ),
		);
	}

	/**
	 * Retrieve creatives from the database
	 *
	 * @access  public
	 * @since   1.2
	 * @param   array $args
	 * @param   bool  $count  Return only the total number of results found (optional)
	 */
	public function get_creatives( $args = array(), $count = false ) {
		global $wpdb;

		$defaults = array(
			'number'  => 20,
			'offset'  => 0,
			'status'  => '',
			'orderby' => $this->primary_key,
			'order'   => 'ASC',
		);

		$args = wp_parse_args( $args, $defaults );

		if ( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where = '';

		if ( ! empty( $args['status'] ) ) {

			if( ! empty( $where ) ) {
				$where .= "AND `status` = '" . $args['status'] . "' ";
			} else {
				$where .= "WHERE `status` = '" . $args['status'] . "' ";
			}
		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? $this->primary_key : $args['orderby'];

		$cache_key = ( true === $count ) ? md5( 'affwp_creatives_count' . serialize( $args ) ) : md5( 'affwp_creatives_' . serialize( $args ) );

		$results = wp_cache_get( $cache_key, 'creatives' );

		if ( false === $results ) {

			if ( true === $count ) {

				$results = absint( $wpdb->get_var( "SELECT COUNT({$this->primary_key}) FROM {$this->table_name} {$where};" ) );

			} else {

				$results = $wpdb->get_results(
					$wpdb->prepare(
						"SELECT * FROM {$this->table_name} {$where} ORDER BY {$args['orderby']} {$args['order']} LIMIT %d, %d;",
						absint( $args['offset'] ),
						absint( $args['number'] )
					)
				);

			}

			wp_cache_set( $cache_key, $results, 'creatives', 3600 );

		}

		return $results;

	}

	/**
	 * Return the number of results found for a given query
	 *
	 * @param  array  $args
	 * @return int
	 */
	public function count( $args = array() ) {
		return $this->get_creatives( $args, true );
	}

	/**
	 * Add a new creative
	 *
	 * @access  public
	 * @since   1.2
	*/
	public function add( $data = array() ) {

		$defaults = array(
			'status' => 'active',
			'date'   => current_time( 'mysql' ),
			'url'	 => '',
			'image'  => '',
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

	public function create_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE {$this->table_name} (
			creative_id bigint(20) NOT NULL AUTO_INCREMENT,
			name tinytext NOT NULL,
			description longtext NOT NULL,
			url varchar(255) NOT NULL,
			text tinytext NOT NULL,
			image varchar(255) NOT NULL,
			status tinytext NOT NULL,
			date datetime NOT NULL,
			PRIMARY KEY  (creative_id),
			KEY creative_id (creative_id)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}
