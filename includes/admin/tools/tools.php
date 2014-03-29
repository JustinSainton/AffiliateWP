<?php
/**
 * Admin Tools Page
 *
 * @package     Affiliate WP
 * @subpackage  Admin/Tools
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/export/export.php';
require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/export/class-export.php';
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
	//$tabs['general']       = __( 'General', 'affiliate-wp' );
	$tabs['export_import'] = __( 'Export / Import', 'affiliate-wp' );
	$tabs['migration']     = __( 'Migration Assistant', 'affiliate-wp' );

	return apply_filters( 'affwp_tools_tabs', $tabs );
}

/**
 * General Tab
 *
 * @since       1.0
 * @return      void
 */
function affwp_general_tab() {
?>

	<div class="metabox-holder">
		<div class="postbox">
			<h3><span><?php _e( 'Recount Affiliate Earnings', 'affiliate-wp' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Use this tool to recount the earnings for all affiliates.', 'affiliate-wp' ); ?></p>
				<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=affiliate-wp-tools' ); ?>">
					<p>
						<?php wp_nonce_field( 'affwp_recount_aff_earnings_nonce', 'affwp_recount_aff_earnings_nonce' ); ?>
						<?php submit_button( __( 'Recount Earnings', 'affiliate-wp' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div>
<?php
}
add_action( 'affwp_tools_tab_general', 'affwp_general_tab' );

/**
 * Migration assistant tab
 *
 * @since       1.0
 * @return      void
 */
function affwp_migration_tab() {
?>
	<div class="metabox-holder">

		<div class="postbox">
			<div class="inside">
				<p><?php _e( 'These tools assist in migrating affiliate and referral data from existing platforms.', 'affiliate-wp' ); ?></p>
			</div><!-- .inside -->
		</div><!-- .postbox -->

		<div class="postbox">
			<h3><span><?php _e( 'Affiliates Pro', 'affiliate-wp' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Use this tool migrate existing affiliate / referral data from Affiliates Pro to Affiliate WP.', 'affiliate-wp' ); ?></p>
				<p><?php _e( '<strong>NOTE:</strong> this tool should only ever be used on a fresh install. If you have already collected affiliate or referral data, do not use this tool.', 'affiliate-wp' ); ?></p>
				<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'index.php?page=affiliate-wp-migrate&type=affiliates-pro' ); ?>">
					<p>
						<?php wp_nonce_field( 'affwp_affpro_migrate_nonce', 'affwp_affpro_migrate_nonce' ); ?>
						<?php submit_button( __( 'Migrate Data from Affiliates Pro', 'affiliate-wp' ), 'secondary', 'submit', false ); ?>
					</p>
				</form>
			</div><!-- .inside -->
		</div><!-- .postbox -->
	</div>
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

	<div class="metabox-holder">

		<div class="postbox">
			<h3><span><?php _e( 'Export Referrals', 'affiliate-wp' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Export referrals to a CSV file.', 'affiliate-wp' ); ?></p>
				<form method="post" enctype="multipart/form-data" action="<?php echo admin_url( 'admin.php?page=affiliate-wp-tools&tab=export_import' ); ?>">
					<p>
						<input type="text" name="user_name" id="user_name" class="affwp-user-search" autocomplete="off" placeholder="<?php _e( 'Affiliate name', 'affiliate-wp' ); ?>" />
						<input type="hidden" name="user_id" id="user_id" value=""/>
						<img class="affwp-ajax waiting" src="<?php echo admin_url('images/wpspin_light.gif'); ?>" style="display: none;"/>
						<div id="affwp_user_search_results"></div>
						<div class="description"><?php _e( 'To search for a affiliate, enter the affiliate\'s login name, first name, or last name. Leave blank to export referrals for all affiliates.', 'affiliate-wp' ); ?></div>
					</p>
					<p>
						<input type="text" class="affwp-datepicker" autocomplete="off" name="start_date" placeholder="<?php _e( 'From - mm/dd/yyyy', 'affiliate-wp' ); ?>"/>
						<input type="text" class="affwp-datepicker" autocomplete="off" name="end_date" placeholder="<?php _e( 'To - mm/dd/yyyy', 'affiliate-wp' ); ?>"/>
						<select name="status" id="status">
							<option value="0"><?php _e( 'All Statuses', 'affiliate-wp' ); ?></option>
							<option value="paid"><?php _e( 'Paid', 'affiliate-wp' ); ?></option>
							<option value="unpaid"><?php _e( 'Unpaid', 'affiliate-wp' ); ?></option>
							<option value="pending"><?php _e( 'Pending', 'affiliate-wp' ); ?></option>
							<option value="rejected"><?php _e( 'Rejected', 'affiliate-wp' ); ?></option>
						</select>
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
				<p><?php _e( 'Export the Affiliate WP settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'affiliate-wp' ); ?></p>
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
				<p><?php _e( 'Import the Affiliate WP settings from a .json file. This file can be obtained by exporting the settings on another site using the form above.', 'affiliate-wp' ); ?></p>
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
	</div>
<?php
}
add_action( 'affwp_tools_tab_export_import', 'affwp_export_import_tab' );


/**
 * Process a settings export that generates a .json file of the shop settings
 *
 * @since       1.0
 * @return      void
 */
function affwp_process_settings_export() {

	if( empty( $_POST['affwp_export_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['affwp_export_nonce'], 'affwp_export_nonce' ) )
		return;

	if( ! current_user_can( 'manage_options' ) )
		return;

	$settings = array();
	$settings = get_option( 'affwp_settings' );

	ignore_user_abort( true );

	if ( ! ini_get( 'safe_mode' ) )
		set_time_limit( 0 );

	nocache_headers();
	header( 'Content-Type: application/json; charset=utf-8' );
	header( 'Content-Disposition: attachment; filename=affwp-settings-export-' . date( 'm-d-Y' ) . '.json' );
	header( "Expires: 0" );

	echo json_encode( $settings );
	exit;
}
add_action( 'affwp_export_settings', 'affwp_process_settings_export' );

/**
 * Process a settings import from a json file
 *
 * @since 1.0
 * @return void
 */
function affwp_process_settings_import() {

	if( empty( $_POST['affwp_import_nonce'] ) )
		return;

	if( ! wp_verify_nonce( $_POST['affwp_import_nonce'], 'affwp_import_nonce' ) )
		return;

	if( ! current_user_can( 'manage_options' ) )
		return;

	$extension = end( explode( '.', $_FILES['import_file']['name'] ) );

    if( $extension != 'json' ) {
        wp_die( __( 'Please upload a valid .json file', 'affiliate-wp' ) );
    }

	$import_file = $_FILES['import_file']['tmp_name'];

	if( empty( $import_file ) ) {
		wp_die( __( 'Please upload a file to import', 'affiliate-wp' ) );
	}

	// Retrieve the settings from the file and convert the json object to an array
	$settings = affwp_object_to_array( json_decode( file_get_contents( $import_file ) ) );

	update_option( 'affwp_settings', $settings );

	wp_safe_redirect( admin_url( 'admin.php?page=affiliate-wp-tools&tab=export_import&affwp_notice=settings-imported' ) ); exit;

}
add_action( 'affwp_import_settings', 'affwp_process_settings_import' );

/**
 * The migration processing screen
 *
 * @since 1.0
 * @return void
 */
function affwp_migrate_admin() {
	$step   = isset( $_GET['step'] ) ? absint( $_GET['step'] ) : 1;
	$type   = isset( $_GET['type'] ) ? $_GET['type'] : false;
?>
	<div class="wrap">
		<h2><?php _e( 'Affiliate WP Migration', 'affiliate-wp' ); ?></h2>
		<div id="edd-upgrade-status">
			<p><?php _e( 'The upgrade process is running, please be patient. This could take several minutes to complete while license keys are upgraded in batches of 100.', 'affiliate-wp' ); ?></p>
			<p><strong><?php printf( __( 'Step %d running', 'affiliate-wp' ), $step ); ?>
		</div>
		<script type="text/javascript">
			document.location.href = "index.php?affwp_action=migrate&step=<?php echo absint( $_GET['step'] ); ?>&type=<?php echo $type; ?>";
		</script>
	</div>
<?php	
}