<?php
/**
 * Affiiates Admin
 *
 * @package     AffiliateWP
 * @subpackage  Admin/Affiliates
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

include AFFILIATEWP_PLUGIN_DIR . 'includes/admin/visits/contextual-help.php';

function affwp_visits_admin() {


	$visits_table = new AffWP_Visits_Table();
	$visits_table->prepare_items();
	$from = ! empty( $_REQUEST['filter_from'] ) ? $_REQUEST['filter_from'] : '';
	$to   = ! empty( $_REQUEST['filter_to'] )   ? $_REQUEST['filter_to']   : '';
	?>
	<div class="wrap">
		<h2><?php _e( 'Visits', 'affiliate-wp' ); ?></h2>
		<?php do_action( 'affwp_affiliates_page_top' ); ?>
		<form id="affwp-visits-filter" method="get" action="<?php echo admin_url( 'admin.php?page=affiliate-wp' ); ?>">
			<?php $visits_table->search_box( __( 'Search', 'affiliate-wp' ), 'affwp-affiliates' ); ?>
			<span class="affwp-ajax-search-wrap">
				<input type="text" name="user_name" id="user_name" class="affwp-user-search" autocomplete="off" placeholder="<?php _e( 'Affiliate name', 'affiliate-wp' ); ?>" />
				<img class="affwp-ajax waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" style="display: none;"/>
			</span>
			<div id="affwp_user_search_results"></div>
			<input type="hidden" name="user_id" id="user_id" value=""/>
			<input type="hidden" name="page" value="affiliate-wp-visits" />
			<input type="text" class="affwp-datepicker" autocomplete="off" name="filter_from" placeholder="<?php _e( 'From - mm/dd/yyyy', 'affiliate-wp' ); ?>" value="<?php echo $from; ?>"/>
			<input type="text" class="affwp-datepicker" autocomplete="off" name="filter_to" placeholder="<?php _e( 'To - mm/dd/yyyy', 'affiliate-wp' ); ?>" value="<?php echo $to; ?>"/>&nbsp;

			<input type="submit" class="button" value="<?php _e( 'Filter', 'affiliate-wp' ); ?>"/>
			<?php $visits_table->views() ?>
			<?php $visits_table->display() ?>
		</form>
		<?php do_action( 'affwp_affiliates_page_bottom' ); ?>
	</div>
<?php

}

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * AffWP_Visits_Table Class
 *
 * Renders the Affiliates table on the Affiliates page
 *
 * @since 1.0
 */
