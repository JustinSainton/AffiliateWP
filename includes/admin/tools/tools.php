<?php
/**
 * Admin Tools Page
 *
 * @package     AffiliateWP
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/migration.php';
require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/class-recount.php';
require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/import/import.php';
require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/export/export.php';
require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/export/class-export.php';
require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/export/class-export-affiliates.php';
require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/export/class-export-referrals.php';
require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/export/class-export-referrals-payout.php';

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @return void
 */
function affwp_tools_admin() {

	$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], affwp_get_tools_tabs() ) ? $_GET[ 'tab' ] : 'export_import';

	ob_start();
	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( affwp_get_tools_tabs() as $tab_id => $tab_name ) {

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
		<div id="tab_container">
			<?php do_action( 'affwp_tools_tab_' . $active_tab ); ?>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}


/**
 * Retrieve tools tabs
 *
 * @since 1.0
 * @return array $tabs
 */
function affwp_get_tools_tabs() {

	$tabs                  = array();
	$tabs['export_import'] = __( 'Export / Import', 'affiliate-wp' );
	$tabs['recount']       = __( 'Recount Stats', 'affiliate-wp' );
	$tabs['migration']     = __( 'Migration Assistant', 'affiliate-wp' );

	return apply_filters( 'affwp_tools_tabs', $tabs );
}

/**
 * Recount Tab
 *
 * @since       1.0
 * @return      void
 */
