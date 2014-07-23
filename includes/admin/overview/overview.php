<?php
/**
 * Affiiates Overview
 *
 * @package     AffiliateWP
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
		<?php do_action( 'affwp_overview_top' ); ?>
		<div id="affwp-dashboard-widgets-wrap">
			<div id="dashboard-widgets" class="metabox-holder">
				<div id="postbox-container-1" class="postbox-container">
					<?php do_action( 'affwp_overview_tleft_op' ); ?>
					<div class="postbox">
						<h3><?php _e( 'Totals', 'affiliate-wp' ); ?></h3>
						<div class="inside">
							
							<table class="affwp_table">

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

							<table class="affwp_table">

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
							<table class="affwp_table">

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
						<h3><?php _e( 'Latest Affiliate Registrations', 'affiliate-wp' ); ?></h3>
						<div class="inside">
							<?php $affiliates = affiliate_wp()->affiliates->get_affiliates( apply_filters( 'affwp_overview_latest_affiliate_registrations', array( 'number' => 5 ) ) ); ?>
							<table class="affwp_table">

								<thead>

									<tr>
										<th><?php _e( 'Affiliate', 'affiliate-wp' ); ?></th>
										<th><?php _e( 'Status', 'affiliate-wp' ); ?></th>
										<th><?php _e( 'Actions', 'affiliate-wp' ); ?></th>
									</tr>

								</thead>

								<tbody>
									<?php if( $affiliates ) : ?>
										<?php foreach( $affiliates as $affiliate  ) : ?>	
											<tr>
												<td><?php echo affiliate_wp()->affiliates->get_affiliate_name( $affiliate->affiliate_id ); ?></td>
												<td><?php echo $affiliate->status; ?></td>
												<td>
													<?php
													if( 'pending' == $affiliate->status ) {
														$review_url = admin_url( 'admin.php?page=affiliate-wp-affiliates&action=review_affiliate&affiliate_id=' . $affiliate->affiliate_id );
														echo '<a href="' . esc_url( $review_url ) . '">' . __( 'Review', 'affiliate-wp' ) . '</a>';
													} else {
														$affiliate_report_url = admin_url( 'admin.php?page=affiliate-wp-affiliates&action=view_affiliate&affiliate_id=' . $affiliate->affiliate_id );
														echo '<a href="' . esc_url( $affiliate_report_url ) . '">' . __( 'View Report', 'affiliate-wp' ) . '</a>';
													}
													?>
												</td>
											</tr>
										<?php endforeach; ?>
									<?php else : ?>
										<tr>
											<td colspan="3"><?php _e( 'No affiliate registrations yet', 'affiliate-wp' ); ?></td>
										</tr>
									<?php endif; ?>
								</tbody>

							</table>
		
						</div>
					</div>
					<?php do_action( 'affwp_overview_left_bottom' ); ?>
				</div>
				<div id="postbox-container-2" class="postbox-container">
					<?php do_action( 'affwp_overview_right_top' ); ?>
					
					<div class="postbox">
						<h3><?php _e( 'Most Valuable Affiliates', 'affiliate-wp' ); ?></h3>
						<div class="inside">
							<?php $affiliates = affiliate_wp()->affiliates->get_affiliates( apply_filters( 'affwp_overview_most_valuable_affiliates', array( 'number' => 5, 'orderby' => 'earnings', 'order' => 'DESC' ) ) ); ?>
							<table class="affwp_table">

								<thead>

									<tr>
										<th><?php _e( 'Affiliate', 'affiliate-wp' ); ?></th>
										<th><?php _e( 'Earnings', 'affiliate-wp' ); ?></th>
										<th><?php _e( 'Referrals', 'affiliate-wp' ); ?></th>
										<th><?php _e( 'Visits', 'affiliate-wp' ); ?></th>
									</tr>

								</thead>

								<tbody>
								<?php if( $affiliates ) : ?>
									<?php foreach( $affiliates as $affiliate  ) : ?>	
										<tr>
											<td><?php echo affiliate_wp()->affiliates->get_affiliate_name( $affiliate->affiliate_id ); ?></td>
											<td><?php echo affwp_currency_filter( $affiliate->earnings ); ?></td>
											<td><?php echo absint( $affiliate->referrals ); ?></td>
											<td><?php echo absint( $affiliate->visits ); ?></td>
										</tr>
									<?php endforeach; ?>
								<?php else : ?>
									<tr>
										<td colspan="3"><?php _e( 'No registered affiliates', 'affiliate-wp' ); ?></td>
									</tr>
								<?php endif; ?>
								</tbody>

							</table>
						</div>
					</div>

					<div class="postbox">
						<h3><?php _e( 'Recent Referrals', 'affiliate-wp' ); ?></h3>
						<div class="inside">
							<?php $referrals = affiliate_wp()->referrals->get_referrals( apply_filters( 'affwp_overview_recent_referrals', array( 'number' => 5, 'status' => 'unpaid' ) ) ); ?>
							<table class="affwp_table">

								<thead>

									<tr>
										<th><?php _e( 'Affiliate', 'affiliate-wp' ); ?></th>
										<th><?php _e( 'Amount', 'affiliate-wp' ); ?></th>
										<th><?php _e( 'Description', 'affiliate-wp' ); ?></th>
									</tr>

								</thead>

								<tbody>
								<?php if( $referrals ) : ?>
									<?php foreach( $referrals as $referral  ) : ?>	
										<tr>
											<td><?php echo affiliate_wp()->affiliates->get_affiliate_name( $referral->affiliate_id ); ?></td>
											<td><?php echo affwp_currency_filter( $referral->amount ); ?></td>
											<td><?php echo ! empty( $referral->description ) ? esc_html( $referral->description ) : ''; ?></td>
										</tr>
									<?php endforeach; ?>
								<?php else : ?>
									<tr>
										<td colspan="3"><?php _e( 'No referrals recorded yet', 'affiliate-wp' ); ?></td>
									</tr>
								<?php endif; ?>
								</tbody>

							</table>
						</div>
					</div>
					<div class="postbox">
						<h3><?php _e( 'Recent Referral Visits', 'affiliate-wp' ); ?></h3>
						<div class="inside">
							<?php $visits = affiliate_wp()->visits->get_visits( apply_filters( 'affwp_overview_recent_referral_visits', array( 'number' => 8 ) ) ); ?>
							<table class="affwp_table">

								<thead>

									<tr>
										<th><?php _e( 'Affiliate', 'affiliate-wp' ); ?></th>
										<th><?php _e( 'URL', 'affiliate-wp' ); ?></th>
										<th><?php _e( 'Converted', 'affiliate-wp' ); ?></th>
									</tr>

								</thead>

								<tbody>
									<?php if( $visits ) : ?>
										<?php foreach( $visits as $visit ) : ?>	
											<tr>
												<td><?php echo affiliate_wp()->affiliates->get_affiliate_name( $visit->affiliate_id ); ?></td>
												<td><a href="<?php echo esc_url( $visit->url ); ?>"><?php echo esc_html( $visit->url ); ?></a></td>
												<td>
													<?php $converted = ! empty( $visit->referral_id ) ? 'yes' : 'no'; ?>
													<span class="visit-converted <?php echo $converted; ?>"><i></i></span>
												</td>
											</tr>
										<?php endforeach; ?>
									<?php else: ?>
										<tr>
											<td colspan="3"><?php _e( 'No referral visits recorded yet', 'affiliate-wp' ); ?></td>
										</tr>
									<?php endif; ?>
								</tbody>

							</table>
						</div>
					</div>
					<?php do_action( 'affwp_overview_right_bottom' ); ?>
				</div>
			</div>
		</div>
		<?php do_action( 'affwp_overview_bottom' ); ?>
	</div>
<?php
}