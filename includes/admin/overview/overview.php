<?php
/**
 * Affiiates Overview
 *
 * @package     Affiliate WP
 * @subpackage  Admin/Overview
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
 */


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

function affwp_affiliates_dashboard() {
?>
	<div class="wrap">
		<h2><?php _e( 'Overview', 'affiliate-wp' ); ?></h2>
		<div id="affwp-dashboard-widgets-wrap">
			<div id="dashboard-widgets" class="metabox-holder">
				<div id="postbox-container-1" class="postbox-container">
					<div class="postbox">
						<h3><?php _e( 'Totals', 'affiliate-wp' ); ?></h3>
						<div class="inside">
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
						</div>
					</div>
					<div class="postbox">
						<h3><?php _e( 'Recent Referrals', 'affiliate-wp' ); ?></h3>
						<div class="inside">
							<?php
							$referrals = affiliate_wp()->referrals->get_referrals( array( 'number' => 5, 'status' => 'unpaid' ) );
							if( $referrals ) {

								echo '<ul>';

								foreach( $referrals as $referral ) {

									echo '<li>';

										echo '<span class="referral-amount">';
											printf(
												_x( '%s for %s', 'Amount for affiliate', 'affiliate-wp' ),
												affwp_currency_filter( $referral->amount ),
												affiliate_wp()->affiliates->get_affiliate_name( $referral->affiliate_id )
											);
										echo '</span>';
										if( ! empty( $referral->description ) ) {
											echo '<span class="referral-sep">&nbsp;&ndash;&nbsp;</span>';
											echo '<span class="referral-description">' . esc_html( $referral->description ) . '</span>';
										}

									echo '</li>';

								}

								echo '</ul>';

							}
							?>
						</div>
					</div>
				</div>
				<div id="postbox-container-2" class="postbox-container">
					<div class="postbox">
						<h3><?php _e( 'Latest Affiliate Registrations', 'affiliate-wp' ); ?></h3>
						<div class="inside">
							<?php
							$affiliates = affiliate_wp()->affiliates->get_affiliates( array( 'number' => 5 ) );
							if( $affiliates ) {
								echo '<ul>';
								foreach( $affiliates as $affiliate ) {
									echo '<li>';
										echo '<span class="affiliate-name">' . affiliate_wp()->affiliates->get_affiliate_name( $affiliate->affiliate_id ) . '</span>';
										echo '<span class="affiliate-sep">&nbsp;&ndash;&nbsp;</span>';
										echo '<span class="affiliate-status ' . esc_attr( $affiliate->status ) . '">';
											echo $affiliate->status;
											if( 'pending' == $affiliate->status ) {
												echo '<span class="affiliate-sep">&nbsp;&ndash;&nbsp;</span>';
												$accept_url = admin_url( 'admin.php?page=affiliate-wp-affiliates&action=accept&affiliate_id=' . $affiliate->affiliate_id );
												$reject_url = admin_url( 'admin.php?page=affiliate-wp-affiliates&action=reject&affiliate_id=' . $affiliate->affiliate_id );
												echo '<a href="' . esc_url( $accept_url ) . '">' . __( 'Accept', 'affiliate-wp' ) . '</a>';
												echo ' | <a href="' . esc_url( $reject_url ) . '">' . __( 'Reject', 'affiliate-wp' ) . '</a>';
											}
										echo '</span>';
									echo '</li>';
								}
								echo '</ul>';
							}
							?>
						</div>
					</div>
					<div class="postbox">
						<h3><?php _e( 'Referral Visits', 'affiliate-wp' ); ?></h3>
						<div class="inside">
							<?php
							$visits = affiliate_wp()->visits->get_visits( array( 'number' => 8 ) );
							if( $visits ) {

								echo '<ul>';

								foreach( $visits as $visit ) {

									echo '<li>';

										echo '<span class="visit-affiliate-name">';
											echo affiliate_wp()->affiliates->get_affiliate_name( $visit->affiliate_id );
										echo '</span>';
										echo '<span class="visit-sep">&nbsp;&ndash;&nbsp;</span>';
										echo '<span class="visit-url">';
											echo '<a href="' . esc_url( $visit->url ) . '">' . esc_html( $visit->url ) . '</a>';
										echo '</span>';
										
									echo '</li>';

								}

								echo '</ul>';

							}
							?>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
<?php
}