function affwp_recount_tab() {
?>
	<div id="affwp-dashboard-widgets-wrap">
		<div class="metabox-holder">
			<div class="postbox">
				<h3><span><?php _e( 'Recount Affiliate Stats', 'affiliate-wp' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Use this tool to recount affiliate statistics.', 'affiliate-wp' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=affiliate-wp-tools&tab=recount' ); ?>">
						<p>
							<span class="affwp-ajax-search-wrap">
								<input type="text" name="user_name" id="user_name" class="affwp-user-search" autocomplete="off" placeholder="<?php _e( 'Affiliate name', 'affiliate-wp' ); ?>"/>
								<img class="affwp-ajax waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" style="display: none;"/>
							</span>
							<select name="recount_type">
								<option value="earnings"><?php _e( 'Paid Earnings', 'affiliate-wp' ); ?></option>
								<option value="referrals"><?php _e( 'Referrals', 'affiliate-wp' ); ?></option>
								<option value="visits"><?php _e( 'Visits', 'affiliate-wp' ); ?></option>
							</select>
							<div id="affwp_user_search_results"></div>
							<div class="description"><?php _e( 'Enter the name of the affiliate or begin typing to perform a search based on the affiliate\'s name.', 'affiliate-wp' ); ?></div>
						</p>
						<p>
							<input type="hidden" name="user_id" id="user_id" value="0"/>
							<input type="hidden" name="affwp_action" value="recount_stats"/>
							<?php submit_button( __( 'Recount', 'affiliate-wp' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
		</div><!-- .metabox-holder -->
	</div><!-- #affwp-dashboard-widgets-wrap -->
<?php
}
add_action( 'affwp_tools_tab_recount', 'affwp_recount_tab' );

/**
 * Migration assistant tab
 *
 * @since       1.0
 * @return      void
 */
function affwp_migration_tab() {
?>
	<div id="affwp-dashboard-widgets-wrap">
		<div class="metabox-holder">
			<div class="postbox">
				<div class="inside">
					<p><?php _e( 'These tools assist in migrating affiliate and referral data from existing platforms.', 'affiliate-wp' ); ?></p>
				</div><!-- .inside -->
			</div><!-- .postbox -->

			<div class="postbox">
				<h3><span><?php _e( 'User Accounts', 'affiliate-wp' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Use this tool to create affiliate accounts for each of your existing WordPress user accounts.', 'affiliate-wp' ); ?></p>
					<form method="get">
						<input type="hidden" name="type" value="users"/>
						<input type="hidden" name="part" value="affiliates"/>
						<input type="hidden" name="page" value="affiliate-wp-migrate"/>
						<p>
							<input type="submit" value="<?php _e( 'Create Affiliate Accounts', 'affiliate-wp' ); ?>" class="button"/>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

			<div class="postbox">
				<h3><span>Affiliates Pro</span></h3>
				<div class="inside">
					<p><?php _e( 'Use this tool migrate existing affiliate / referral data from Affiliates Pro to AffiliateWP.', 'affiliate-wp' ); ?></p>
					<p><?php _e( '<strong>NOTE:</strong> this tool should only ever be used on a fresh install. If you have already collected affiliate or referral data, do not use this tool.', 'affiliate-wp' ); ?></p>
					<form method="get">
						<input type="hidden" name="type" value="affiliates-pro"/>
						<input type="hidden" name="part" value="affiliates"/>
						<input type="hidden" name="page" value="affiliate-wp-migrate"/>
						<p>
							<input type="submit" value="<?php _e( 'Migrate Data from Affiliates Pro', 'affiliate-wp' ); ?>" class="button"/>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

			<div class="postbox">
				<h3><span>WP Affiliate</span></h3>
				<div class="inside">
					<p><?php _e( 'Use this tool migrate existing affiliate accounts from WP Affiliate to AffiliateWP.', 'affiliate-wp' ); ?></p>
					<form method="get">
						<input type="hidden" name="type" value="wp-affiliate"/>
						<input type="hidden" name="part" value="affiliates"/>
						<input type="hidden" name="page" value="affiliate-wp-migrate"/>
						<p>
							<input type="submit" value="<?php _e( 'Migrate Data from WP Affiliate', 'affiliate-wp' ); ?>" class="button"/>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

		</div><!-- .metabox-holder -->
	</div><!-- #affwp-dashboard-widgets-wrap -->
<?php
}
add_action( 'affwp_tools_tab_migration', 'affwp_migration_tab' );

/**
 * Export / Import tab
 *
 * @since       1.0
 * @return      void
 */
function affwp_export_import_tab() {
?>
	<div id="affwp-dashboard-widgets-wrap">
		<div class="metabox-holder">

			<div class="postbox">
				<h3><span><?php _e( 'Export Affiliates', 'affiliate-wp' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Export affiliates to a CSV file.', 'affiliate-wp' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=affiliate-wp-tools&tab=export_import' ); ?>">
						<p>
							<select name="status" id="status">
								<option value="0"><?php _e( 'All Statuses', 'affiliate-wp' ); ?></option>
								<option value="active"><?php _e( 'Active', 'affiliate-wp' ); ?></option>
								<option value="pending"><?php _e( 'Pending', 'affiliate-wp' ); ?></option>
								<option value="rejected"><?php _e( 'Rejected', 'affiliate-wp' ); ?></option>
							</select>
						</p>
						<p>
							<input type="hidden" name="affwp_action" value="export_affiliates" />
							<?php wp_nonce_field( 'affwp_export_affiliates_nonce', 'affwp_export_affiliates_nonce' ); ?>
							<?php submit_button( __( 'Export', 'affiliate-wp' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

			<div class="postbox">
				<h3><span><?php _e( 'Export Referrals', 'affiliate-wp' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Export referrals to a CSV file.', 'affiliate-wp' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=affiliate-wp-tools&tab=export_import' ); ?>">
						<p>
							<span class="affwp-ajax-search-wrap">
								<input type="text" name="user_name" id="user_name" class="affwp-user-search" autocomplete="off" placeholder="<?php _e( 'Affiliate name', 'affiliate-wp' ); ?>" />
								<input type="hidden" name="user_id" id="user_id" value=""/>
								<img class="affwp-ajax waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" style="display: none;"/>
							</span>
							<input type="text" class="affwp-datepicker" autocomplete="off" name="start_date" placeholder="<?php _e( 'From - mm/dd/yyyy', 'affiliate-wp' ); ?>"/>
							<input type="text" class="affwp-datepicker" autocomplete="off" name="end_date" placeholder="<?php _e( 'To - mm/dd/yyyy', 'affiliate-wp' ); ?>"/>
							<select name="status" id="status">
								<option value="0"><?php _e( 'All Statuses', 'affiliate-wp' ); ?></option>
								<option value="paid"><?php _e( 'Paid', 'affiliate-wp' ); ?></option>
								<option value="unpaid"><?php _e( 'Unpaid', 'affiliate-wp' ); ?></option>
								<option value="pending"><?php _e( 'Pending', 'affiliate-wp' ); ?></option>
								<option value="rejected"><?php _e( 'Rejected', 'affiliate-wp' ); ?></option>
							</select>
							<div id="affwp_user_search_results"></div>
							<div class="description"><?php _e( 'To search for a affiliate, enter the affiliate\'s login name, first name, or last name. Leave blank to export referrals for all affiliates.', 'affiliate-wp' ); ?></div>
						</p>
						<p>
							<input type="hidden" name="affwp_action" value="export_referrals" />
							<?php wp_nonce_field( 'affwp_export_referrals_nonce', 'affwp_export_referrals_nonce' ); ?>
							<?php submit_button( __( 'Export', 'affiliate-wp' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

			<div class="postbox">
				<h3><span><?php _e( 'Export Settings', 'affiliate-wp' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Export the AffiliateWP settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'affiliate-wp' ); ?></p>
					<form method="post" action="<?php echo admin_url( 'admin.php?page=affiliate-wp-tools&tab=export_import' ); ?>">
						<p><input type="hidden" name="affwp_action" value="export_settings" /></p>
						<p>
							<?php wp_nonce_field( 'affwp_export_nonce', 'affwp_export_nonce' ); ?>
							<?php submit_button( __( 'Export', 'affiliate-wp' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->

			<div class="postbox">
				<h3><span><?php _e( 'Import Settings', 'affiliate-wp' ); ?></span></h3>
				<div class="inside">
					<p><?php _e( 'Import the AffiliateWP settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'affiliate-wp' ); ?></p>
					<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=affiliate-wp-tools&tab=export_import' ); ?>">
						<p>
							<input type="file" name="import_file"/>
						</p>
						<p>
							<input type="hidden" name="affwp_action" value="import_settings" />
							<?php wp_nonce_field( 'affwp_import_nonce', 'affwp_import_nonce' ); ?>
							<?php submit_button( __( 'Import', 'affiliate-wp' ), 'secondary', 'submit', false ); ?>
						</p>
					</form>
				</div><!-- .inside -->
			</div><!-- .postbox -->
		</div><!-- .metabox-holder -->
	</div><!-- #affwp-dashboard-widgets-wrap -->
<?php
}
add_action( 'affwp_tools_tab_export_import', 'affwp_export_import_tab' );