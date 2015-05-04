<?php
/**
 * Plugin Name: AffiliateWP
 * Plugin URI: http://affiliatewp.com
 * Description: Affiliate Plugin for WordPress
 * Author: Pippin Williamson and Andrew Munro
 * Author URI: http://affiliatewp.com
 * Version: 1.6.2
 * Text Domain: affiliate-wp
 * Domain Path: languages
 *
 * AffiliateWP is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * AffiliateWP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AffiliateWP. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package AffiliateWP
 * @category Core
 * @author Pippin Williamson
 * @version 1.6.2
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'Affiliate_WP' ) ) :

/**
 * Main Affiliate_WP Class
 *
 * @since 1.0
 */
final class Affiliate_WP {
	/** Singleton *************************************************************/

	/**
	 * @var Affiliate_WP The one true Affiliate_WP
	 * @since 1.0
	 */
	private static $instance;

	/**
	 * The version number of AffiliateWP
	 *
	 * @since 1.0
	 */
	private $version = '1.6.2';

	/**
	 * The affiliates DB instance variable.
	 *
	 * @var Affiliate_WP_DB_Affiliates
	 * @since 1.0
	 */
	public $affiliates;

	/**
	 * The affiliate meta DB instance variable.
	 *
	 * @var Affiliate_WP_Affiliate_Meta_DB
	 * @since 1.6
	 */
	public $affiliate_meta;

	/**
	 * The referrals instance variable.
	 *
	 * @var Affiliate_WP_Referrals_DB
	 * @since 1.0
	 */
	public $referrals;

	/**
	 * The visits DB instance variable
	 *
	 * @var Affiliate_WP_Visits_DB
	 * @since 1.0
	 */
	public $visits;

	/**
	 * The settings instance variable
	 *
	 * @var Affiliate_WP_Settings
	 * @since 1.0
	 */
	public $settings;

	/**
	 * The affiliate tracking handler instance variable
	 *
	 * @var Affiliate_WP_Tracking
	 * @since 1.0
	 */
	public $tracking;

	/**
	 * The template loader instance variable
	 *
	 * @var Affiliate_WP_Templates
	 * @since 1.0
	 */
	public $templates;

	/**
	 * The affiliate login handler instance variable
	 *
	 * @var Affiliate_WP_Login
	 * @since 1.0
	 */
	public $login;

	/**
	 * The affiliate registration handler instance variable
	 *
	 * @var Affiliate_WP_Register
	 * @since 1.0
	 */
	public $register;

	/**
	 * The integrations handler instance variable
	 *
	 * @var Affiliate_WP_Integrations
	 * @since 1.0
	 */
	public $integrations;

	/**
	 * The email notification handler instance variable
	 *
	 * @var Affiliate_WP_Emails
	 * @since 1.0
	 */
	public $emails;

	/**
	 * The creatives instance variable
	 *
	 * @var Affiliate_WP_Creatives_DB
	 * @since 1.2
	 */
	public $creatives;

	/**
	 * The creative class instance variable
	 *
	 * @var Affiliate_WP_Creatives
	 * @since 1.3
	 */
	public $creative;

	/**
	 * Main Affiliate_WP Instance
	 *
	 * Insures that only one instance of Affiliate_WP exists in memory at any one
	 * time. Also prevents needing to define globals all over the place.
	 *
	 * @since 1.0
	 * @static
	 * @staticvar array $instance
	 * @uses Affiliate_WP::setup_globals() Setup the globals needed
	 * @uses Affiliate_WP::includes() Include the required files
	 * @uses Affiliate_WP::setup_actions() Setup the hooks and actions
	 * @uses Affiliate_WP::updater() Setup the plugin updater
	 * @return Affiliate_WP
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Affiliate_WP ) ) {
			self::$instance = new Affiliate_WP;
			self::$instance->setup_constants();
			self::$instance->includes();

			add_action( 'plugins_loaded', array( self::$instance, 'setup_objects' ), -1 );
			add_action( 'plugins_loaded', array( self::$instance, 'load_textdomain' ) );
		}
		return self::$instance;
	}

	/**
	 * Throw error on object clone
	 *
	 * The whole idea of the singleton design pattern is that there is a single
	 * object therefore, we don't want the object to be cloned.
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __clone() {
		// Cloning instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliate-wp' ), '1.0' );
	}

	/**
	 * Disable unserializing of the class
	 *
	 * @since 1.0
	 * @access protected
	 * @return void
	 */
	public function __wakeup() {
		// Unserializing instances of the class is forbidden
		_doing_it_wrong( __FUNCTION__, __( 'Cheatin&#8217; huh?', 'affiliate-wp' ), '1.0' );
	}

