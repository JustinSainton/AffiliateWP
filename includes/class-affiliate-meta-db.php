<?php

class Affiliate_WP_Affiliate_Meta_DB extends Affiliate_WP_DB {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.6
	*/
	public function __construct() {
		global $wpdb;

		if( defined( 'AFFILIATE_WP_NETWORK_WIDE' ) && AFFILIATE_WP_NETWORK_WIDE ) {
			// Allows a single affiliate meta table for the whole network
			$this->table_name  = 'affiliate_wp_affiliatemeta';
		} else {
			$this->table_name  = $wpdb->prefix . 'affiliate_wp_affiliatemeta';
		}
		$this->primary_key = 'meta_id';
		$this->version     = '1.0';

		add_action( 'init', array( $this, 'register_table' ) );

	}

	/**
	 * Register the table with $wpdb so the metadata api can find it
	 *
	 * @access  public
	 * @since   1.6
	*/
	public function register_table() {
		global $wpdb;
		$wpdb->affiliatemeta = $this->table_name;
	}

	/**
	 * Retrieve affiliate meta field for a affiliate.
	 *
	 * @param   int    $affiliate_id  Affiliate ID.
	 * @param   string $meta_key      The meta key to retrieve.
	 * @param   bool   $single        Whether to return a single value.
	 * @return  mixed                 Will be an array if $single is false. Will be value of meta data field if $single is true.
	 *
	 * @access  public
	 * @since   1.6
	 */
	function get_meta( $affiliate_id = 0, $meta_key = '', $single = false ) {
		return get_metadata( 'affiliate', $affiliate_id, $meta_key, $single );
	}

	/**
	 * Add meta data field to a affiliate.
	 *
	 * @param   int    $affiliate_id  Affiliate ID.
	 * @param   string $meta_key      Metadata name.
	 * @param   mixed  $meta_value    Metadata value.
	 * @param   bool   $unique        Optional, default is false. Whether the same key should not be added.
	 * @return  bool                  False for failure. True for success.
	 *
	 * @access  public
	 * @since   1.6
	 */
	function add_meta( $affiliate_id = 0, $meta_key = '', $meta_value, $unique = false ) {
		return add_metadata( 'affiliate', $affiliate_id, $meta_key, $meta_value, $unique );
	}

	/**
	 * Update affiliate meta field based on affiliate ID.
	 *
	 * Use the $prev_value parameter to differentiate between meta fields with the
	 * same key and affiliate ID.
	 *
	 * If the meta field for the affiliate does not exist, it will be added.
	 *
	 * @param   int    $affiliate_id  Affiliate ID.
	 * @param   string $meta_key      Metadata key.
	 * @param   mixed  $meta_value    Metadata value.
	 * @param   mixed  $prev_value    Optional. Previous value to check before removing.
	 * @return  bool                  False on failure, true if success.
	 *
	 * @access  public
	 * @since   1.6
	 */
	function update_meta( $affiliate_id = 0, $meta_key = '', $meta_value, $prev_value = '' ) {
		return update_metadata( 'affiliate', $affiliate_id, $meta_key, $meta_value, $prev_value );
	}

	/**
	 * Remove metadata matching criteria from a affiliate.
	 *
	 * You can match based on the key, or key and value. Removing based on key and
	 * value, will keep from removing duplicate metadata with the same key. It also
	 * allows removing all metadata matching key, if needed.
	 *
	 * @param   int    $affiliate_id  Affiliate ID.
	 * @param   string $meta_key      Metadata name.
	 * @param   mixed  $meta_value    Optional. Metadata value.
	 * @return  bool                  False for failure. True for success.
	 *
	 * @access  public
	 * @since   1.6
	 */
	function delete_meta( $affiliate_id = 0, $meta_key = '', $meta_value = '' ) {
		return delete_metadata( 'affiliate', $affiliate_id, $meta_key, $meta_value );
	}

	/**
	 * Create the table
	 *
	 * @access  public
	 * @since   1.6
	*/
	public function create_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		$sql = "CREATE TABLE {$this->table_name} (
			meta_id bigint(20) NOT NULL AUTO_INCREMENT,
			affiliate_id bigint(20) NOT NULL DEFAULT '0',
			meta_key varchar(255) DEFAULT NULL,
			meta_value longtext,
			PRIMARY KEY  (meta_id),
			KEY affiliate_id (affiliate_id),
			KEY meta_key (meta_key)
			) CHARACTER SET utf8 COLLATE utf8_general_ci;";

		dbDelta( $sql );

		update_option( $this->table_name . '_db_version', $this->version );
	}

}
