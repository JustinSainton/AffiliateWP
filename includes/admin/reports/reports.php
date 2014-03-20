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

function affwp_reports_admin() {

?>
	<div class="wrap">
		<h2><?php _e( 'Reports', 'affiliate-wp' ); ?></h2>
		<?php do_action( 'affwp_reports_page_top' ); ?>
		
		<table id="affwp_unpaid_counts" class="affwp_table">

			<thead>

				<tr>

					<th><?php _e( 'Total Unpaid Referrals', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Total Unpaid Referrals Today', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Total Unpaid Referrals This Month', 'affiliate-wp' ); ?></th>

				</tr>

			</thead>

			<tbody>

				<tr>
					<td><?php echo affiliate_wp()->referrals->unpaid_count(); ?></td>
					<td><?php echo affiliate_wp()->referrals->unpaid_count( 'today' ); ?></td>
					<td><?php echo affiliate_wp()->referrals->unpaid_count( 'month' ); ?></td>
				</tr>

			</tbody>

		</table>

		<table id="affwp_unpaid_earnings" class="affwp_table">

			<thead>

				<tr>

					<th><?php _e( 'Total Unpaid Referrals', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Total Unpaid Referrals Today', 'affiliate-wp' ); ?></th>
					<th><?php _e( 'Total Unpaid Referrals This Month', 'affiliate-wp' ); ?></th>

				</tr>

			</thead>

			<tbody>

				<tr>
					<td><?php echo affiliate_wp()->referrals->unpaid_earnings(); ?></td>
					<td><?php echo affiliate_wp()->referrals->unpaid_earnings( 'today' ); ?></td>
					<td><?php echo affiliate_wp()->referrals->unpaid_earnings( 'month' ); ?></td>
				</tr>

			</tbody>

		</table>

		<?php do_action( 'affwp_reports_page_bottom' ); ?>
	</div>
<?php


}