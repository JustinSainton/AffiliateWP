<?php
/**
 * Creatives Admin
 *
 * @package     AffiliateWP
 * @subpackage  Admin/Affiliates
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.2
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function affwp_creatives_admin() {

	if ( isset( $_GET['action'] ) && 'view_creative' == $_GET['action'] ) {

		include AFFILIATEWP_PLUGIN_DIR . 'includes/admin/creatives/view.php';

	} else if ( isset( $_GET['action'] ) && 'add_creative' == $_GET['action'] ) {

		include AFFILIATEWP_PLUGIN_DIR . 'includes/admin/creatives/new.php';

	} else if ( isset( $_GET['action'] ) && 'edit_creative' == $_GET['action'] ) {

		include AFFILIATEWP_PLUGIN_DIR . 'includes/admin/creatives/edit.php';

	} else if( isset( $_GET['action'] ) && 'delete' == $_GET['action'] ) {

		include AFFILIATEWP_PLUGIN_DIR . 'includes/admin/creatives/delete.php';

	} else {

		$creatives_table = new AffWP_Creatives_Table();
		$creatives_table->prepare_items();
	?>
	<div class="wrap">
			<h2><?php _e( 'Creatives', 'affiliate-wp' ); ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'affwp_notice' => false, 'action' => 'add_creative' ) ) ); ?>" class="add-new-h2"><?php _e( 'Add New', 'affiliate-wp' ); ?></a>
			</h2>
			<?php do_action( 'affwp_affiliates_page_top' ); ?>
			<form id="affwp-creatives-filter" method="get" action="<?php echo admin_url( 'admin.php?page=affiliate-wp-creatives' ); ?>">

				<input type="hidden" name="page" value="affiliate-wp-creatives" />

				<?php $creatives_table->views() ?>
				<?php $creatives_table->display() ?>
			</form>

			<?php do_action( 'affwp_affiliates_page_bottom' ); ?>
		</div>

<?php
	}
}

// Load WP_List_Table if not loaded
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

/**
 * AffWP_Creatives_Table Class
 *
 * Renders the Affiliates table on the Affiliates page
 *
 * @since 1.2
 */
class AffWP_Creatives_Table extends WP_List_Table {

	/**
	 * Number of results to show per page
	 *
	 * @var string
	 * @since 1.2
	 */
	public $per_page = 30;

	/**
	 *
	 * Total number of creatives
	 * @var string
	 * @since 1.2
	 */
	public $total_count;

	/**
	 * Active number of creatives
	 *
	 * @var string
	 * @since 1.2
	 */
	public $active_count;

	/**
	 * Inactive number of creatives
	 *
	 * @var string
	 * @since 1.2
	 */
	public $inactive_count;

	/**
	 * Get things started
	 *
	 * @since 1.2
	 * @uses AffWP_Creatives_Table::get_creative_counts()
	 * @see WP_List_Table::__construct()
	 */
	public function __construct() {
		global $status, $page;

		parent::__construct( array(
			'ajax'      => false
		) );

		$this->get_creative_counts();
	}

