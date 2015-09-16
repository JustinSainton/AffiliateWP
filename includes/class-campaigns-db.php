<?php

class Affiliate_WP_Campaigns_DB extends Affiliate_WP_DB {

	/**
	 * Setup our table name, primary key, and version
	 *
	 * This is a read-only VIEW of the visits table
	 *
	 * @param  int  $affiliate_id The ID of the affiliate to retrieve campaigns for
	 * @since  1.7
	 */
	public function __construct() {
		global $wpdb;

		if( defined( 'AFFILIATE_WP_NETWORK_WIDE' ) && AFFILIATE_WP_NETWORK_WIDE ) {
			// Allows a single visits table for the whole network
			$this->table_name  = 'affiliate_wp_campaigns';
		} else {
			$this->table_name  = $wpdb->prefix . 'affiliate_wp_campaigns';
		}
		$this->primary_key = 'affiliate_id';
		$this->version     = '1.0';
	}

	/**
	 * Retrieve campaigns and associated stats
	 *
	 * @param  int  $affiliate_id The ID of the affiliate to retrieve campaigns for
	 * @since  1.7
	 * @return array
	 */
	public function get_campaigns( $affiliate_id = 0 ) {

		global $wpdb;
		
		$cache_key = 'affwp_affiliate_campaigns_' . $affiliate_id;

		$results = wp_cache_get( $cache_key, 'campaigns' );

		if ( false === $results ) {
		
			$campaigns = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM $this->table_name WHERE affiliate_id = %d;", $affiliate_id ) );

			wp_cache_set( $cache_key, $campaigns, 'campaigns', 3600 );

		}

		return $campaigns;

	}

	/**
	 * Ensure insert method cannot be called
	 *
	 * @since  1.7
	 */
	public function insert( $data, $type = '' ) {
		_doing_it_wrong( 'insert', 'The AffiliateWP Campaigns table is a read-only VIEW. Data cannot be inserted.', '1.7' );
	}

	/**
	 * Ensure update method cannot be called
	 *
	 * @since  1.7
	 */
	public function update( $row_id, $data = array(), $where = '', $type = '' ) {
		_doing_it_wrong( 'update', 'The AffiliateWP Campaigns table is a read-only VIEW. Data cannot be updated.', '1.7' );
	}

	/**
	 * Ensure delete method cannot be called
	 *
	 * @since  1.7
	 */
	public function delete( $row_id = 0 ) {
		_doing_it_wrong( 'delete', 'The AffiliateWP Campaigns table is a read-only VIEW. Data cannot be deleted.', '1.7' );
	}

	/**
	 * Create the view
	 *
	 * @since  1.7
	 */
	public function create_view() {

		global $wpdb;

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		if( defined( 'AFFILIATE_WP_NETWORK_WIDE' ) && AFFILIATE_WP_NETWORK_WIDE ) {
			// Allows a single visits table for the whole network
			$visits_db  = 'affiliate_wp_visits';
		} else {
			$visits_db  = $wpdb->prefix . 'affiliate_wp_visits';
		}

		$sql = "CREATE OR REPLACE VIEW $this->table_name AS
				SELECT affiliate_id,
					campaign,
					COUNT(url) as visits,
					COUNT(DISTINCT url) as unique_visits,
					SUM(IF(referral_id<>0,1,0)) as referrals,
					ROUND((SUM(IF(referral_id<>0,1,0))/COUNT(url)) * 100, 2) as conversion_rate
				FROM $visits_db GROUP BY affiliate_id, campaign;";

		$wpdb->query( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}
}
