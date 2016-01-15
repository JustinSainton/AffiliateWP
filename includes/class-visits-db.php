<?php

class Affiliate_WP_Visits_DB extends Affiliate_WP_DB {

	public function __construct() {
		global $wpdb;

		if( defined( 'AFFILIATE_WP_NETWORK_WIDE' ) && AFFILIATE_WP_NETWORK_WIDE ) {
			// Allows a single visits table for the whole network
			$this->table_name  = 'affiliate_wp_visits';
		} else {
			$this->table_name  = $wpdb->prefix . 'affiliate_wp_visits';
		}
		$this->primary_key = 'visit_id';
		$this->version     = '1.0';
	}


	public function get_columns() {
		return array(
			'visit_id'     => '%d',
			'affiliate_id' => '%d',
			'referral_id'  => '%d',
			'url'          => '%s',
			'referrer'     => '%s',
			'campaign'     => '%s',
			'ip'           => '%s',
			'date'         => '%s',
		);
	}

	public function get_column_defaults() {
		return array(
			'affiliate_id' => 0,
			'referral_id'  => 0,
			'date'         => date( 'Y-m-d H:i:s' ),
			'referrer'     => ! empty( $_SERVER['HTTP_REFERER'] ) ? $_SERVER['HTTP_REFERER'] : '',
			'campaign'     => ! empty( $_REQUEST['campaign'] )    ? $_REQUEST['campaign']    : ''
		);
	}

	/**
	 * Retrieve visits from the database
	 *
	 * @access  public
	 * @since   1.0
	 * @param   array $args
	 * @param   bool  $count  Return only the total number of results found (optional)
	*/
	public function get_visits( $args = array(), $count = false ) {
		global $wpdb;

		$defaults = array(
			'number'          => 20,
			'offset'          => 0,
			'affiliate_id'    => 0,
			'referral_id'     => 0,
			'referral_status' => '',
			'campaign'        => '',
			'order'           => 'DESC',
			'orderby'         => 'visit_id'
		);

		$args = wp_parse_args( $args, $defaults );

		if( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

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

		// visits for specific campaign
		if( ! empty( $args['campaign'] ) ) {

			if( empty( $where ) ) {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}

			if( is_array( $args['campaign'] ) ) {
				$where .= " `campaign` IN(" . implode( ',', $args['campaign'] ) . ") ";
			} else {
				$where .= " `campaign` = '" . $args['campaign'] . "' ";
			}

		}

		// visits for specific referral status
		if ( ! empty( $args['referral_status'] ) ) {

			if ( 'converted' === $args['referral_status'] ) {
				$where .= "WHERE `referral_id` > 0";
			} elseif ( 'unconverted' === $args['referral_status'] ) {
				$where .= "WHERE `referral_id` = 0";
			}

		}

		// Visits for a date or date range
		if( ! empty( $args['date'] ) ) {

			if( is_array( $args['date'] ) ) {

				if( ! empty( $args['date']['start'] ) ) {

					$start = date( 'Y-m-d H:i:s', strtotime( $args['date']['start'] ) );

					if( ! empty( $where ) ) {

						$where .= " AND `date` >= '{$start}'";

					} else {

						$where .= " WHERE `date` >= '{$start}'";

					}

				}

				if( ! empty( $args['date']['end'] ) ) {

					$end = date( 'Y-m-d H:i:s', strtotime( $args['date']['end'] ) );

					if( ! empty( $where ) ) {

						$where .= " AND `date` <= '{$end}'";

					} else {

						$where .= " WHERE `date` <= '{$end}'";

					}

				}

			} else {

				$year  = date( 'Y', strtotime( $args['date'] ) );
				$month = date( 'm', strtotime( $args['date'] ) );
				$day   = date( 'd', strtotime( $args['date'] ) );

				if( empty( $where ) ) {
					$where .= " WHERE";
				} else {
					$where .= " AND";
				}

				$where .= " $year = YEAR ( date ) AND $month = MONTH ( date ) AND $day = DAY ( date )";
			}

		}

		// Build the search query
		if( ! empty( $args['search'] ) ) {

			if( empty( $where ) ) {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}

			if ( filter_var( $args['search'], FILTER_VALIDATE_IP ) ) {

				$where .= " `ip` LIKE '%%" . $args['search'] . "%%' ";

			} else {

				$where .= " ( `referrer` LIKE '%%" . $args['search'] . "%%' OR `url` LIKE '%%" . $args['search'] . "%%' ) ";

			}
		}

		$args['orderby'] = ! array_key_exists( $args['orderby'], $this->get_columns() ) ? $this->primary_key : $args['orderby'];

		$cache_key = ( true === $count ) ? md5( 'affwp_visits_count' . serialize( $args ) ) : md5( 'affwp_visits_' . serialize( $args ) );

		$results = wp_cache_get( $cache_key, 'visits' );

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

			wp_cache_set( $cache_key, $results, 'visits', 3600 );

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
		return $this->get_visits( $args, true );
	}

	public function add( $data = array() ) {

		if( ! empty( $data['url'] ) ) {
			$data['url'] = affwp_sanitize_visit_url( $data['url'] );
		}

		if( ! empty( $data['campaign'] ) ) {

			// Make sure campaign is not longer than 50 characters
			$data['campaign'] = substr( $data['campaign'], 0, 50 );

		}

		$visit_id = $this->insert( $data, 'visit' );


		affwp_increase_affiliate_visit_count( $data['affiliate_id'] );

		return $visit_id;
	}

	public function create_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE {$this->table_name} (
			visit_id bigint(20) NOT NULL AUTO_INCREMENT,
			affiliate_id bigint(20) NOT NULL,
			referral_id bigint(20) NOT NULL,
			url mediumtext NOT NULL,
			referrer mediumtext NOT NULL,
			campaign varchar(50) NOT NULL,
			ip tinytext NOT NULL,
			date datetime NOT NULL,
			PRIMARY KEY  (visit_id),
			KEY affiliate_id (affiliate_id)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}
