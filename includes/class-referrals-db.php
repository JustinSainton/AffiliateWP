<?php

class Affiliate_WP_Referrals_DB extends Affiliate_WP_DB  {

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
			'visit_id'    => '%d',
			'description' => '%s',
			'status'      => '%s',
			'amount'      => '%s',
			'ip'          => '%s',
			'currency'    => '%s',
			'custom'      => '%s',
			'context'     => '%s',
			'reference'   => '%d',
			'date'        => '%s',
		);
	}

	public function get_column_defaults() {
		return array(
			'affiliate_id' => 0,
			'date'         => date( 'Y-m-d H:i:s' ),
			'currency'     => affwp_get_currency()
		);
	}

	public function add( $data = array() ) {

		$defaults = array(
			'status' => 'pending'
		);

		$args = wp_parse_args( $data, $defaults );

		$add  = $this->insert( $args, 'referral' );

		if( $add ) {
			do_action( 'affwp_add_referral', $add );
			return $add;
		}

		return false;

	}

	/**
	 * Retrieve a referral by a specific field. Optionally let's you retreive via field that also has a specific context
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function get_by( $column, $row_id, $context = '' ) {
		global $wpdb;

		$and = '';
		if( ! empty( $context ) ) {
			$and = " AND context = '$context'";
		}

		return $wpdb->get_row( "SELECT * FROM $this->table_name WHERE $column = '$row_id'$and;" );
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
			'reference'    => '',
			'context'      => '',
			'status'       => '',
			'orderby'      => 'referral_id',
			'order'        => 'DESC',
			'search'       => false
		);

		$args  = wp_parse_args( $args, $defaults );

		if( $args['number'] < 1 ) {
			$args['number'] = 999999999999;
		}

		$where    = '';

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

			if( empty( $where ) ) {
				$where .= " WHERE";
			} else {
				$where .= " AND";
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
				} else {
					$where .= " AND";
				}

				$where .= " $year = YEAR ( date ) AND $month = MONTH ( date ) AND $day = DAY ( date )";
			}

		}

		if( ! empty( $args['reference'] ) ) {

			if( empty( $where ) ) {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}

			if( is_array( $args['reference'] ) ) {
				$where .= " `reference` IN(" . implode( ',', $args['reference'] ) . ") ";
			} else {
				if( ! empty( $args['search'] ) ) {
					$where .= " `reference` LIKE '%%" . $args['reference'] . "%%' ";
				} else {
					$where .= " `reference` = '" . $args['reference'] . "' ";
				}
			}

		}

		if( ! empty( $args['context'] ) ) {

			if( empty( $where ) ) {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}

			if( is_array( $args['context'] ) ) {
				$where .= " `context` IN(" . implode( ',', $args['context'] ) . ") ";
			} else {
				if( ! empty( $args['search'] ) ) {
					$where .= " `context` LIKE '%%" . $args['context'] . "%%' ";
				} else {
					$where .= " `context` = '" . $args['context'] . "' ";
				}
			}

		}

		$cache_key = md5( 'affwp_referrals_' . serialize( $args ) );

		$referrals = wp_cache_get( $cache_key, 'referrals' );
		
		if( $referrals === false ) {
			$referrals = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM  $this->table_name $where ORDER BY {$args['orderby']} {$args['order']} LIMIT %d,%d;", absint( $args['offset'] ), absint( $args['number'] ) ) );
			wp_cache_set( $cache_key, $referrals, 'referrals', 3600 );
		}

		return $referrals;

	}

	/**
	 * Get the total unpaid earnings
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function unpaid_earnings( $date = '', $affiliate_id = 0, $format = true ) {

		$args = array();
		$args['status'] = 'unpaid';
		$args['affiliate_id'] = $affiliate_id;

		if( ! empty( $date ) ) {

			switch( $date ) {

				case 'month' :
				
					$date = array(
						'start' => date( 'Y-m-01 00:00:00', current_time( 'timestamp' ) ),
						'end'   => date( 'Y-m-' . cal_days_in_month( CAL_GREGORIAN, date( 'n' ), date( 'Y' ) ) . ' 00:00:00', current_time( 'timestamp' ) ),
					);
					break;

			}

			$args['date'] = $date;
		}

		$referrals = $this->get_referrals( $args );

		$earnings  = array_sum( wp_list_pluck( $referrals, 'amount' ) );

		if( $format ) {
			$earnings = affwp_currency_filter( affwp_format_amount( $earnings ) );
		}

		return $earnings;

	}

	/**
	 * Count the total number of unpaid referrals
	 *
	 * @access  public
	 * @since   1.0
	*/
	public function unpaid_count( $date = '', $affiliate_id = 0 ) {

		$args = array();
		$args['status'] = 'unpaid';
		$args['affiliate_id'] = $affiliate_id;

		if( ! empty( $date ) ) {

			switch( $date ) {

				case 'month' :
				
					$date = array(
						'start' => date( 'Y-m-01 00:00:00', current_time( 'timestamp' ) ),
						'end'   => date( 'Y-m-' . cal_days_in_month( CAL_GREGORIAN, date( 'n' ), date( 'Y' ) ) . ' 00:00:00', current_time( 'timestamp' ) ),
					);
					break;

			}

			$args['date'] = $date;
		}

		return $this->count( $args );
	}

	/**
	 * Count the total number of referrals in the database
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
			} else {
				$where .= " AND";
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

				if( empty( $where ) ) {

					$where .= " WHERE `date` >= '{$start}' AND `date` <= '{$end}'";
				
				} else {
					
					$where .= " AND `date` >= '{$start}' AND `date` <= '{$end}'";
	
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

		if( ! empty( $args['context'] ) ) {

			if( empty( $where ) ) {
				$where .= " WHERE";
			} else {
				$where .= " AND";
			}

			if( is_array( $args['context'] ) ) {
				$where .= " `context` IN(" . implode( ',', $args['context'] ) . ") ";
			} else {
				$where .= " `context` = '" . $args['context'] . "' ";
			}

		}

		$cache_key = md5( 'affwp_referrals_count' . serialize( $args ) );

		$count = wp_cache_get( $cache_key, 'referrals' );
		
		if( $count === false ) {
			$count = $wpdb->get_var( "SELECT COUNT($this->primary_key) FROM " . $this->table_name . "{$where};" );
			wp_cache_set( $cache_key, $count, 'referrals', 3600 );
		}

		return absint( $count );

	}

	public function bulk_update_status( $referral_ids = array(), $status = '' ) {

		global $wpdb;

		if( empty( $referral_ids ) ) {
			return false;
		}

		if( empty( $status ) ) {
			return false;
		}

		$referral_ids = implode( ',', $referral_ids );

		// Not working yet
		$update = $wpdb->query( $wpdb->prepare( "UPDATE $this->table_name SET status = '%s' WHERE $this->primary_key IN(%s)", $status, $referral_ids ) );

		if( $update ) {
			return true;
		}
		return false;
	}

	public function create_table() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE " . $this->table_name . " (
		`referral_id` bigint(20) NOT NULL AUTO_INCREMENT,
		`affiliate_id` bigint(20) NOT NULL,
		`visit_id` bigint(20) NOT NULL,
		`description` longtext NOT NULL,
		`status` tinytext NOT NULL,
		`amount` mediumtext NOT NULL,
		`ip` tinytext NOT NULL,
		`currency` char(3) NOT NULL,
		`custom` longtext NOT NULL,
		`context` tinytext NOT NULL,
		`reference` mediumtext NOT NULL,
		`date` datetime NOT NULL,
		PRIMARY KEY  (referral_id),
		KEY affiliate_id (affiliate_id)
		) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}