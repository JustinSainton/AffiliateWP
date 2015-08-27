<?php

class Affiliate_WP_MemberPress extends Affiliate_WP_Base {

	/**
	 * Get things started
	 *
	 * @access  public
	 * @since   1.5
	*/
	public function init() {

		$this->context = 'memberpress';

		add_action( 'mepr-txn-status-pending', array( $this, 'add_pending_referral' ), 10 );
		add_action( 'mepr-txn-status-complete', array( $this, 'mark_referral_complete' ), 10 );
		add_action( 'mepr-txn-status-refunded', array( $this, 'revoke_referral_on_refund' ), 10 );

		add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );

		// Per membership referral rates
		add_action( 'add_meta_boxes', array( $this, 'register_metabox' ) );
		add_action( 'save_post', array( $this, 'save_meta' ) );

	}

	/**
	 * Store a pending referraling when a one-time product is purchased
	 *
	 * @access  public
	 * @since   1.5
	*/
	public function add_pending_referral( $txn ) {

		// Pending referrals are only created for one-time purchases
		if ( $this->was_referred() ) {

			$referral = affiliate_wp()->referrals->get_by( 'reference', $txn->id, $this->context );

			if ( ! empty( $referral ) ) {
				return;
			}

			$user = get_userdata( $txn->user_id );

			// Customers cannot refer themselves
			if ( ! empty( $user->user_email ) && $this->is_affiliate_email( $user->user_email ) ) {
				return;
			}

			if( get_post_meta( $txn->product_id, '_affwp_' . $this->context . '_referrals_disabled', true ) ) {
				return; // Referrals are disabled on this membership
			}

			// get referral total
			$referral_total = $this->calculate_referral_amount( $txn->amount, $txn->id, $txn->product_id );

			// insert a pending referral
			$this->insert_pending_referral( $referral_total, $txn->id, get_the_title( $txn->product_id ), array(), $txn->subscription_id );

		}
	}

	/**
	 * Update a referral to Unpaid when a one-time purchase is completed
	 *
	 * @access  public
	 * @since   1.5
	*/
	public function mark_referral_complete( $txn ) {

		// Completes a referral for a one-time purchase
		$this->complete_referral( $txn->id );
	}

	/**
	 * Reject referrals when the transaction is refunded
	 *
	 * @access  public
	 * @since   1.5
	*/
	public function revoke_referral_on_refund( $txn ) {

		if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
			return;
		}

		$this->reject_referral( $txn->id );

	}

	/**
	 * Setup the reference link
	 *
	 * @access  public
	 * @since   1.5
	*/
	public function reference_link( $reference = 0, $referral ) {

		if( empty( $referral->context ) || 'memberpress' != $referral->context ) {

			return $reference;

		}

		$url = admin_url( 'admin.php?page=memberpress-trans&search=' . $reference );

		return '<a href="' . esc_url( $url ) . '">' . $reference . '</a>';
	}

	/**
	 * Register the metabox for membership rates
	 *
	 * @access  public
	 * @since   1.7
	*/
	public function register_metabox() {

		add_meta_box( 'affwp_level_rate', __( 'Affiliate Rate', 'affiliate-wp' ),  array( $this, 'render_metabox' ), 'memberpressproduct', 'side', 'low' );

	}

	/**
	 * Render the affiliate rates metabox
	 *
	 * @access  public
	 * @since   1.7
	*/
	public function render_metabox() {

		global $post;

		$product_id = ! empty( $post ) ? $post->ID : 0;

		$rate       = get_post_meta( $product_id, '_affwp_' . $this->context . '_product_rate', true );
		$disabled   = get_post_meta( $product_id, '_affwp_' . $this->context . '_referrals_disabled', true );
?>
		<p>
			<label for="affwp_product_rate">
				<input type="text" name="_affwp_<?php echo $this->context; ?>_product_rate" id="affwp_product_rate" class="small-text" value="<?php echo esc_attr( $rate ); ?>" />
				<?php _e( 'Referral Rate', 'affiliate-wp' ); ?>
			</label>
		</p>

		<p>
			<label for="affwp_disable_referrals">
				<input type="checkbox" name="_affwp_<?php echo $this->context; ?>_referrals_disabled" id="affwp_disable_referrals" value="1"<?php checked( $disabled, true ); ?> />
				<?php _e( 'Disable referrals on this membership', 'affiliate-wp' ); ?>
			</label>
		</p>

		<p><?php _e( 'These settings will be used to calculate affiliate earnings per-sale. Leave blank to use the site default referral rate.', 'affiliate-wp' ); ?></p>
<?php
	}

	/**
	 * Saves per-product referral rate settings input fields
	 *
	 * @access  public
	 * @since   1.7
	*/
	public function save_meta( $post_id = 0 ) {

		// If this is an autosave, our form has not been submitted, so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return $post_id;
		}

		// Don't save revisions and autosaves
		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return $post_id;
		}

		$post = get_post( $post_id );

		if( ! $post ) {
			return $post_id;
		}

		// Check post type is product
		if ( 'memberpressproduct' != $post->post_type ) {
			return $post_id;
		}

		// Check user permission
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return $post_id;
		}

		if( ! empty( $_POST['_affwp_' . $this->context . '_product_rate'] ) ) {

			$rate = sanitize_text_field( $_POST['_affwp_' . $this->context . '_product_rate'] );
			update_post_meta( $post_id, '_affwp_' . $this->context . '_product_rate', $rate );

		} else {

			delete_post_meta( $post_id, '_affwp_' . $this->context . '_product_rate' );

		}

		if( isset( $_POST['_affwp_' . $this->context . '_referrals_disabled'] ) ) {

			update_post_meta( $post_id, '_affwp_' . $this->context . '_referrals_disabled', 1 );

		} else {

			delete_post_meta( $post_id, '_affwp_' . $this->context . '_referrals_disabled' );

		}

	}
}
new Affiliate_WP_MemberPress;
