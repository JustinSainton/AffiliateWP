<?php

class Affiliate_WP_Admin_Notices {

	public function __construct() {

		add_action( 'admin_notices', array( $this, 'show_notices' ) );
	}


	public function show_notices() {

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

				case 'affiliate_actived' :

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

	}

}
new Affiliate_WP_Admin_Notices;