<div id="affwp-affiliate-dashboard-referrals" class="affwp-tab-content">

	<h4><?php _e( 'Referrals', 'affiliate-wp' ); ?></h4>

	<?php
	$per_page  = 30;
	$page      = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
	$pages     = absint( ceil( affwp_count_referrals( affwp_get_affiliate_id() ) / $per_page ) );
	$referrals = affiliate_wp()->referrals->get_referrals(
		array(
			'number'       => $per_page,
			'offset'       => $per_page * ( $page - 1 ),
			'affiliate_id' => affwp_get_affiliate_id(),
			'status'       => array( 'paid', 'unpaid', 'rejected' ),
		)
	);
	?>

	<?php do_action( 'affwp_referrals_dashboard_before_table', affwp_get_affiliate_id() ); ?>

	<table id="affwp-affiliate-dashboard-referrals" class="affwp-table">
		<thead>
			<tr>
				<th class="referral-amount"><?php _e( 'Amount', 'affiliate-wp' ); ?></th>
				<th class="referral-description"><?php _e( 'Description', 'affiliate-wp' ); ?></th>
				<th class="referral-status"><?php _e( 'Status', 'affiliate-wp' ); ?></th>
				<th class="referral-date"><?php _e( 'Date', 'affiliate-wp' ); ?></th>
				<?php do_action( 'affwp_referrals_dashboard_th' ); ?>
			</tr>
		</thead>

		<tbody>
			<?php if ( $referrals ) : ?>

				<?php foreach ( $referrals as $referral ) : ?>
					<tr>
						<td class="referral-amount"><?php echo affwp_currency_filter( affwp_format_amount( $referral->amount ) ); ?></td>
						<td class="referral-description"><?php echo wp_kses_post( nl2br( $referral->description ) ); ?></td>
						<td class="referral-status <?php echo $referral->status; ?>"><?php echo affwp_get_referral_status_label( $referral ); ?></td>
						<td class="referral-date"><?php echo date_i18n( get_option( 'date_format' ), strtotime( $referral->date ) ); ?></td>
						<?php do_action( 'affwp_referrals_dashboard_td', $referral ); ?>
					</tr>
				<?php endforeach; ?>

			<?php else : ?>

				<tr>
					<td colspan="4"><?php _e( 'You have not made any referrals yet.', 'affiliate-wp' ); ?></td>
				</tr>

			<?php endif; ?>
		</tbody>
	</table>

	<?php do_action( 'affwp_referrals_dashboard_after_table', affwp_get_affiliate_id() ); ?>

	<?php if ( $pages > 1 ) : ?>

		<p class="affwp-pagination">
			<?php
			echo paginate_links(
				array(
					'current'      => $page,
					'total'        => $pages,
					'add_fragment' => '#affwp-affiliate-dashboard-referrals',
					'add_args'     => array(
						'tab' => 'referrals',
					),
				)
			);
			?>
		</p>

	<?php endif; ?>

</div>
