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

function affwp_reports_admin() {

	$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], affwp_get_reports_tabs() ) ? $_GET[ 'tab' ] : 'export_import';

?>
	<div class="wrap">

		<?php do_action( 'affwp_reports_page_top' ); ?>

		<h2 class="nav-tab-wrapper">
			<?php
			foreach( affwp_get_reports_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab'              => $tab_id,
					'affwp_notice'     => false
				) );

				$active = $active_tab == $tab_id ? ' nav-tab-active' : '';

				echo '<a href="' . esc_url( $tab_url ) . '" title="' . esc_attr( $tab_name ) . '" class="nav-tab' . $active . '">';
					echo esc_html( $tab_name );
				echo '</a>';
			}
			?>
		</h2>

		<?php do_action( 'affwp_reports_page_middle' ); ?>

		<div id="tab_container">
			<?php do_action( 'affwp_reports_tab_' . $active_tab ); ?>
		</div><!-- #tab_container-->

		<?php do_action( 'affwp_reports_page_bottom' ); ?>

	</div>
<?php
}

/**
 * Retrieve reports tabs
 *
 * @since 1.1
 * @return array $tabs
 */
function affwp_get_reports_tabs() {

	$tabs                  = array();
	$tabs['referrals']     = __( 'Referrals', 'affiliate-wp' );
	$tabs['registrations'] = __( 'Registrations', 'affiliate-wp' );
	$tabs['visits']        = __( 'Visits', 'affiliate-wp' );

	return apply_filters( 'affwp_reports_tabs', $tabs );
}

/**
 * Display the referrals reports tab
 *
 * @since 1.1
 * @return void
 */
function affwp_reports_tab_referrals() {
?>
	<table id="affwp_total_earnings" class="affwp_table">

		<thead>

			<tr>

				<th><?php _e( 'Paid Earnings', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Paid Earnings This Month', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Paid Earnings Today', 'affiliate-wp' ); ?></th>

			</tr>

		</thead>

		<tbody>

			<tr>
				<td><?php echo affiliate_wp()->referrals->paid_earnings(); ?></td>
				<td><?php echo affiliate_wp()->referrals->paid_earnings( 'month' ); ?></td>
				<td><?php echo affiliate_wp()->referrals->paid_earnings( 'today' ); ?></td>
			</tr>

		</tbody>

	</table>

	<table id="affwp_unpaid_earnings" class="affwp_table">

		<thead>

			<tr>

				<th><?php _e( 'Unpaid Earnings', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Unpaid Earnings This Month', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Unpaid Earnings Today', 'affiliate-wp' ); ?></th>

			</tr>

		</thead>

		<tbody>

			<tr>
				<td><?php echo affiliate_wp()->referrals->unpaid_earnings(); ?></td>
				<td><?php echo affiliate_wp()->referrals->unpaid_earnings( 'month' ); ?></td>
				<td><?php echo affiliate_wp()->referrals->unpaid_earnings( 'today' ); ?></td>
			</tr>

		</tbody>

	</table>

	<table id="affwp_unpaid_counts" class="affwp_table">

		<thead>

			<tr>

				<th><?php _e( 'Unpaid Referrals', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Unpaid Referrals This Month', 'affiliate-wp' ); ?></th>
				<th><?php _e( 'Unpaid Referrals Today', 'affiliate-wp' ); ?></th>

			</tr>

		</thead>

		<tbody>

			<tr>
				<td><?php echo affiliate_wp()->referrals->unpaid_count(); ?></td>
				<td><?php echo affiliate_wp()->referrals->unpaid_count( 'month' ); ?></td>
				<td><?php echo affiliate_wp()->referrals->unpaid_count( 'today' ); ?></td>
			</tr>

		</tbody>

	</table>

	<?php
	$graph = new Affiliate_WP_Referrals_Graph;
	$graph->set( 'x_mode', 'time' );
	$graph->display();

}
add_action( 'affwp_reports_tab_referrals', 'affwp_reports_tab_referrals' );