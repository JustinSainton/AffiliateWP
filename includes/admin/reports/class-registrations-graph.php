<?php

class Affiliate_WP_Registrations_Graph extends Affiliate_WP_Graph {

	/**
	 * Retrieve referral data
	 *
	 * @since 1.1
	 */
	public function get_data() {

		$dates = affwp_get_report_dates();

		$start = $dates['year'] . '-' . $dates['m_start'] . '-' . $dates['day'] . ' 00:00:00';
		$end   = $dates['year_end'] . '-' . $dates['m_end'] . '-' . $dates['day_end'] . ' 23:59:59';
		$date  = array(
			'start' => $start,
			'end'   => $end
		);

		$affiliates = affiliate_wp()->affiliates->get_affiliates( array(
			'orderby'  => 'date',
			'order'    => 'ASC',
			'date'     => $date
		) );

		if( $affiliates ) {

			$affiliate_data = array();
			$affiliate_data[] = array( strtotime( $start ) * 1000, 0 );
			foreach( $affiliates as $affiliate ) {

				$affiliate_data[] = array( strtotime( $affiliate->date_registered ) * 1000, 1 );

			}
			$affiliate_data[] = array( strtotime( $end ) * 1000, 0 );

		}

		$data = array(
			__( 'Affiliate Registrations', 'affiliate-wp' ) => $affiliate_data
		);

		return $data;

	}

	/**
	 * Retrieve conversion rate for successful visits
	 *
	 * @since 1.1
	 */
	public function get_conversion_rate() {
		return round( ( $this->converted / $this->total ) * 100, 2 );
	}

}