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

/**
 * Options Page
 *
 * Renders the options page contents.
 *
 * @since 1.0
 * @return void
 */
function affwp_tools_admin() {

	$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], affwp_get_tools_tabs() ) ? $_GET[ 'tab' ] : 'general';

	ob_start();
	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( affwp_get_tools_tabs() as $tab_id => $tab_name ) {

				$tab_url = add_query_arg( array(
					'settings-updated' => false,
					'tab' => $tab_id
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
	$tabs['general']       = __( 'General', 'affiliate-wp' );
	$tabs['migration']     = __( 'Migration Assistant', 'affiliate-wp' );
	$tabs['export_import'] = __( 'Export / Import', 'affiliate-wp' );

	return apply_filters( 'affwp_tools_tabs', $tabs );
}

/**
 * Tools
 *
 * Shows the tools panel which contains EDD-specific tools including the
 * built-in import/export system.
 *
 * @since       1.8
 * @author      Daniel J Griffiths
 * @return      void
 */
function affwp_export_import_tab() {
?>

	<div class="metabox-holder">
		<div class="postbox">
			<h3><span><?php _e( 'Export Settings', 'affiliate-wp' ); ?></span></h3>
			<div class="inside">
				<p><?php _e( 'Export the Affiliate WP settings for this site as a .json file. This allows you to easily import the configuration into another site.', 'affiliate-wp' ); ?></p>
				<p><?php printf( __( 'To export shop data (purchases, customers, etc), visit the <a href="%s">Reports</a> page.', 'affiliate-wp' ), admin_url( 'edit.php?post_type=download&page=edd-reports&tab=export' ) ); ?>
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

<?php
}
add_action( 'affwp_tools_tab_export_import', 'affwp_export_import_tab' );


/**
 * Process a settings export that generates a .json file of the shop settings
 *
 * @since       1.7
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
 * @since 1.7
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