	/**
	 * Retrieve the view types
	 *
	 * @access public
	 * @since 1.0
	 * @return array $views All the views available
	 */
	public function get_views() {
		$base           = admin_url( 'admin.php?page=affiliate-wp-creatives' );

		$current        = isset( $_GET['status'] ) ? $_GET['status'] : '';
		$total_count    = '&nbsp;<span class="count">(' . $this->total_count    . ')</span>';
		$active_count   = '&nbsp;<span class="count">(' . $this->active_count . ')</span>';
		$inactive_count = '&nbsp;<span class="count">(' . $this->inactive_count  . ')</span>';

		$views = array(
			'all'		=> sprintf( '<a href="%s"%s>%s</a>', remove_query_arg( 'status', $base ), $current === 'all' || $current == '' ? ' class="current"' : '', __('All', 'affiliate-wp') . $total_count ),
			'active'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'active', $base ), $current === 'active' ? ' class="current"' : '', __('Active', 'affiliate-wp') . $active_count ),
			'inactive'	=> sprintf( '<a href="%s"%s>%s</a>', add_query_arg( 'status', 'inactive', $base ), $current === 'inactive' ? ' class="current"' : '', __('Inactive', 'affiliate-wp') . $inactive_count ),
		);

		return $views;
	}

	/**
	 * Retrieve the table columns
	 *
	 * @access public
	 * @since 1.2
	 * @return array $columns Array of all the list table columns
	 */
	public function get_columns() {
		$columns = array(
			'name'       => __( 'Name', 'affiliate-wp' ),
			'url'        => __( 'URL', 'affiliate-wp' ),
			'shortcode'  => __( 'Shortcode', 'affiliate-wp' ),
			'status'     => __( 'Status', 'affiliate-wp' ),
			'actions'    => __( 'Actions', 'affiliate-wp' ),
		);

		return $columns;
	}

	/**
	 * Retrieve the table's sortable columns
	 *
	 * @access public
	 * @since 1.2
	 * @return array Array of all the sortable columns
	 */
	public function get_sortable_columns() {
		return array(
			'name'   => array( 'name', false ),
			'status' => array( 'status', false ),
		);
	}

	/**
	 * This function renders most of the columns in the list table.
	 *
	 * @access public
	 * @since 1.2
	 *
	 * @param array $creative Contains all the data of the creatives
	 * @param string $column_name The name of the column
	 *
	 * @return string Column Name
	 */
	function column_default( $creative, $column_name ) {
		switch( $column_name ){
			default:
				$value = isset( $creative->$column_name ) ? $creative->$column_name : '';
				break;
		}

		return $value;
	}

	/**
	 * Render the URL column
	 *
	 * @access public
	 * @since 1.2
	 * @return string URL
	 */
	function column_url( $creative ) {
		return $creative->url;
	}

	/**
	 * Render the shortcode column
	 *
	 * @access public
	 * @since 1.2
	 * @return string Shortcode for creative
	 */
	function column_shortcode( $creative ) {
		return '[affiliate_creative id="' . $creative->creative_id . '"]';
	}

	/**
	 * Render the actions column
	 *
	 * @access public
	 * @since 1.2
	 * @param array $creative Contains all the data for the creative column
	 * @return string action links
	 */
	function column_actions( $creative ) {

		$row_actions['edit'] = '<a href="' . esc_url( add_query_arg( array( 'affwp_notice' => false, 'action' => 'edit_creative', 'creative_id' => $creative->creative_id ) ) ) . '">' . __( 'Edit', 'affiliate-wp' ) . '</a>';

		if ( strtolower( $creative->status ) == 'active' ) {
			$row_actions['deactivate'] = '<a href="' . esc_url( add_query_arg( array( 'affwp_notice' => 'creative_deactivated', 'action' => 'deactivate', 'creative_id' => $creative->creative_id ) ) ) . '">' . __( 'Deactivate', 'affiliate-wp' ) . '</a>';
		} else {
			$row_actions['activate'] = '<a href="' . esc_url( add_query_arg( array( 'affwp_notice' => 'creative_activated', 'action' => 'activate', 'creative_id' => $creative->creative_id ) ) ) . '">' . __( 'Activate', 'affiliate-wp' ) . '</a>';
		}

		$row_actions['delete'] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'delete', 'creative_id' => $creative->creative_id, 'affwp_notice' => false ) ) ) . '">' . __( 'Delete', 'affiliate-wp' ) . '</a>';

		$row_actions = apply_filters( 'affwp_creative_row_actions', $row_actions, $creative );

		return $this->row_actions( $row_actions, true );

	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.2
	 * @access public
	 */
	function no_items() {
		_e( 'No creatives found.', 'affiliate-wp' );
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since 1.2
	 * @return void
	 */
	public function process_bulk_action() {
		$ids = isset( $_GET['creative_id'] ) ? $_GET['creative_id'] : false;

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$ids = array_map( 'absint', $ids );

		if ( empty( $ids ) ) {
			return;
		}

		foreach ( $ids as $id ) {

			if ( 'delete' === $this->current_action() ) {
				affiliate_wp()->creatives->delete( $id );
			}

			if ( 'activate' === $this->current_action() ) {
				affwp_set_creative_status( $id, 'active' );
			}

			if ( 'deactivate' === $this->current_action() ) {
				affwp_set_creative_status( $id, 'inactive' );
			}

		}

	}

	/**
	 * Retrieve the creative counts
	 *
	 * @access public
	 * @since 1.2
	 * @return void
	 */
	public function get_creative_counts() {
		$this->active_count   = affiliate_wp()->creatives->count( array( 'status' => 'active' ) );
		$this->inactive_count = affiliate_wp()->creatives->count( array( 'status' => 'inactive' ) );
		$this->total_count    = $this->active_count + $this->inactive_count;
	}

	/**
	 * Retrieve all the data for all the Creatives
	 *
	 * @access public
	 * @since 1.2
	 * @return array $creatives_data Array of all the data for the Creatives
	 */
	public function creatives_data() {
		
		$page = isset( $_GET['paged'] ) ? absint( $_GET['paged'] ) : 1;
		$status  = isset( $_GET['status'] ) ? $_GET['status'] : '';


		$creatives = affiliate_wp()->creatives->get_creatives( array(
			'number'  => $this->per_page,
			'offset'  => $this->per_page * ( $page - 1 ),
			'status'  => $status,
		) );

		return $creatives;
	
	}

	/**
	 * Setup the final data for the table
	 *
	 * @access public
	 * @since 1.2
	 * @uses AffWP_Creatives_Table::get_columns()
	 * @uses AffWP_Creatives_Table::get_sortable_columns()
	 * @uses AffWP_Creatives_Table::process_bulk_action()
	 * @uses AffWP_Creatives_Table::creatives_data()
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

		$data = $this->creatives_data();

		$current_page = $this->get_pagenum();

		$status = isset( $_GET['status'] ) ? $_GET['status'] : 'any';

		switch( $status ) {
			case 'active':
				$total_items = $this->active_count;
				break;
			case 'inactive':
				$total_items = $this->inactive_count;
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