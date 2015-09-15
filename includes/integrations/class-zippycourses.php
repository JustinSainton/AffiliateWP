<?php

class Affiliate_WP_ZippyCourses extends Affiliate_WP_Base {

    /**
     * The order object
     *
     * @access  private
     * @since   1.7
    */
    private $order;

    /**
     * Setup actions and filters
     *
     * @access  public
     * @since   1.7
    */
    public function init() {

        $this->context = 'zippycourses';

        add_action( 'admin_init', array( $this, 'metabox' ) );
        add_action( 'save_post', array( $this, 'save_meta' ) );

        add_action( 'zippy_event_order_status_change', array( $this, 'add_pending_referral' ), 10 );
        add_action( 'zippy_event_order_status_change', array( $this, 'mark_referral_complete' ), 10 );
        add_action( 'zippy_event_order_status_change', array( $this, 'revoke_referral_on_refund' ), 10 );

        add_filter( 'affwp_referral_reference_column', array( $this, 'reference_link' ), 10, 2 );
    }

    /**
     * Register product settings metabox
     *
     * @access  public
     * @since   1.7
    */
    public function metabox() {
        add_meta_box( 'zippy-affiliate-wp', __( 'Affiliate Settings', 'affiliate-wp' ), array($this, 'product_settings_mb'), 'product', 'side', 'default' );
    }

    /**
     * Adds per-product referral rate settings input fields
     *
     * @access  public
     * @since   1.7
    */
    public function product_settings_mb( $product ) {

        $rate     = get_post_meta( $product->ID, '_affwp_' . $this->context . '_product_rate', true );
        $disabled = get_post_meta( $product->ID, '_affwp_' . $this->context . '_referrals_disabled', true );
        ?>
        <p>
            <strong><?php _e( 'Affiliate Rates:', 'affiliate-wp' ); ?></strong>
        </p>

        <p>
            <label for="affwp_product_rate">
                <input type="text" name="_affwp_<?php echo $this->context; ?>_product_rate" id="affwp_product_rate" class="small-text" value="<?php echo esc_attr( $rate ); ?>" />
                <?php _e( 'Referral Rate', 'affiliate-wp' ); ?>
            </label>
        </p>

        <p>
            <label for="affwp_disable_referrals">
                <input type="checkbox" name="_affwp_<?php echo $this->context; ?>_referrals_disabled" id="affwp_disable_referrals" value="1"<?php checked( $disabled, true ); ?> />
                <?php printf( __( 'Disable referrals on this %s', 'affiliate-wp' ), 'Product' ); ?>
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
        if ( 'product' != $post->post_type ) {
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

    /**
     * Store a pending referral when a new order is created
     *
     * @access  public
     * @since   1.7
    */
    public function add_pending_referral( Zippy_Event $event ) {

        if ( $this->was_referred() ) {

            if( $event->new_status == 'pending' && $event->old_status != 'pending' ) {

                $order = $event->order;

                $customer = $order->getCustomer();

                if ( $customer === null || $this->is_affiliate_email( $customer->getEmail() ) ) {
                    return; // Customers cannot refer themselves
                }

                $total          = $order->getTotal();
                $product        = $order->getProduct();
                $description    = $product !== null ? get_the_title( $product->getId() ) : '';

                // insert a pending referral
                $referral_id = $this->insert_pending_referral( $total, $order->getId(), $description, array( $product->getId() ) );

            }

        }

    }

    /**
     * Mark referral as complete when payment is completed
     *
     * @access  public
     * @since   1.7
    */
    public function mark_referral_complete( Zippy_Event $event ) {

        if( $event->new_status == 'complete' && $event->old_status != 'complete' ) {

            $order = $event->order;

            $referral   = affiliate_wp()->referrals->get_by( 'reference', $order->getId(), $this->context );
            if( !$referral ) {
                return;
            }

            $this->complete_referral( $order->getId() );
            $amount     = affwp_currency_filter( affwp_format_amount( $referral->amount ) );
            $name       = affiliate_wp()->affiliates->get_affiliate_name( $referral->affiliate_id );
            $note       = sprintf( __( 'Referral #%d for %s recorded for %s', 'affiliate-wp' ), $referral->referral_id, $amount, $name );

            $order->addNote( array(
                'content'   => $note,
                'timestamp' => time()
            ) );

            $order->saveNotes();

        }

    }

    /**
     * Revoke the referral when the order is refunded
     *
     * @access  public
     * @since   1.7
    */
    public function revoke_referral_on_refund( Zippy_Event $event ) {

        if( ! affiliate_wp()->settings->get( 'revoke_on_refund' ) ) {
            return;
        }

        $revokable_statuses = array( 'refund', 'cancel', 'revoke' );

        if( in_array( $event->new_status, $revokable_statuses ) ) {

            $order = $event->order;

            $this->reject_referral( $order->getId() );

        }

    }

    /**
     * Sets up the reference link in the Referrals table
     *
     * @access  public
     * @since   1.7
    */
    public function reference_link( $reference = 0, $referral ) {

        if( empty( $referral->context ) || 'zippycourses' != $referral->context ) {

            return $reference;

        }

        $url = get_edit_post_link( $reference );

        return '<a href="' . esc_url( $url ) . '">' . get_the_title($reference) . '</a>';
    }

}

new Affiliate_WP_ZippyCourses;