	/**
	 * Setup plugin constants
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function setup_constants() {
		// Plugin version
		if ( ! defined( 'AFFILIATEWP_VERSION' ) ) {
			define( 'AFFILIATEWP_VERSION', $this->version );
		}

		// Plugin Folder Path
		if ( ! defined( 'AFFILIATEWP_PLUGIN_DIR' ) ) {
			define( 'AFFILIATEWP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
		}

		// Plugin Folder URL
		if ( ! defined( 'AFFILIATEWP_PLUGIN_URL' ) ) {
			define( 'AFFILIATEWP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
		}

		// Plugin Root File
		if ( ! defined( 'AFFILIATEWP_PLUGIN_FILE' ) ) {
			define( 'AFFILIATEWP_PLUGIN_FILE', __FILE__ );
		}
	}

	/**
	 * Include required files
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function includes() {

		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/actions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/settings/class-settings.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-db.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-affiliates-db.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-capabilities.php';

		if( is_admin() ) {

			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/AFFWP_Plugin_Updater.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/affiliates/actions.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/ajax-actions.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/class-addon-updater.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/class-menu.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/affiliates/affiliates.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/class-notices.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/creatives/actions.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/creatives/creatives.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/overview/overview.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/referrals/actions.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/referrals/referrals.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/reports/reports.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/visits/visits.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/tools.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/class-upgrades.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/class-welcome.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/plugins.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/class-migrate.php';

		} else {

			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-shortcodes.php';

		}

		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/emails/class-affwp-emails.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/emails/functions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/emails/actions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-graph.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-referrals-graph.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-visits-graph.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-integrations.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-login.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-referrals-db.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-register.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-templates.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-tracking.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-visits-db.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-creatives-db.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-creatives.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-affiliate-meta-db.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/affiliate-functions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/affiliate-meta-functions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/misc-functions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/referral-functions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/visit-functions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/creative-functions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/install.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/plugin-compatibility.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/scripts.php';
	}

	/**
	 * Setup all objects
	 *
	 * @access private
	 * @since 1.6.2
	 * @return void
	 */
	public function setup_objects() {

		self::$instance->affiliates     = new Affiliate_WP_DB_Affiliates;
		self::$instance->affiliate_meta = new Affiliate_WP_Affiliate_Meta_DB;
		self::$instance->referrals      = new Affiliate_WP_Referrals_DB;
		self::$instance->visits         = new Affiliate_WP_Visits_DB;
		self::$instance->settings       = new Affiliate_WP_Settings;
		self::$instance->tracking       = new Affiliate_WP_Tracking;
		self::$instance->templates      = new Affiliate_WP_Templates;
		self::$instance->login          = new Affiliate_WP_Login;
		self::$instance->register       = new Affiliate_WP_Register;
		self::$instance->integrations   = new Affiliate_WP_Integrations;
		self::$instance->emails         = new Affiliate_WP_Emails;
		self::$instance->creatives      = new Affiliate_WP_Creatives_DB;
		self::$instance->creative       = new Affiliate_WP_Creatives;

		self::$instance->updater();
	}

	/**
	 * Plugin Updater
	 *
	 * @access private
	 * @since 1.0
	 * @return void
	 */
	private function updater() {

		if( ! is_admin() ) {
			return;
		}

		$license_key = $this->settings->get( 'license_key' );

		if( $license_key ) {
			// setup the updater
			$affwp_updater = new AFFWP_Plugin_Updater( 'http://affiliatewp.com', __FILE__, array(
					'version' 	=> AFFILIATEWP_VERSION,
					'license' 	=> $license_key,
					'item_name' => 'AffiliateWP',
					'item_id'   => 17,
					'author' 	=> 'Pippin Williamson'
				)
			);
		}
	}

	/**
	 * Loads the plugin language files
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function load_textdomain() {

		// Set filter for plugin's languages directory
		$lang_dir = dirname( plugin_basename( AFFILIATEWP_PLUGIN_FILE ) ) . '/languages/';
		$lang_dir = apply_filters( 'aff_wp_languages_directory', $lang_dir );

		// Traditional WordPress plugin locale filter
		$locale        = apply_filters( 'plugin_locale',  get_locale(), 'affiliate-wp' );
		$mofile        = sprintf( '%1$s-%2$s.mo', 'affiliate-wp', $locale );

		// Setup paths to current locale file
		$mofile_local  = $lang_dir . $mofile;
		$mofile_global = WP_LANG_DIR . '/affiliate-wp/' . $mofile;

		if ( file_exists( $mofile_global ) ) {
			// Look in global /wp-content/languages/affiliate-wp/ folder
			load_textdomain( 'affiliate-wp', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/affiliate-wp/languages/ folder
			load_textdomain( 'affiliate-wp', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'affiliate-wp', false, $lang_dir );
		}
	}
}

endif; // End if class_exists check


/**
 * The main function responsible for returning the one true Affiliate_WP
 * Instance to functions everywhere.
 *
 * Use this function like you would a global variable, except without needing
 * to declare the global.
 *
 * Example: <?php $affiliate_wp = affiliate_wp(); ?>
 *
 * @since 1.0
 * @return Affiliate_WP The one true Affiliate_WP Instance
 */
function affiliate_wp() {
	return Affiliate_WP::instance();
}
affiliate_wp();
