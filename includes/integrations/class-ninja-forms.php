<?php

class Affiliate_WP_Ninja_Forms extends Affiliate_WP_Base {

	/**
	 * Get thigns started
	 *
	 * @access  public
	 * @since   1.6
	 */
	public function init() {

		$this->context = 'ninja-forms';

		add_action( 'nf_save_sub', array( $this, 'add_referral' ) );
		add_action( 'untrash_post', array( $this, 'restore_referral' ) );
		add_action( 'delete_post', array( $this, 'revoke_referral_on_delete' ) );
		add_action( 'wp_trash_post', array( $this, 'revoke_referral_on_delete' ) );
		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );
		add_filter( 'ninja_forms_form_settings_restrictions', array( $this, 'add_restriction_setting' ) );

	}

	/**
	 * Record referral on submission
	 *
	 * @access  public
	 * @since   1.6
	 * @param   int $sub_id
	 */
	public function add_referral( $sub_id ) {

		if ( ! $this->was_referred() ) {
			return;
		}

		global $ninja_forms_processing;

		if ( ! $ninja_forms_processing->get_form_setting( 'affwp_allow_referrals' ) ) {
			return;
		}

		// Customers cannot refer themselves
		if ( $this->is_affiliate_email( $this->get_submitted_email() ) ) {
			return;
		}

		$description    = $ninja_forms_processing->get_form_setting( 'form_title' );
		$total          = $this->get_total();
		$referral_total = $this->calculate_referral_amount( $total, $sub_id, $ninja_forms_processing->get_form_ID() );

		$this->insert_pending_referral( $referral_total, $sub_id, $description );
		$this->complete_referral( $sub_id );

	}

	/**
	 * Restore a rejected referral when untrashing a submission
	 *
	 * @access  public
	 * @since   1.6
	 * @param   int $sub_id
	 */
	public function restore_referral( $sub_id = 0 ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		if( 'nf_sub' != get_post_type( $sub_id ) ) {
			return;
		}

		$referral_id = affiliate_wp()->referrals->get_column_by( 'referral_id', 'reference', $sub_id );

		if( $referral_id ) {
			affwp_set_referral_status( $referral_id, 'unpaid' );
		}

	}

	/**
	 * Revoke a referral when a submission is deleted or trashed
	 *
	 * @access  public
	 * @since   1.6
	 * @param   int $sub_id
	 */
	public function revoke_referral_on_delete( $sub_id = 0 ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		if( 'nf_sub' != get_post_type( $sub_id ) ) {
			return;
		}

		$this->reject_referral( $sub_id );

	}

	/**
	 * Build the reference URL
	 *
	 * @access  public
	 * @since   1.6
	 * @param   int    $reference
	 * @param   object $referral
	 * @return  string
	 */
	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'ninja-forms' != $referral->context ) {
			return $reference;
		}

		$url = admin_url( 'post.php?action=edit&post=' . $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';

	}

	/**
	 * Add custom form restriction setting
	 *
	 * @access  public
	 * @since   1.7
	 * @param   array $restrictions
	 * @return  array
	 */
	public function add_restriction_setting( $restrictions ) {

		$restrictions['settings'][] = array(
			'name'          => 'affwp_allow_referrals',
			'type'          => 'checkbox',
			'label'         => __( 'Allow Referrals', 'affiliate-wp' ),
			'desc'          => __( 'Enable affiliate referral creation for this form?', 'affiliate-wp' ),
			'default_value' => 0,
		);

		return $restrictions;

	}

	/**
	 * Get the email submitted in the form
	 *
	 * @access  public
	 * @since   1.6
	 */
	public function get_submitted_email() {

		global $ninja_forms_processing;

		$user_info = $ninja_forms_processing->get_user_info();
		if ( isset ( $user_info['billing']['email'] ) ) {
			$email = $user_info['billing']['email'];
		} else {
			$email = '';
		}

		return $email;

	}

	/**
	 * Get the purchase total
	 *
	 * @access  public
	 * @since   1.6
	 */
	public function get_total() {

		global $ninja_forms_processing;

		$total = $ninja_forms_processing->get_calc_total();

		if ( is_array ( $total ) ) {

			// If this is an array, grab the string total.

			if ( isset ( $total['total'] ) ) {

				$purchase_total = $total['total'];

			} else {

				$purchase_total = '';

			}

		} else {

			// This isn't an array, so $purchase_total can just be set to the string value.
			if ( ! empty( $total ) ) {
				$purchase_total = $total;
			} else {
				$purchase_total = 0.00;
			}

		}

		return affwp_sanitize_amount( $purchase_total );

	}

}

new Affiliate_WP_Ninja_Forms;
