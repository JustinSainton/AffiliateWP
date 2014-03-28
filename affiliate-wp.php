<?php
/**
 * Plugin Name: Affiliate WP
 * Plugin URI: http://affiliatewp.com
 * Description: Affiliate Plugin for WordPress
 * Author: Pippin Williamson
 * Author URI: http://pippinsplugins.com
 * Version: 0.1
 * Text Domain: affiliate-wp
 * Domain Path: languages
 *
 * Affiliate WP is distributed under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 2 of the License, or
 * any later version.
 *
 * Affiliate WP is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Affiliate WP. If not, see <http://www.gnu.org/licenses/>.
 *
 * @package AffiliateWP
 * @category Core
 * @author Pippin Williamson
 * @version 0.1
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

	private $version = '0.1';

	// Class properties
	public $affiliates;
	public $referrals;
	public $visits;
	public $tracking;
	public $settings;
	public $templates;


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
	 * @see EDD()
	 * @return The one true Affiliate_WP
	 */
	public static function instance() {
		if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Affiliate_WP ) ) {
			self::$instance = new Affiliate_WP;
			self::$instance->setup_constants();
			self::$instance->includes();
			self::$instance->load_textdomain();

			// Setup objects
			self::$instance->affiliates   = new Affiliate_WP_DB_Affiliates;
			self::$instance->referrals    = new Affiliate_WP_Referrals_DB;
			self::$instance->visits       = new Affiliate_WP_Visits_DB;
			self::$instance->tracking     = new Affiliate_WP_Tracking;
			self::$instance->settings     = new Affiliate_WP_Settings;
			self::$instance->templates    = new Affiliate_WP_Templates;
			self::$instance->login        = new Affiliate_WP_Login;
			self::$instance->register     = new Affiliate_WP_Register;
			self::$instance->integrations = new Affiliate_WP_Integrations;

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

		if( is_admin() ) {
		
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/ajax-actions.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/class-menu.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/affiliates/affiliates.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/class-notices.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/referrals/referrals.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/export/class-export.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/export/class-export-referrals.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/export/class-export-referrals-payout.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/reports/reports.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/settings/display-settings.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/visits/visits.php';
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/tools/tools.php';

		} else {
		
			require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-shortcodes.php';

		}

		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/actions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/admin/settings/class-settings.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-db.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-affiliates-db.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-graph.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-referrals-graph.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-integrations.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-login.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-referrals-db.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-register.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-templates.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-tracking.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/class-visits-db.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/affiliate-functions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/misc-functions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/referral-functions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/visit-functions.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/install.php';
		require_once AFFILIATEWP_PLUGIN_DIR . 'includes/scripts.php';
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
			// Look in global /wp-content/languages/edd folder
			load_textdomain( 'affiliate-wp', $mofile_global );
		} elseif ( file_exists( $mofile_local ) ) {
			// Look in local /wp-content/plugins/affiliate-wp/languages/ folder
			load_textdomain( 'affiliate-wp', $mofile_local );
		} else {
			// Load the default language files
			load_plugin_textdomain( 'affiliate-wp', false, $lang_dir );
		}
	}

	/*****************************
	* Affiliate helpers
	*****************************/

	public function is_valid_affiliate( $affiliate_id = 0 ) {

		$affiliate = affiliate_wp()->affiliates->get( 'affiliate_id', $affiliate_id );

		if( is_user_logged_in() ) {
			if( $this->get_affilite_id_of_user() == $affiliate ) {
				$affiliate = 0; // Affiliate ID is the same as the current user
			}
		}
		return ! empty( $affiliate );
	}

	public function get_affilite_id_of_user( $user_id = 0 ) {

		if( empty( $user_id ) ) {
			$user_id = get_current_user_id();
		}

		$affiliate_id = affiliate_wp()->affiliates->get_by( 'user_id', $user_id );

		if( ! empty( $affiliate_id ) ) {
			return $affiliate_id;
		}

		return false;
	}

	public function insert_affiliate( $args = array() ) {

		$defaults = array(
			'user_id' => 0,
		);

		$args = wp_parse_args( $args, $defaults );		

		return affiliate_wp()->affiliates->add( $args );
		
	}

	/*****************************
	* Referral helpers
	*****************************/

	public function get_referral_affiliate() {
		return affiliate_wp()->cookies->is_referral_cookie_set();
	}

	public function insert_referral( $args = array() ) {

		$defaults = array(
			'affiliate_id' => $this->get_referral_affiliate(),
			'status'       => 'pending',
			'ip'           => $this->get_ip()
		);

		$args = wp_parse_args( $args, $defaults );		

		$referral_id = affiliate_wp()->referrals->add( $args );
		
		// update the original visit with the referral ID

		return $referral_id;

	}

	/*****************************
	* Visit helpers
	*****************************/

	public function insert_visit( $args = array() ) {

		$defaults = array(
			'affiliate_id' => $this->get_referral_affiliate(),
			'ip'           => $this->get_ip(),
			'date'         => date( 'Y-m-d H:i:s' )
		);

		$args = wp_parse_args( $args, $defaults );		

		$visit_id = affiliate_wp()->visits->add( $args );
		
		// update the original visit with the referral ID

		return $visit_id;

	}

	/*****************************
	* Misc methods
	*****************************/

	public function get_ip() {
		if ( ! empty( $_SERVER['HTTP_CLIENT_IP'] ) ) {
			//check ip from share internet
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		} elseif ( ! empty( $_SERVER['HTTP_X_FORWARDED_FOR'] ) ) {
			//to check ip is pass from proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return apply_filters( 'affwp_get_ip', $ip );
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
 * @return object The one true Affiliate_WP Instance
 */
function affiliate_wp() {
	return Affiliate_WP::instance();
}

// Get Affiliate WP Running
affiliate_wp();
