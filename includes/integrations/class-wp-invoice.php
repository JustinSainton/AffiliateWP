<?php

class Affiliate_WP_Invoice extends Affiliate_WP_Base {

  /**
   * Run
   */
  public function init() {
    $this->context = 'wp-invoice';

    add_action( 'wpi_successful_payment', array( $this, 'track_successful_payment' ) );
  }

  /**
   * Track Successful Payment
   * @param $invoice
   */
  public function track_successful_payment( $invoice ) {

    if( $this->was_referred() ) {

      $new_invoice = new WPI_Invoice();
      $new_invoice->load_invoice("id={$invoice->data['invoice_id']}");

      $this->insert_pending_referral(
        $new_invoice->data['total_payments']?$new_invoice->data['total_payments']:$new_invoice->data['net'],
        $new_invoice->data['invoice_id'],
        $new_invoice->data['post_title']
      );

      if ( $new_invoice->data['post_status'] == 'paid' ) {
        $this->complete_referral( $new_invoice->data['invoice_id'] );
      }

    }
  }

}

new Affiliate_WP_Invoice;
