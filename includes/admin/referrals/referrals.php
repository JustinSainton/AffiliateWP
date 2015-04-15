<?php
/**
 * Referrals Admin
 *
 * @package     AffiliateWP
 * @subpackage  Admin/Referrals
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

include AFFILIATEWP_PLUGIN_DIR . 'includes/admin/referrals/contextual-help.php';

function affwp_referrals_admin() {

	if( isset( $_GET['action'] ) && 'add_referral' == $_GET['action'] ) {

		include AFFILIATEWP_PLUGIN_DIR . 'includes/admin/referrals/new.php';

	} else if( isset( $_GET['action'] ) && 'edit_referral' == $_GET['action'] ) {

		include AFFILIATEWP_PLUGIN_DIR . 'includes/admin/referrals/edit.php';

	} else {

		$referrals_table = new AffWP_Referrals_Table();
		$referrals_table->prepare_items();
		?>
		<div class="wrap">
			<h2><?php _e( 'Referrals', 'affiliate-wp' ); ?></h2>

			<?php do_action( 'affwp_referrals_page_top' ); ?>
			
			<div id="affwp-referrals-export-wrap">
				<a href="<?php echo esc_url( add_query_arg( 'action', 'add_referral' ) ); ?>" class="button-secondary"><?php _e( 'Add New Referral', 'affiliate-wp' ); ?></a>
				<button class="button-primary affwp-referrals-export-toggle"><?php _e( 'Generate Payout File', 'affiliate-wp' ); ?></button>
				<button class="button-primary affwp-referrals-export-toggle" style="display:none"><?php _e( 'Close', 'affiliate-wp' ); ?></button>
				
				<?php do_action( 'affwp_referrals_page_buttons' ); ?>

				<form id="affwp-referrals-export-form" style="display:none;" action="<?php echo admin_url( 'admin.php?page=affiliate-wp-referrals' ); ?>" method="post">
					<p>
						<input type="text" class="affwp-datepicker" autocomplete="off" name="from" placeholder="<?php _e( 'From - mm/dd/yyyy', 'affiliate-wp' ); ?>"/>
						<input type="text" class="affwp-datepicker" autocomplete="off" name="to" placeholder="<?php _e( 'To - mm/dd/yyyy', 'affiliate-wp' ); ?>"/>
						<input type="text" class="affwp-text" name="minimum" placeholder="<?php esc_attr_e( 'Minimum amount', 'affiliate-wp' ); ?>"/>
						<input type="hidden" name="affwp_action" value="generate_referral_payout"/>
						<?php do_action( 'affwp_referrals_page_csv_export_form' ); ?>
						<input type="submit" value="<?php _e( 'Generate CSV File', 'affiliate-wp' ); ?>" class="button-secondary"/>
						<p><?php printf( __( 'This will mark all unpaid referrals in this timeframe as paid. To export referrals with a status other than <em>unpaid</em>, go to the <a href="%s">Tools &rarr; Export</a> page.', 'affiliate-wp' ), admin_url( 'admin.php?page=affiliate-wp-tools&tab=export_import' ) ); ?></p>
					</p>
				</form>

			</div>
			<form id="affwp-referrals-filter-form" method="get" action="<?php echo admin_url( 'admin.php?page=affiliate-wp-referrals' ); ?>">
			
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
	 * Number of unpaid referrals
	 *
	 * @var int
	 * @since 1.0
	 */
	public $unpaid_count;

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
		$unpaid_count   = '&nbsp;<span class="count">(' . $this->unpaid_count . ')</span>';
		$pending_count  = '&nbsp;<span class="count">(' . $this->pending_count . ')</span>';
		$rejected_count = '&nbsp;<span class="count">(' . $this->rejected_count . ')</span>';

		$views = array(
			'all'      => sprintf( '<a href="%s"%s>%s</a>', esc_url( remove_query_arg( 'status', $base ) ), $current === 'all' || $current == '' ? ' class="current"' : '', __( 'All', 'affiliate-wp' ) . $total_count ),
			'paid'     => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'paid', $base ) ), $current === 'paid' ? ' class="current"' : '', __( 'Paid', 'affiliate-wp' ) . $paid_count ),
			'unpaid'   => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'unpaid', $base ) ), $current === 'unpaid' ? ' class="current"' : '', __( 'Unpaid', 'affiliate-wp' ) . $unpaid_count ),
			'pending'  => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'pending', $base ) ), $current === 'pending' ? ' class="current"' : '', __( 'Pending', 'affiliate-wp' ) . $pending_count ),
			'rejected' => sprintf( '<a href="%s"%s>%s</a>', esc_url( add_query_arg( 'status', 'rejected', $base ) ), $current === 'rejected' ? ' class="current"' : '', __( 'Rejected', 'affiliate-wp' ) . $rejected_count ),
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
			'cb'          => '<input type="checkbox" />',
			'amount'      => __( 'Amount', 'affiliate-wp' ),
			'affiliate'   => __( 'Affiliate', 'affiliate-wp' ),
			'reference'   => __( 'Reference', 'affiliate-wp' ),
			'description' => __( 'Description', 'affiliate-wp' ),
			'date'        => __( 'Date', 'affiliate-wp' ),
			'actions'     => __( 'Actions', 'affiliate-wp' ),
			'status'      => __( 'Status', 'affiliate-wp' ),
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
			'amount'    => array( 'amount', false ),
			'affiliate' => array( 'affiliate_id', false ),
			'date'      => array( 'date', false ),
			'status'    => array( 'status', false ),
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
	 * Render the amount column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $referral Contains all the data for the checkbox column
	 * @return string Displays the referral amount
	 */
	public function column_amount( $referral ) {
		return affwp_currency_filter( affwp_format_amount( $referral->amount ) );
	}

	/**
	 * Render the status column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $referral Contains all the data for the checkbox column
	 * @return string Displays the referral status
	 */
	public function column_status( $referral ) {
		return '<span class="affwp-status ' . $referral->status . '"><i></i>' . affwp_get_referral_status_label( $referral ) . '</span>';
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
		return '<a href="' . admin_url( 'admin.php?page=affiliate-wp-referrals&affiliate_id=' . $referral->affiliate_id ) . '">' . affiliate_wp()->affiliates->get_affiliate_name( $referral->affiliate_id ) . '</a>';
	}
	
	/**
	 * Render the reference column
	 *
	 * @access public
	 * @since 1.0
	 * @param array $referral Contains all the data for the checkbox column
	 * @return string The reference
	 */
	public function column_reference( $referral ) {

		return apply_filters( 'affwp_referral_reference_column', $referral->reference, $referral );

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
		
		if( 'paid' == $referral->status ) {
			
			$action_links[] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'mark_as_unpaid', 'referral_id' => $referral->referral_id ) ) ) . '" class="mark-as-paid">' . __( 'Mark as Unpaid', 'affiliate-wp' ) . '</a>';
	
		} else {

			if( 'unpaid' == $referral->status ) {	

				$action_links[] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'mark_as_paid', 'referral_id' => $referral->referral_id ) ) ) . '" class="mark-as-paid">' . __( 'Mark as Paid', 'affiliate-wp' ) . '</a>';

			}

			if( 'rejected' == $referral->status || 'pending' == $referral->status ) {
			
				$action_links[] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'accept', 'referral_id' => $referral->referral_id ) ) ) . '" class="reject">' . __( 'Accept', 'affiliate-wp' ) . '</a>';
			
			}

			if( 'rejected' != $referral->status ) {
			
				$action_links[] = '<a href="' . esc_url( add_query_arg( array( 'action' => 'reject', 'referral_id' => $referral->referral_id ) ) ) . '" class="reject">' . __( 'Reject', 'affiliate-wp' ) . '</a>';
			
			}

		}
		
		$action_links[] = '<span class="trash"><a href="' . esc_url( add_query_arg( array( 'action' => 'edit_referral', 'referral_id' => $referral->referral_id ) ) ) . '" class="edit">' . __( 'Edit', 'affiliate-wp' ) . '</a></span>';
		$action_links[] = '<span class="trash"><a href="' . esc_url( add_query_arg( array( 'action' => 'delete', 'referral_id' => $referral->referral_id ) ) ) . '" class="delete">' . __( 'Delete', 'affiliate-wp' ) . '</a></span>';
		
		$action_links   = array_unique( apply_filters( 'affwp_referral_action_links', $action_links, $referral ) );

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
	 * Outputs the reporting views
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function bulk_actions( $which = '' ) {
		
		if ( is_null( $this->_actions ) ) {
			$no_new_actions = $this->_actions = $this->get_bulk_actions();
			$this->_actions = array_intersect_assoc( $this->_actions, $no_new_actions );
			$two = '';
		} else {
			$two = '2';
		}

		if ( empty( $this->_actions ) )
			return;

		echo "<select name='action$two'>\n";
		echo "<option value='-1' selected='selected'>" . __( 'Bulk Actions', 'affiliate-wp' ) . "</option>\n";

		foreach ( $this->_actions as $name => $title ) {
			$class = 'edit' == $name ? ' class="hide-if-no-js"' : '';

			echo "\t<option value='$name'$class>$title</option>\n";
		}

		echo "</select>\n";

		do_action( 'affwp_referral_bulk_actions' );

		submit_button( __( 'Apply', 'affiliate-wp' ), 'action', false, false, array( 'id' => "doaction$two" ) );
		echo "\n";

		// Makes the filters only get output at the top of the page
		if( ! did_action( 'affwp_referral_filters' ) ) {

			$from = ! empty( $_REQUEST['filter_from'] ) ? $_REQUEST['filter_from'] : '';
			$to   = ! empty( $_REQUEST['filter_to'] )   ? $_REQUEST['filter_to']   : '';

			echo "<input type='text' class='affwp-datepicker' autocomplete='off' name='filter_from' placeholder='" . __( 'From - mm/dd/yyyy', 'affiliate-wp' ) . "' value='" . $from . "'/>";
			echo "<input type='text' class='affwp-datepicker' autocomplete='off' name='filter_to' placeholder='" . __( 'To - mm/dd/yyyy', 'affiliate-wp' ) . "' value='" . $to . "'/>&nbsp;";

			do_action( 'affwp_referral_filters' );

			submit_button( __( 'Filter', 'affiliate-wp' ), 'action', false, false );
			echo "\n";

		}
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
			'accept'         => __( 'Accept', 'affiliate-wp' ),
			'reject'         => __( 'Reject', 'affiliate-wp' ),
			'mark_as_paid'   => __( 'Mark as Paid', 'affiliate-wp' ),
			'mark_as_unpaid' => __( 'Mark as Unpaid', 'affiliate-wp' ),
			'delete'         => __( 'Delete', 'affiliate-wp' ),
		);

		return apply_filters( 'affwp_referrals_bulk_actions', $actions );
	}

	/**
	 * Process the bulk actions
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function process_bulk_action() {
		$ids = isset( $_GET['referral_id'] ) ? $_GET['referral_id'] : array();

		if ( ! is_array( $ids ) ) {
			$ids = array( $ids );
		}

		$ids    = array_map( 'absint', $ids );
		$action = ! empty( $_REQUEST['action'] ) ? $_REQUEST['action'] : false;

		if( empty( $ids ) || empty( $action ) ) {
			return;
		}

		foreach ( $ids as $id ) {
			
			if ( 'delete' === $this->current_action() ) {
				affwp_delete_referral( $id );
			}
			
			if ( 'reject' === $this->current_action() ) {
				affwp_set_referral_status( $id, 'rejected' );
			}

			if ( 'accept' === $this->current_action() ) {
				affwp_set_referral_status( $id, 'unpaid' );
			}

			if ( 'mark_as_paid' === $this->current_action() ) {
				affwp_set_referral_status( $id, 'paid' );
			}

			if ( 'mark_as_unpaid' === $this->current_action() ) {
				affwp_set_referral_status( $id, 'unpaid' );
			}

			do_action( 'affwp_referrals_do_bulk_action_' . $this->current_action(), $id );

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

		$affiliate_id = isset( $_GET['affiliate_id'] ) ? $_GET['affiliate_id'] : ''; 

		if( is_array( $affiliate_id ) ) {
			$affiliate_id = array_map( 'absint', $affiliate_id );
		} else {
			$affiliate_id = absint( $affiliate_id );
		}

		$this->paid_count     = affiliate_wp()->referrals->count( array( 'affiliate_id' => $affiliate_id, 'status' => 'paid' ) );
		$this->unpaid_count   = affiliate_wp()->referrals->count( array( 'affiliate_id' => $affiliate_id, 'status' => 'unpaid' ) );
		$this->pending_count  = affiliate_wp()->referrals->count( array( 'affiliate_id' => $affiliate_id, 'status' => 'pending' ) );
		$this->rejected_count = affiliate_wp()->referrals->count( array( 'affiliate_id' => $affiliate_id, 'status' => 'rejected' ) );
		$this->total_count    = $this->paid_count + $this->unpaid_count + $this->pending_count + $this->rejected_count;
	}

	/**
	 * Retrieve all the data for all the Affiliates
	 *
	 * @access public
	 * @since 1.0
	 * @return array $referrals_data Array of all the data for the Affiliates
	 */
	public function referrals_data() {
		
		$page      = isset( $_GET['paged'] )        ? absint( $_GET['paged'] ) : 1;
		$status    = isset( $_GET['status'] )       ? $_GET['status']          : ''; 
		$affiliate = isset( $_GET['affiliate_id'] ) ? $_GET['affiliate_id']    : ''; 
		$reference = isset( $_GET['reference'] )    ? $_GET['reference']       : ''; 
		$context   = isset( $_GET['context'] )      ? $_GET['context']         : ''; 
		$from      = isset( $_GET['filter_from'] )  ? $_GET['filter_from']     : ''; 
		$to        = isset( $_GET['filter_to'] )    ? $_GET['filter_to']       : ''; 
		$order     = isset( $_GET['order'] )        ? $_GET['order']           : 'DESC';
		$orderby   = isset( $_GET['orderby'] )      ? $_GET['orderby']         : 'referral_id';
		$referral  = ''; 
		$is_search = false;

		$date = array();
		if( ! empty( $from ) ) {
			$date['start'] = $from;
		}
		if( ! empty( $to ) ) {
			$date['end']   = $to . ' 23:59:59';;
		}

		if( ! empty( $_GET['s'] ) ) {

			$is_search = true;

			$search = sanitize_text_field( $_GET['s'] );

			if( is_numeric( $search ) ) {
				// This is an referral ID search
				$referral = absint( $search );
			} elseif ( strpos( $search, 'ref:' ) !== false ) {
				$reference = trim( str_replace( 'ref:', '', $search ) );
			} elseif ( strpos( $search, 'context:' ) !== false ) {
				$context = trim( str_replace( 'context:', '', $search ) );
			} elseif ( strpos( $search, 'affiliate:' ) !== false ) {
				$affiliate = absint( trim( str_replace( 'affiliate:', '', $search ) ) );
			}

		}

		$referrals  = affiliate_wp()->referrals->get_referrals( array(
			'number'       => $this->per_page,
			'offset'       => $this->per_page * ( $page - 1 ),
			'status'       => $status,
			'referral_id'  => $referral,
			'affiliate_id' => $affiliate,
			'reference'    => $reference,
			'context'      => $context,
			'date'         => $date,
			'search'       => $is_search,
			'orderby'      => sanitize_text_field( $orderby ),
			'order'        => sanitize_text_field( $order )
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
			case 'unpaid':
				$total_items = $this->unpaid_count;
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