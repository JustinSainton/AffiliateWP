<?php
/**
 * Admin Options Page
 *
 * @package     AffiliateWP
 * @subpackage  Admin/Settings
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
function affwp_settings_admin() {

	$active_tab = isset( $_GET[ 'tab' ] ) && array_key_exists( $_GET['tab'], affwp_get_settings_tabs() ) ? $_GET[ 'tab' ] : 'general';

	ob_start();
	?>
	<div class="wrap">
		<h2 class="nav-tab-wrapper">
			<?php
			foreach( affwp_get_settings_tabs() as $tab_id => $tab_name ) {

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
			<form method="post" action="options.php">
				<table class="form-table">
				<?php
				settings_fields( 'affwp_settings' );
				do_settings_fields( 'affwp_settings_' . $active_tab, 'affwp_settings_' . $active_tab );
				?>
				</table>
				<?php submit_button(); ?>
			</form>
		</div><!-- #tab_container-->
	</div><!-- .wrap -->
	<?php
	echo ob_get_clean();
}


/**
 * Retrieve settings tabs
 *
 * @since 1.0
 * @return array $tabs
 */
function affwp_get_settings_tabs() {

	$tabs                 = array();
	$tabs['general']      = __( 'General', 'affiliate-wp' );
	$tabs['integrations'] = __( 'Integrations', 'affiliate-wp' );
	$tabs['emails']       = __( 'Emails', 'affiliate-wp' );
	$tabs['misc']         = __( 'Misc', 'affiliate-wp' );

	return apply_filters( 'affwp_settings_tabs', $tabs );
}

/**
 * Retrieve a list of all published pages
 *
 * On large sites this can be expensive, so only load if on the settings page or $force is set to true
 *
 * @since 1.0
 * @param bool $force Force the pages to be loaded even if not on settings
 * @return array $pages_options An array of the pages
 */
function affwp_get_pages( $force = false ) {

	$pages_options = array( 0 => '' ); // Blank option

	if( ( ! isset( $_GET['page'] ) || 'affiliate-wp-settings' != $_GET['page'] ) && ! $force ) {
		return $pages_options;
	}

	$pages = get_pages();
	if ( $pages ) {
		foreach ( $pages as $page ) {
			$pages_options[ $page->ID ] = $page->post_title;
		}
	}

	return $pages_options;
}
