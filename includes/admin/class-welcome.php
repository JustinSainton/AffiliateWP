<?php
/**
 * Welcome Page Class
 *
 * @package     Affiliate WP
 * @subpackage  Admin/Welcome
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.4
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * AFFWP_Welcome Class
 *
 * A general class for About and Credits page.
 *
 * @since 1.4
 */
class Affiliate_WP_Welcome {

	/**
	 * @var string The capability users should have to view the page
	 */
	public $minimum_capability = 'manage_options';

	/**
	 * Get things started
	 *
	 * @since 1.4
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'welcome'    ) );
	}

	/**
	 * Register the Dashboard Pages which are later hidden but these pages
	 * are used to render the Welcome and Credits pages.
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_menus() {
		// About Page
		add_dashboard_page(
			__( 'Welcome to Affiliate WP', 'affiliate-wp' ),
			__( 'Welcome to Affiliate WP', 'affiliate-wp' ),
			$this->minimum_capability,
			'affwp-about',
			array( $this, 'about_screen' )
		);

		// Getting Started Page
		add_dashboard_page(
			__( 'Getting started with Affiliate WP', 'affiliate-wp' ),
			__( 'Getting started with Affiliate WP', 'affiliate-wp' ),
			$this->minimum_capability,
			'affwp-getting-started',
			array( $this, 'getting_started_screen' )
		);

		// Credits Page
		add_dashboard_page(
			__( 'The people that build Affiliate WP', 'affiliate-wp' ),
			__( 'The people that build Affiliate WP', 'affiliate-wp' ),
			$this->minimum_capability,
			'affwp-credits',
			array( $this, 'credits_screen' )
		);
	}

	/**
	 * Hide Individual Dashboard Pages
	 *
	 * @access public
	 * @since 1.4
	 * @return void
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'affwp-about' );
		remove_submenu_page( 'index.php', 'affwp-getting-started' );
		remove_submenu_page( 'index.php', 'affwp-credits' );

		$page = isset( $_GET['page'] ) ? $_GET['page'] : false;

		if( 'affwp-about' != $page  && 'affwp-getting-started' != $page && 'affwp-credits' != $page ) {
			return;
		}

		// Badge for welcome page
		$badge_url = AFFILIATEWP_PLUGIN_URL . 'assets/images/affwp-badge.png';
		?>
		<style type="text/css" media="screen">
		/*<![CDATA[*/
		.affwp-badge {
			height: 151px;
			width: 185px;
			position: relative;
			color: #fff;
			font-weight: bold;
			font-size: 14px;
			text-align: center;
			background: url('<?php echo $badge_url; ?>') no-repeat;
		}

		.affwp-badge span {
			position: absolute;
			bottom: 20px;
			left: 0;
			width: 100%;
		}

		.about-wrap .affwp-badge {
			position: absolute;
			top: 0;
			right: 0;
		}

