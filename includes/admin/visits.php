<?php
/**
 * Affiiates Admin
 *
 * @package     Affiliate WP
 * @subpackage  Admin/Affiliates
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function affwp_visits_admin() {


	$affiliates_table = new AffWP_Visits_Table();
	$affiliates_table->prepare_items();
	?>
	<div class="wrap">
		<h2><?php _e( 'Visits', 'affiliate-wp' ); ?>
			<a href="<?php echo add_query_arg( array( 'affwp-action' => 'add_affiliate' ) ); ?>" class="add-new-h2"><?php _e( 'Add New', 'affiliate-wp' ); ?></a>
		</h2>
		<?php do_action( 'affwp_affiliates_page_top' ); ?>
		<form id="affwp-affiliates-filter" method="get" action="<?php echo admin_url( 'admin.php?page=affiliate-wp' ); ?>">
			<?php $affiliates_table->search_box( __( 'Search', 'affiliate-wp' ), 'affwp-affiliates' ); ?>

			<input type="hidden" name="page" value="affiliate-wp" />

			<?php $affiliates_table->views() ?>
			<?php $affiliates_table->display() ?>
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
			'cb'           => '<input type="checkbox" />',
			'ip'           => __( 'ID', 'affiliate-wp' ),
			'url'          => __( 'URL', 'affiliate-wp' ),
			'affiliate_id' => __( 'Affiliate ID', 'affiliate-wp' ),
			'referral_id'  => __( 'Referral ID', 'affiliate-wp' ),
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
			'name'   => array( 'name', false )
		);
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.0
	 *
	 * @param array $item Contains all the data of the affiliate
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	function column_default( $affiliate, $column_name ) {
		switch( $column_name ){
			default:
				$value = isset( $affiliate->$column_name ) ? $affiliate->$column_name : '';
				break;
		}

		return $value;
	}

	/**
	 * Render the Name Column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $item Contains all the data of the discount code
	 * @return string Data shown in the Name column
	 */
	function column_name( $affiliate ) {

		$base         = admin_url( 'admin.php?page=affiliate-wp&affiliate_id=' . $affiliate->affiliate_id );
		$row_actions  = array();
		$name         = get_userdata( $affiliate->user_id )->display_name; 

		$row_actions['edit'] = '<a href="' . add_query_arg( array( 'action' => 'edit_discount', 'affiliate_id' => $affiliate->affiliate_id ) ) . '">' . __( 'Edit', 'affiliate-wp' ) . '</a>';

		if( strtolower( $affiliate->status ) == 'active' )
			$row_actions['deactivate'] = '<a href="' . add_query_arg( array( 'action' => 'deactivate_affiliate', 'affiliate_id' => $affiliate->affiliate_id ) ) . '">' . __( 'Deactivate', 'affiliate-wp' ) . '</a>';
		else
			$row_actions['activate'] = '<a href="' . add_query_arg( array( 'action' => 'activate_affiliate', 'affiliate_id' => $affiliate->affiliate_id ) ) . '">' . __( 'Activate', 'affiliate-wp' ) . '</a>';

		$row_actions['delete'] = '<a href="' . wp_nonce_url( add_query_arg( array( 'action' => 'delete_affiliate', 'affiliate_id' => $affiliate->affiliate_id ) ), 'affwp_delete_affiliate_nonce' ) . '">' . __( 'Delete', 'affiliate-wp' ) . '</a>';

		$row_actions = apply_filters( 'affwp_affiliate_row_actions', $row_actions, $affiliate );

		return $name . $this->row_actions( $row_actions );
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $affiliate Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	function column_cb( $affiliate ) {
		return '<input type="checkbox" name="visit_id[]" value="' . $affiliate->visit_id . '" />';
	}

	/**
	 * Render the affiliate column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $referral Contains all the data for the checkbox column
	 * @return string The affiliate
	 */
	function column_affiliate( $referral ) {
		return '<a href="' . admin_url( 'admin.php?page=affiliate-wp&affiliate=' . $referral->affiliate_id ) . '">' . $referral->affiliate_id . '</a>';
	}

	/**
	 * Render the actions column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $referral Contains all the data for the actions column
	 * @return string The actions HTML
	 */
	function column_actions( $referral ) {
		return 'Actions here';
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
	 * Retrieve the bulk actions
	 *
	 * @access public
	 * @since 1.0
	 * @return array $actions Array of the bulk actions
	 */
	public function get_bulk_actions() {
		$actions = array(
			'activate'   => __( 'Activate', 'affiliate-wp' ),
			'deactivate' => __( 'Deactivate', 'affiliate-wp' ),
			'delete'     => __( 'Delete', 'affiliate-wp' )
		);

		return $actions;
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function process_bulk_action() {
		$ids = isset( $_GET['discount'] ) ? $_GET['discount'] : false;

		if ( ! is_array( $ids ) )
			$ids = array( $ids );

		foreach ( $ids as $id ) {
			if ( 'delete' === $this->current_action() ) {

			}
			if ( 'activate' === $this->current_action() ) {

			}
			if ( 'deactivate' === $this->current_action() ) {

			}
		}

	}

	/**
	 * Retrieve the discount code counts
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function get_visits_counts() {
		$this->total_count    = affiliate_wp()->visits->count();
	}

	/**
	 * Retrieve all the data for all the Affiliates
	 *
	 * @access public
	 * @since 1.0
	 * @return array $visits_data Array of all the data for the Affiliates
	 */
	public function visits_data() {
		
		$page   = isset( $_GET['paged'] )  ? absint( $_GET['paged'] ) : 1;

		$visits  = affiliate_wp()->visits->get_visits( array(
			'number' => $this->per_page,
			'offset' => $this->per_page * ( $page - 1 ),
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