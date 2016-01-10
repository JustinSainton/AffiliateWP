<?php

class Affiliate_WP_Admin_Notices {

	public function __construct() {

		add_action( 'admin_notices', array( $this, 'show_notices' ) );
		add_action( 'affwp_dismiss_notices', array( $this, 'dismiss_notices' ) );
	}


	public function show_notices() {

		$integrations = affiliate_wp()->integrations->get_enabled_integrations();

		if( empty( $integrations ) && ! get_user_meta( get_current_user_id(), '_affwp_no_integrations_dismissed', true ) ) {
			echo '<div class="error">';
				echo '<p>' . sprintf( __( 'There are currently no AffiliateWP <a href="%s">integrations</a> enabled. If you are using AffiliateWP without any integrations, you may disregard this message.', 'affiliate-wp' ), admin_url( 'admin.php?page=affiliate-wp-settings&tab=integrations' ) ) . '</p>';
				echo '<p><a href="' . wp_nonce_url( add_query_arg( array( 'affwp_action' => 'dismiss_notices', 'affwp_notice' => 'no_integrations' ) ), 'affwp_dismiss_notice', 'affwp_dismiss_notice_nonce' ) . '">' . __( 'Dismiss Notice', 'affiliate-wp' ) . '</a></p>';
			echo '</div>';
		}

		$class = 'updated';

		if ( isset( $_GET['settings-updated'] ) && $_GET['settings-updated'] && isset( $_GET['page'] ) && $_GET['page'] == 'affiliate-wp-settings' ) {
			$message = __( 'Settings updated.', 'affiliate-wp' );
		}

		if ( isset( $_GET['affwp_notice'] ) && $_GET['affwp_notice'] ) {

			switch( $_GET['affwp_notice'] ) {

				case 'affiliate_added' :

					$message = __( 'Affiliate added successfully', 'affiliate-wp' );

					break;

				case 'affiliate_added_failed' :

					$message = __( 'Affiliate wasn\'t added, please try again.', 'affiliate-wp' );
					$class   = 'error';

					break;

				case 'affiliate_updated' :

					$message = __( 'Affiliate updated successfully', 'affiliate-wp' );

					$message .= '<p>'. sprintf( __( '<a href="%s">Back to Affiliates</a>', 'affiliate-wp' ), admin_url( 'admin.php?page=affiliate-wp-affiliates' ) ) .'</p>';

					break;

				case 'affiliate_update_failed' :

					$message = __( 'Affiliate update failed, please try again', 'affiliate-wp' );
					$class   = 'error';

					break;

				case 'affiliate_deleted' :

					$message = __( 'Affiliate account(s) deleted successfully', 'affiliate-wp' );

					break;

				case 'affiliate_delete_failed' :

					$message = __( 'Affiliate deletion failed, please try again', 'affiliate-wp' );
					$class   = 'error';

					break;

				case 'affiliate_activated' :

					$message = __( 'Affiliate account activated', 'affiliate-wp' );

					break;

				case 'affiliate_deactivated' :

					$message = __( 'Affiliate account deactivated', 'affiliate-wp' );

					break;

				case 'affiliate_accepted' :

					$message = __( 'Affiliate request was accepted', 'affiliate-wp' );

					break;

				case 'affiliate_rejected' :

					$message = __( 'Affiliate request was rejected', 'affiliate-wp' );

					break;

				case 'stats_recounted' :

					$message = __( 'Affiliate stats have been recounted!', 'affiliate-wp' );

					break;

				case 'referral_added' :

					$message = __( 'Referral added successfully', 'affiliate-wp' );

					break;

				case 'referral_updated' :

					$message = __( 'Referral updated successfully', 'affiliate-wp' );

					break;

				case 'referral_update_failed' :

					$message = __( 'Referral update failed, please try again', 'affiliate-wp' );

					break;

				case 'referral_deleted' :

					$message = __( 'Referral deleted successfully', 'affiliate-wp' );

					break;

				case 'referral_delete_failed' :

					$message = __( 'Referral deletion failed, please try again', 'affiliate-wp' );
					$class   = 'error';

					break;

				case 'creative_updated' :

					$message = __( 'Creative updated successfully', 'affiliate-wp' );

					$message .= '<p>'. sprintf( __( '<a href="%s">Back to Creatives</a>', 'affiliate-wp' ), admin_url( 'admin.php?page=affiliate-wp-creatives' ) ) .'</p>';

					break;

				case 'creative_added' :

					$message = __( 'Creative added successfully', 'affiliate-wp' );

					break;

				case 'creative_deleted' :

					$message = __( 'Creative deleted successfully', 'affiliate-wp' );

					break;

				case 'creative_activated' :

					$message = __( 'Creative activated', 'affiliate-wp' );

					break;

				case 'creative_deactivated' :

					$message = __( 'Creative deactivated', 'affiliate-wp' );

					break;

				case 'settings-imported' :

					$message = __( 'Settings successfully imported', 'affiliate-wp' );

					break;

			}
		}

		if ( ! empty( $message ) ) {
			echo '<div class="' . esc_attr( $class ) . '"><p><strong>' .  $message  . '</strong></p></div>';
		}

		if ( 'expired' === affiliate_wp()->settings->check_license() ) {
			echo '<div class="error info"><p>' . __( 'Your license key for AffiliateWP has expired. Please renew your license to re-enable automatic updates.', 'affiliate-wp' ) . '</p></div>';
		} elseif ( 'valid' !== affiliate_wp()->settings->check_license() ) {
			echo '<div class="notice notice-info"><p>' . sprintf( __( 'Please <a href="%s">enter and activate</a> your license key for AffiliateWP to enable automatic updates.', 'affiliate-wp' ), admin_url( 'admin.php?page=affiliate-wp-settings' ) ) . '</p></div>';
		}

	}

	/**
	 * Dismiss admin notices when Dismiss links are clicked
	 *
	 * @since 1.7.5
	 * @return void
	 */
	function dismiss_notices() {
		if( ! isset( $_GET['affwp_dismiss_notice_nonce'] ) || ! wp_verify_nonce( $_GET['affwp_dismiss_notice_nonce'], 'affwp_dismiss_notice') ) {
			wp_die( __( 'Security check failed', 'affiliate-wp' ), __( 'Error', 'affiliate-wp' ), array( 'response' => 403 ) );
		}

		if( isset( $_GET['affwp_notice'] ) ) {
			update_user_meta( get_current_user_id(), '_affwp_' . $_GET['affwp_notice'] . '_dismissed', 1 );
			wp_redirect( remove_query_arg( array( 'affwp_action', 'affwp_notice' ) ) );
			exit;
		}
	}
}
new Affiliate_WP_Admin_Notices;