		.affwp-welcome-screenshots {
			float: right;
			margin-left: 10px!important;
		}
		/*]]>*/
		</style>
		<?php
	}

	/**
	 * Navigation tabs
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function tabs() {
		$selected = isset( $_GET['page'] ) ? $_GET['page'] : 'affwp-about';
		?>
		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php echo $selected == 'affwp-about' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'affwp-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "What's New", 'affiliate-wp' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'affwp-getting-started' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'affwp-getting-started' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Getting Started', 'affiliate-wp' ); ?>
			</a>
			<a class="nav-tab <?php echo $selected == 'affwp-credits' ? 'nav-tab-active' : ''; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'affwp-credits' ), 'index.php' ) ) ); ?>">
				<?php _e( 'Credits', 'affiliate-wp' ); ?>
			</a>
		</h2>
		<?php
	}

	/**
	 * Render About Screen
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function about_screen() {
		list( $display_version ) = explode( '-', AFFILIATEWP_VERSION );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Affiliate WP %s', 'affiliate-wp' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for Installing Affiliate WP %s. Affiliate marketing for WordPress you\'ll love ', 'affiliate-wp' ), $display_version ); ?></div>
			<div class="affwp-badge"><span><?php printf( __( 'Version %s', 'affiliate-wp' ), $display_version ); ?></span></div>

			<?php $this->tabs(); ?>

			<div class="changelog">
				<h3><?php _e( 'New features', 'affiliate-wp' );?></h3>

				<div class="feature-section">

					<img src="<?php echo AFFILIATEWP_PLUGIN_URL . 'assets/images/screenshots/order-details.png'; ?>" class="affwp-welcome-screenshots"/>

					<h4><?php _e( 'First New Feature', 'affiliate-wp' );?></h4>
					<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>
					<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>

					<h4><?php _e( 'Second New Feature', 'affiliate-wp' );?></h4>
					<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>
				</div>
			</div>

			<div class="changelog">
				<h3><?php _e( 'Additional Updates', 'affiliate-wp' );?></h3>

				<div class="feature-section col three-col">
					<div>
						<h4><?php _e( 'Update One', 'affiliate-wp' );?></h4>
					<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>

						<h4><?php _e( 'Update Two', 'affiliate-wp' );?></h4>
					<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>
					</div>

					<div>
						<h4><?php _e( 'Update Three', 'affiliate-wp' );?></h4>
					<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>

						<h4><?php _e( 'Update Four', 'affiliate-wp' );?></h4>
						<p><?php _e( 'A new API has been introduced for easily adding new template tags to purchase receipts and admin sale notifications.', 'affiliate-wp' );?></p>
					</div>

					<div class="last-feature">
						<h4><?php _e( 'Update Five', 'affiliate-wp' );?></h4>
					<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>

						<h4><?php _e( 'Update Six','affiliate-wp' );?></h4>
					<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>
					</div>
				</div>
			</div>

			<div class="return-to-dashboard">
				<a href="<?php echo esc_url( admin_url( 'admin.php?page=affiliate-wp-settings' ) ); ?>"><?php _e( 'Go to Affiliate WP Settings', 'affiliate-wp' ); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Render Getting Started Screen
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function getting_started_screen() {
		list( $display_version ) = explode( '-', AFFILIATEWP_VERSION );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Affiliate WP %s', 'affiliate-wp' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for Installing Affiliate WP %s. Affiliate marketing for WordPress you\'ll love ', 'affiliate-wp' ), $display_version ); ?></div>
			<div class="affwp-badge"><span><?php printf( __( 'Version %s', 'affiliate-wp' ), $display_version ); ?></span></div>

			<?php $this->tabs(); ?>

			<p class="about-description"><?php _e( 'Use the tips below to get started using Affiliate WP. You will be up and running in no time!', 'affiliate-wp' ); ?></p>

			<div class="changelog">
				
				<h3><?php _e( 'Overview of Affiliates and Referrals', 'affiliate-wp' );?></h3>

				<div class="feature-section">

					<img src="<?php echo AFFILIATEWP_PLUGIN_URL . 'assets/images/screenshots/edit-download.png'; ?>" class="affwp-welcome-screenshots"/>

					<h4><?php _e( 'Affiliates &rarr; Add New', 'affiliate-wp' );?></h4>
					<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>

				</div>

				<h3><?php _e( 'Adding Affiliates', 'affiliate-wp' );?></h3>

				<div class="feature-section">

					<img src="<?php echo AFFILIATEWP_PLUGIN_URL . 'assets/images/screenshots/edit-download.png'; ?>" class="affwp-welcome-screenshots"/>

					<h4><?php _e( 'Affiliates &rarr; Add New', 'affiliate-wp' );?></h4>
					<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>

					<h4><?php _e( 'Affiliate Registration', 'affiliate-wp' );?></h4>
					<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>


				</div>

				<h3><?php _e( 'Affiliate\'s Area', 'affiliate-wp' );?></h3>

				<div class="feature-section">

					<img src="<?php echo AFFILIATEWP_PLUGIN_URL . 'assets/images/screenshots/edit-download.png'; ?>" class="affwp-welcome-screenshots"/>

					<h4><?php _e( 'Affiliates &rarr; Add New', 'affiliate-wp' );?></h4>
					<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>

				</div>

			</div>

			<div class="changelog">
				<h3><?php _e( 'Quick Terminology', 'affiliate-wp' );?></h3>

				<div class="feature-section col three-col">
					<div>
						<h4><?php _e( 'Affiliates', 'affiliate-wp' );?></h4>
						<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>
					</div>

					<div>
						<h4><?php _e( 'Referrals', 'affiliate-wp' );?></h4>
						<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>

					</div>

					<div class="last-feature">
						<h4><?php _e( 'Referrals', 'affiliate-wp' );?></h4>
						<p><?php _e( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua.', 'affiliate-wp' );?></p>

					</div>
				</div>
			</div>


			<div class="changelog">
				<h3><?php _e( 'Need Help?', 'affiliate-wp' );?></h3>

				<div class="feature-section">

					<h4><?php _e( 'Phenomenal Support','affiliate-wp' );?></h4>
					<p><?php _e( 'We do our best to provide the best support we can. If you encounter a problem or have a question, visit our <a href="https://affiliatewp.com/support">support</a> page to open a ticket.', 'affiliate-wp' );?></p>
				</div>
			</div>

		</div>
		<?php
	}

	/**
	 * Render Credits Screen
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function credits_screen() {
		list( $display_version ) = explode( '-', AFFILIATEWP_VERSION );
		?>
		<div class="wrap about-wrap">
			<h1><?php printf( __( 'Welcome to Affiliate WP %s', 'affiliate-wp' ), $display_version ); ?></h1>
			<div class="about-text"><?php printf( __( 'Thank you for updating to the latest version! Affiliate WP %s is ready to make your online store faster, safer and better!', 'affiliate-wp' ), $display_version ); ?></div>
			<div class="affwp-badge"><span><?php printf( __( 'Version %s', 'affiliate-wp' ), $display_version ); ?></span></div>

			<?php $this->tabs(); ?>

			<p class="about-description"><?php _e( 'Affiliate WP is created by a worldwide team of developers who aim to provide the #1 eCommerce platform for selling digital goods through WordPress.', 'affiliate-wp' ); ?></p>

			<?php echo $this->contributors(); ?>
		</div>
		<?php
	}


	/**
	 * Render Contributors List
	 *
	 * @since 1.0
	 * @uses AFFWP_Welcome::get_contributors()
	 * @return string $contributor_list HTML formatted list of all the contributors for affwp
	 */
	public function contributors() {
		$contributors = $this->get_contributors();

		if ( empty( $contributors ) )
			return '';

		$contributor_list = '<ul class="wp-people-group">';

		foreach ( $contributors as $contributor ) {
			$contributor_list .= '<li class="wp-person">';
			$contributor_list .= sprintf( '<a href="%s" title="%s">',
				esc_url( 'https://github.com/' . $contributor->login ),
				esc_html( sprintf( __( 'View %s', 'affiliate-wp' ), $contributor->login ) )
			);
			$contributor_list .= sprintf( '<img src="%s" width="64" height="64" class="gravatar" alt="%s" />', esc_url( $contributor->avatar_url ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
			$contributor_list .= sprintf( '<a class="web" href="%s">%s</a>', esc_url( 'https://github.com/' . $contributor->login ), esc_html( $contributor->login ) );
			$contributor_list .= '</a>';
			$contributor_list .= '</li>';
		}

		$contributor_list .= '</ul>';

		return $contributor_list;
	}

	/**
	 * Retreive list of contributors from GitHub.
	 *
	 * @access public
	 * @since 1.0
	 * @return array $contributors List of contributors
	 */
	public function get_contributors() {
		$contributors = get_transient( 'affwp_contributors' );

		if ( false !== $contributors )
			return $contributors;

		$response = wp_remote_get( 'https://api.github.com/repos/affiliatewp/AffiliateWP/contributors', array( 'sslverify' => false ) );

		if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) )
			return array();

		$contributors = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! is_array( $contributors ) )
			return array();

		set_transient( 'affwp_contributors', $contributors, 3600 );

		return $contributors;
	}

	/**
	 * Sends user to the Welcome page on first activation of affwp as well as each
	 * time affwp is upgraded to a new version
	 *
	 * @access public
	 * @since 1.0
	 * @return void
	 */
	public function welcome() {

		// Bail if no activation redirect
		if ( ! get_transient( '_affwp_activation_redirect' ) )
			return;

		// Delete the redirect transient
		delete_transient( '_affwp_activation_redirect' );

		// Bail if activating from network, or bulk
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) )
			return;

		$upgrade = get_option( 'affwp_version_upgraded_from' );

		if( ! $upgrade ) { // First time install
			wp_safe_redirect( admin_url( 'index.php?page=affwp-getting-started' ) ); exit;
		} else { // Update
			wp_safe_redirect( admin_url( 'index.php?page=affwp-about' ) ); exit;
		}
	}
}
new Affiliate_WP_Welcome;
