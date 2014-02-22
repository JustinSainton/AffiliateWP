<?php
/**
 * Referrals Admin
 *
 * @package     Affiliate WP
 * @subpackage  Admin/Referrals
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function affwp_referrals_admin() {

	if( isset( $_GET['action'] ) && 'add_referral' == $_GET['action'] ) {

		include AFFILIATEWP_PLUGIN_DIR . 'includes/admin/referrals/new.php';

	} else {

		$referrals_table = new AffWP_Referrals_Table();
		$referrals_table->prepare_items();
		?>
		<div class="wrap">
			<h2><?php _e( 'Referrals', 'affiliate-wp' ); ?>
				<a href="<?php echo add_query_arg( array( 'action' => 'add_referral' ) ); ?>" class="add-new-h2"><?php _e( 'Add New', 'affiliate-wp' ); ?></a>
			</h2>
			<?php do_action( 'affwp_referrals_page_top' ); ?>
			<form id="affwp-referrals-filter" method="get" action="<?php echo admin_url( 'admin.php?page=affiliate-wp-referrals' ); ?>">
				<?php $referrals_table->search_box( __( 'Search', 'affiliate-wp' ), 'affwp-referrals' ); ?>

				<input type="hidden" name="page" value="affiliate-wp-referrals" />

				<?php $referrals_table->views() ?>
				<?php $referrals_table->display() ?>
			</form>
			<?php do_action( 'affwp_referrals_page_bottom' ); ?>
		</div>
	<?php
	}

}

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * AffWP_Referrals_Table Class
 *
 * Renders the Affiliates table on the Affiliates page
 *
 * @since 1.0
 */
