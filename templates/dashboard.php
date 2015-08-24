<?php $active_tab = ! empty( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'urls'; ?>

<div id="affwp-affiliate-dashboard">

	<?php if ( 'pending' == affwp_get_affiliate_status( affwp_get_affiliate_id() ) ) : ?>

		<p class="affwp-notice"><?php _e( 'Your affiliate account is pending approval', 'affiliate-wp' ); ?></p>

	<?php elseif ( 'inactive' == affwp_get_affiliate_status( affwp_get_affiliate_id() ) ) : ?>

		<p class="affwp-notice"><?php _e( 'Your affiliate account is not active', 'affiliate-wp' ); ?></p>

	<?php elseif ( 'rejected' == affwp_get_affiliate_status( affwp_get_affiliate_id() ) ) : ?>

		<p class="affwp-notice"><?php _e( 'Your affiliate account request has been rejected', 'affiliate-wp' ); ?></p>

	<?php endif; ?>

	<?php if ( 'active' == affwp_get_affiliate_status( affwp_get_affiliate_id() ) ) : ?>

		<?php do_action( 'affwp_affiliate_dashboard_top', affwp_get_affiliate_id() ); ?>

		<?php if ( ! empty( $_GET['affwp_notice'] ) && 'profile-updated' == $_GET['affwp_notice'] ) : ?>

			<p class="affwp-notice"><?php _e( 'Your affiliate profile has been updated', 'affiliate-wp' ); ?></p>

		<?php endif; ?>

		<?php do_action( 'affwp_affiliate_dashboard_notices', affwp_get_affiliate_id() ); ?>

		<ul id="affwp-affiliate-dashboard-tabs">
			<li class="affwp-affiliate-dashboard-tab<?php echo $active_tab == 'urls' ? ' active' : ''; ?>">
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'urls' ) ); ?>"><?php _e( 'Affiliate URLs', 'affiliate-wp' ); ?></a>
			</li>
			<li class="affwp-affiliate-dashboard-tab<?php echo $active_tab == 'stats' ? ' active' : ''; ?>">
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'stats' ) ); ?>"><?php _e( 'Statistics', 'affiliate-wp' ); ?></a>
			</li>
			<li class="affwp-affiliate-dashboard-tab<?php echo $active_tab == 'graphs' ? ' active' : ''; ?>">
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'graphs' ) ); ?>"><?php _e( 'Graphs', 'affiliate-wp' ); ?></a>
			</li>
			<li class="affwp-affiliate-dashboard-tab<?php echo $active_tab == 'referrals' ? ' active' : ''; ?>">
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'referrals' ) ); ?>"><?php _e( 'Referrals', 'affiliate-wp' ); ?></a>
			</li>
			<li class="affwp-affiliate-dashboard-tab<?php echo $active_tab == 'visits' ? ' active' : ''; ?>">
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'visits' ) ); ?>"><?php _e( 'Visits', 'affiliate-wp' ); ?></a>
			</li>
			<li class="affwp-affiliate-dashboard-tab<?php echo $active_tab == 'creatives' ? ' active' : ''; ?>">
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'creatives' ) ); ?>"><?php _e( 'Creatives', 'affiliate-wp' ); ?></a>
			</li>
			<li class="affwp-affiliate-dashboard-tab<?php echo $active_tab == 'settings' ? ' active' : ''; ?>">
				<a href="<?php echo esc_url( add_query_arg( 'tab', 'settings' ) ); ?>"><?php _e( 'Settings', 'affiliate-wp' ); ?></a>
			</li>
			<?php do_action( 'affwp_affiliate_dashboard_tabs', affwp_get_affiliate_id(), $active_tab ); ?>
		</ul>

		<?php affiliate_wp()->templates->get_template_part( 'dashboard-tab', $active_tab ); ?>

		<?php do_action( 'affwp_affiliate_dashboard_bottom', affwp_get_affiliate_id() ); ?>

	<?php endif; ?>

	
</div>
