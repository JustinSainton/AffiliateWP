<?php

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * User migration class that handles importing existing user accounts as affiliates
 *
 * @since 1.3
 * @return void
 */
class Affiliate_WP_Migrate_Users extends Affiliate_WP_Migrate_Base {

	/**
	 * Process the migration routine
	 *
	 * @since 1.3
	 * @return void
	 */
	public function process( $step = 1, $part = '' ) {

		switch( $part ) {

			case 'affiliates' :

				$affiliates = $this->do_users( $step );

				if( $affiliates ) {

					$this->step_forward( $step, 'affiliates' );

				}

				break;

		}

		$this->finish();

	}

	/**
	 * Move forward one step
	 *
	 * @since 1.3
	 * @return void
	 */
	public function step_forward( $step = 1, $part = '' ) {

		$step++;
		$redirect  = add_query_arg( array(
			'page' => 'affiliate-wp-migrate',
			'type' => 'users',
			'part' => $part,
			'step' => $step
		), admin_url( 'index.php' ) );
		wp_redirect( $redirect ); exit;

	}

	/**
	 * Import one batch of users
	 *
	 * @since 1.3
	 * @return void
	 */
	public function do_users( $step = 1 ) {

		global $wpdb;
		$offset = ($step - 1) * 100;
		$users  = $wpdb->get_results( "SELECT * FROM $wpdb->users ORDER BY ID LIMIT $offset, 100;" );

		if( $users ) {
			foreach( $users as $user ) {

				// try to get an existing affiliate based on the user_id
				$existing_affiliate = affiliate_wp()->affiliates->get_by( 'user_id', $user->ID );

				if( ! $existing_affiliate ) {

					$args = array(
						'status'          => 'active',
						'user_id'         => $user->ID,
						'payment_email'	  => $user->user_email,
						'date_registered' => $user->user_registered,
					);

					//insert a new affiliate - we need to always insert to make sure the affiliate_ids will match
					$id = affiliate_wp()->affiliates->insert( $args, 'affiliate' );

				}

			}

			return true;

		} else {

			// No users found, so all done
			return false;

		}

	}
}