class AffWP_Referrals_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var int
	 * @since 1.0
	 */
	public $per_page = 30;

	/**
	 *
	 * Total number of referrals
	 * @var int
	 * @since 1.0
	 */
	public $total_count;

	/**
	 * Number of paid referrals
	 *
	 * @var int
	 * @since 1.0
	 */
	public $paid_count;

	/**
	 * Number of pending referrals
	 *
	 * @var int
	 * @since 1.0
	 */
	public $pending_count;

	/**
	 * Number of rejected referrals
	 *
	 * @var int
	 * @since 1.0
	 */
	public $rejected_count;

	/**
	 * Get things started
	 *
	 * @since 1.0
	 * @uses AffWP_Referrals_Table::get_affiliate_counts()
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		parent::__construct( array(
			'ajax'      => false
		) );

		$this->get_referral_counts();
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
	 * Retrieve the view types
	 *
	 * @access public
	 * @since 1.0
	 * @return array $views All the views available
	 */
	public function get_views() {
		$base           = admin_url( 'admin.php?page=affiliate-wp-referrals' );
		$current        = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$paid_count     = '&nbsp;<span class="count">(' . $this->paid_count . ')</span>';
		$pending_count  = '&nbsp;<span class="count">(' . $this->pending_count . ')</span>';
		$rejected_count = '&nbsp;<span class="count">(' . $this->rejected_count . ')</span>';

		$views = array(
			'all'      => sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'affiliate-wp' ) . $total_count ),
			'paid'	   => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'paid', $base ), $current === 'paid' ? ' class="current"' : '', __( 'Paid', 'affiliate-wp' ) . $paid_count ),
			'pending'  => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'pending', $base ), $current === 'pending' ? ' class="current"' : '', __( 'Pending', 'affiliate-wp' ) . $pending_count ),
			'rejected' => sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'rejected', $base ), $current === 'rejected' ? ' class="current"' : '', __( 'Rejected', 'affiliate-wp' ) . $rejected_count ),
		);

		return $views;
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
			'cb'        => '<input type="checkbox" />',
			'amount'    => __( 'Amount', 'affiliate-wp' ),
			'affiliate' => __( 'Affiliate', 'affiliate-wp' ),
			'date'      => __( 'Date', 'affiliate-wp' ),
			'actions'   => __( 'Actions', 'affiliate-wp' ),
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
	public function column_default( $referral, $column_name ) {
		switch( $column_name ){
			
			case 'date' :
				$value = date_i18n( get_option( 'date_format' ), strtotime( $referral->date ) );
				break;

			default:
				$value = isset( $referral->$column_name ) ? $referral->$column_name : '';
				break;
		}

		return $value;
	}

	/**
	 * Render the checkbox column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $referral Contains all the data for the checkbox column
	 * @return string Displays a checkbox
	 */
	public function column_cb( $referral ) {
		return '<input type="checkbox" name="referral_id[]" value="' . absint( $referral->referral_id ) . '" />';
	}

	/**
	 * Render the affiliate column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $referral Contains all the data for the checkbox column
	 * @return string The affiliate
	 */
	public function column_affiliate( $referral ) {
		return '<a href="' . admin_url( 'admin.php?page=affiliate-wp&action=view_affiliateaffiliate=' . $referral->affiliate_id ) . '">' . $referral->affiliate_id . '</a>';
	}

	/**
	 * Render the actions column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $referral Contains all the data for the actions column
	 * @return string The actions HTML
	 */
	public function column_actions( $referral ) {
		
		$action_links   = array();
		
		if( 'paid' == affwp_get_referral_status( $referral ) ) {
			
			$action_links[] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'mark_as_unpaid', 'referral' => $referral->referral_id ) ) ) . '" class="mark-as-paid">' . __( 'Mark as Unpaid', 'affiliate-wp' ) . '</a>';
	
		} else {

			$action_links[] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'mark_as_paid', 'referral' => $referral->referral_id ) ) ) . '" class="mark-as-paid">' . __( 'Mark as Paid', 'affiliate-wp' ) . '</a>';
			
			if( 'rejected' == affwp_get_referral_status( $referral ) ) {
			
				$action_links[] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'accept', 'referral' => $referral->referral_id ) ) ) . '" class="reject">' . __( 'Accept', 'affiliate-wp' ) . '</a>';
			
			} else {
			
				$action_links[] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'reject', 'referral' => $referral->referral_id ) ) ) . '" class="reject">' . __( 'Reject', 'affiliate-wp' ) . '</a>';
			
			}

		}
		
		$action_links[] = '<span class="trash"><a href="' . esc_url( add_query_arg( array( 'action' => 'delete', 'referral' => $referral->referral_id ) ) ) . '" class="delete">' . __( 'Delete', 'affiliate-wp' ) . '</a></span>';
		
		$action_links   = array_unique( apply_filters( 'affwp_referral_action_links', $action_links ) );

		return '<div class="action-links">' . implode( ' | ', $action_links ) . '</div>';
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.7.2
	 * @access public
	 */
	public function no_items() {
		_e( 'No referrals found.', 'affiliate-wp' );
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
			'delete'         => __( 'Delete', 'affiliate-wp' ),
			'reject'         => __( 'Reject', 'affiliate-wp' ),
			'mark_as_paid'   => __( 'Mark as Paid', 'affiliate-wp' ),
			'mark_as_unpaid' => __( 'Mark as Unpaid', 'affiliate-wp' ),
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
		$ids = isset( $_GET['referral'] ) ? absint( $_GET['referral'] ) : false;

		if ( ! is_array( $ids ) )
			$ids = array( $ids );

		foreach ( $ids as $id ) {
			
			if ( 'delete' === $this->current_action() ) {
				affwp_delete_referral( $id );
			}
			
			if ( 'reject' === $this->current_action() ) {
				affwp_set_referral_status( $id, 'rejected' );
			}

			if ( 'accept' === $this->current_action() ) {
				affwp_set_referral_status( $id, 'pending' );
			}

			if ( 'mark_as_paid' === $this->current_action() ) {
				affwp_set_referral_status( $id, 'paid' );
			}

			if ( 'mark_as_unpaid' === $this->current_action() ) {
				affwp_set_referral_status( $id, 'pending' );
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
	public function get_referral_counts() {
		$this->paid_count     = affiliate_wp()->referrals->count( array( 'status' => 'paid' ) );
		$this->pending_count  = affiliate_wp()->referrals->count( array( 'status' => 'pending' ) );
		$this->rejected_count = affiliate_wp()->referrals->count( array( 'status' => 'rejected' ) );
		$this->total_count    = $this->paid_count + $this->pending_count + $this->rejected_count;
	}

	/**
	 * Retrieve all the data for all the Affiliates
	 *
	 * @access public
	 * @since 1.0
	 * @return array $referrals_data Array of all the data for the Affiliates
	 */
	public function referrals_data() {
		
		$page      = isset( $_GET['paged'] )  ? absint( $_GET['paged'] ) : 1;
		$status    = isset( $_GET['status'] ) ? $_GET['status'] : ''; 
		$affiliate = isset( $_GET['affiliate'] ) ? $_GET['affiliate'] : ''; 

		$referrals  = affiliate_wp()->referrals->get_referrals( array(
			'number'       => $this->per_page,
			'offset'       => $this->per_page * ( $page - 1 ),
			'status'       => $status,
			'affiliate_id' => $affiliate
		) );
		return $referrals;
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.0
	 * @uses AffWP_Referrals_Table::get_columns()
	 * @uses AffWP_Referrals_Table::get_sortable_columns()
	 * @uses AffWP_Referrals_Table::process_bulk_action()
	 * @uses AffWP_Referrals_Table::referrals_data()
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

		$data = $this->referrals_data();

		$current_page = $this->get_pagenum();

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch( $status ) {
			case 'paid':
				$total_items = $this->paid_count;
				break;
			case 'pending':
				$total_items = $this->pending_count;
				break;
			case 'rejected':
				$total_items = $this->rejected_count;
				break;
			case 'any':
				$total_items = $this->total_count;
				break;
		}

		$this->items = $data;

		$this->set_pagination_args( array(
				'total_items' => $total_items,
				'per_page'    => $per_page,
				'total_pages' => ceil( $total_items / $per_page )
			)
		);
	}
}