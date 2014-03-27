<?php

class Affiliate_WP_Referrals_Graph extends Affiliate_WP_Graph {

	/**
	 * Get things started
	 *
	 * @since 1.0
	 */
	public function __construct( $_data = array() ) {

		if( empty( $_data ) ) {

			$this->data = $this->get_data();

		}

		// Generate unique ID
		$this->id   = md5( rand() );

		// Setup default options;
		$this->options = array(
			'y_mode'          => null,
			'y_decimals'      => 0,
			'x_decimals'      => 0,
			'y_position'      => 'right',
			'time_format'     => '%d/%b',
			'ticksize_unit'   => 'day',
			'ticksize_num'    => 1,
			'multiple_y_axes' => false,
			'bgcolor'         => '#f9f9f9',
			'bordercolor'     => '#ccc',
			'color'           => '#bbb',
			'borderwidth'     => 2,
			'bars'            => false,
			'lines'           => true,
			'points'          => true
		);

	}

	/**
	 * Retrieve referral data
	 *
	 * @since 1.0
	 */
	public function get_data() {

		$paid     = array();
		$unpaid   = array();
		$rejected = array();
		$pending  = array();

		$referrals = affiliate_wp()->referrals->get_referrals( array( 'orderby' => 'date', 'order' => 'ASC' ));

		if( $referrals ) {
			foreach( $referrals as $referral ) {

				switch( $referral->status ) {

					case 'paid' :

						$paid[] = array( strtotime( $referral->date ) * 1000, $referral->amount );

						break;

					case 'unpaid' :

						$unpaid[] = array( strtotime( $referral->date ) * 1000, $referral->amount );

						break;

					case 'rejected' :

						$rejected[] = array( strtotime( $referral->date ) * 1000, $referral->amount );

						break;

					case 'pending' :

						$pending[] = array( strtotime( $referral->date ) * 1000, $referral->amount );

						break;

					default :

						break;

				}

			}
		}

		$data = array(
			__( 'Pending Referrals', 'affiliate-wp' )  => $pending,
			__( 'Paid Referrals', 'affiliate-wp' )     => $paid,
			__( 'Unpaid Referrals', 'affiliate-wp' )   => $unpaid,
			__( 'Rejected Referrals', 'affiliate-wp' ) => $rejected
		);

		return $data;

	}

}