class AffWP_Visits_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.0
	 */
	public $per_page = 30;

	/**
	 *
	 * Total number of affiliates
	 * @var string
	 * @since 1.0
	 */
	public $total_count;

	/**
	 * Active number of affiliates
	 *
	 * @var string
	 * @since 1.0
	 */
	public $active_count;

	/**
	 * Inactive number of affiliates
	 *
	 * @var string
	 * @since 1.0
	 */
	public $inactive_count;

	/**
	 * Get things started
	 *
	 * @since 1.0
	 * @uses AffWP_Visits_Table::get_visits_counts()
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		parent::__construct( array(
			'ajax'      => false
		) );

		$this->get_visits_counts();
	}

	/**
	 * Show the search field
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return svoid
	 */
	public function search_box( $text, $input_id ) {
		if ( empty( $_REQUEST['s'] ) && !$this->has_items() )
			return;

		$input_id = $input_id . '-search-input';

		if ( ! empty( $_REQUEST['orderby'] ) )
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		if ( ! empty( $_REQUEST['order'] ) )
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		?>
		<p class="search-box">
			<label class="screen-reader-text" for="<?php echo $input_id ?>"><?php echo $text; ?>:</label>
			<input type="search" id="<?php echo $input_id ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>
	<?php
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.0
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'url'          => __( 'Landing Page', 'affiliate-wp' ),
			'referrer'     => __( 'Referring URL', 'affiliate-wp' ),
			'affiliate'    => __( 'Affiliate', 'affiliate-wp' ),
			'referral_id'  => __( 'Referral ID', 'affiliate-wp' ),
			'ip'           => __( 'IP', 'affiliate-wp' ),
			'converted'    => __( 'Converted', 'affiliate-wp' ),
			'date'         => __( 'Date', 'affiliate-wp' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since 1.0
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'date'      => array( 'date', false ),
			'converted' => array( 'referral_id', false )
		);
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param array $item Contains all the data of the visit
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	function column_default( $visit, $column_name ) {
		switch( $column_name ){
			default:
				$value = isset( $visit->$column_name ) ? $visit->$column_name : '';
				break;
		}

		return $value;
	}

	/**
	 * Render the affiliate column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $referral Contains all the data for the checkbox column
	 * @return string The affiliate
	 */
	function column_affiliate( $visit ) {
		return '<a href="' . esc_url( admin_url( 'admin.php?page=affiliate-wp-visits&affiliate=' . $visit->affiliate_id ) ) . '">' . affiliate_wp()->affiliates->get_affiliate_name( $visit->affiliate_id ) . '</a>';
	}

	/**
	 * Render the referrer column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $referral Contains all the data for the checkbox column
	 * @return string Referring URL
	 */
	function column_referrer( $visit ) {

		$referrer = ! empty( $visit->referrer ) ? '<a href="' . esc_url( $visit->referrer ) . '" taret="_blank">' . $visit->referrer . '</a>' : __( 'Direct traffic', 'affiliate-wp' );
		return $referrer;
	}

	/**
	 * Render the converted column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $referral Contains all the data for the checkbox column
	 * @return string Converted status icon
	 */
	function column_converted( $visit ) {

		$converted = ! empty( $visit->referral_id ) ? 'yes' : 'no';
		return '<span class="visit-converted ' . $converted . '"><i></i></span>';
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.7.2
	 * @access public
	 */
	function no_items() {
		_e( 'No visits found.', 'affiliate-wp' );
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function process_bulk_action() {

	}

	/**
	 * Retrieve the discount code counts
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function get_visits_counts() {
		$this->total_count = affiliate_wp()->visits->count();
	}

	/**
	 * Retrieve all the data for all the Affiliates
	 *
	 * @access public
	 * @since 1.0
	 * @return array $visits_data Array of all the data for the Affiliates
	 */
	public function visits_data() {
		
		$page         = isset( $_GET['paged'] )     ? absint( $_GET['paged'] )          : 1;
		$user_id      = isset( $_GET['user_id'] )   ? absint( $_GET['user_id'] )        : false;
		$referral_id  = isset( $_GET['referral'] )  ? absint( $_GET['referral'] )       : false;
		$affiliate_id = isset( $_GET['affiliate'] ) ? absint( $_GET['affiliate'] )      : false;
		$order        = isset( $_GET['order'] )     ? $_GET['order']                    : 'DESC';
		$orderby      = isset( $_GET['orderby'] )   ? $_GET['orderby']                  : 'date';
		$search       = isset( $_GET['s'] )         ? sanitize_text_field( $_GET['s'] ) : '';

		$from = ! empty( $_REQUEST['filter_from'] ) ? $_REQUEST['filter_from'] : '';
		$to   = ! empty( $_REQUEST['filter_to'] )   ? $_REQUEST['filter_to']   : '';

		$date = array();
		if( ! empty( $from ) ) {
			$date['start'] = $from;
		}
		if( ! empty( $to ) ) {
			$date['end']   = $to . ' 23:59:59';
		}

		if( ! empty( $user_id ) && empty( $affiliate_id ) ) {

			$affiliate_id = affiliate_wp()->affiliates->get_column_by( 'affiliate_id', 'user_id', $user_id );

		}

		if ( strpos( $search, 'referral:' ) !== false ) {
			$referral_id = absint( trim( str_replace( 'referral:', '', $search ) ) );
			$search      = '';
		} elseif ( strpos( $search, 'affiliate:' ) !== false ) {
			$affiliate_id = absint( trim( str_replace( 'affiliate:', '', $search ) ) );
			$search       = '';
		}

		$visits = affiliate_wp()->visits->get_visits( array(
			'number'       => $this->per_page,
			'offset'       => $this->per_page * ( $page - 1 ),
			'affiliate_id' => $affiliate_id,
			'referral_id'  => $referral_id,
			'date'         => $date,
			'orderby'      => $orderby,
			'order'        => $order,
			'search'       => $search
		) );
		return $visits;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.0
	 * @uses AffWP_Visits_Table::get_columns()
	 * @uses AffWP_Visits_Table::get_sortable_columns()
	 * @uses AffWP_Visits_Table::process_bulk_action()
	 * @uses AffWP_Visits_Table::visits_data()
	 * @uses WP_List_Table::get_pagenum()
	 * @uses WP_List_Table::set_pagination_args()
	 * @return void
	 */
	public function prepare_items() {
		$per_page = $this->per_page;

		$columns = $this->get_columns();

		$hidden = array();

		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->process_bulk_action();

		$data = $this->visits_data();

		$current_page = $this->get_pagenum();

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $this->total_count,
				'per_page'    => $per_page,
				'total_pages' => ceil( $this->total_count / $per_page )
			)
		);
	}
}