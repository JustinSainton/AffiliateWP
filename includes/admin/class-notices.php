<?php

class Affiliate_WP_Admin_Notices {

	public function __construct() {

		add_action( 'admin_notices', array( $this, 'show_notices' ) );
	}


	public function show_notices() {

		if( empty( $_GET['affwp_notice'] ) ) {
			return;
		}

		$class = 'updated';

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

				$message = __( 'Affiliate deleted successfully', 'affiliate-wp' );

				break;

			case 'affiliate_delete_failed' :

				$message = __( 'Affiliate deletion failed, please try again', 'affiliate-wp' );
				$class   = 'error';

				break;

			case 'affiliate_active' :

				$message = __( 'Affiliate account activated', 'affiliate-wp' );

				break;

			case 'affiliate_inactive' :

				$message = __( 'Affiliate account deactivated', 'affiliate-wp' );

				break;

			case 'affiliate_accept' :

				$message = __( 'Affiliate request was accepted', 'affiliate-wp' );

				break;

			case 'affiliate_reject' :

				$message = __( 'Affiliate request was rejected', 'affiliate-wp' );

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

			case 'settings-imported' :

				$message = __( 'Settings successfully imported', 'affiliate-wp' );

				break;

		}

		if( ! empty( $message ) ) {

			echo '<div class="' . esc_attr( $class ) . '"><p>' .  $message  . '</p></div>';

		}

	}

}
new Affiliate_WP_Admin_Notices;