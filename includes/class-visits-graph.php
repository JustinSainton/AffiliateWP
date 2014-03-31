<?php

class Affiliate_WP_Visits_Graph extends Affiliate_WP_Graph {

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
			'points'          => true,
			'affiliate_id'    => false,
		);

	}

	/**
	 * Retrieve referral data
	 *
	 * @since 1.0
	 */
	public function get_data() {

		$converted   = array();
		$unconverted = array();

		$dates = affwp_get_report_dates();

		$start = $dates['year'] . '-' . $dates['m_start'] . '-' . $dates['day'] . ' 00:00:00';
		$end   = $dates['year_end'] . '-' . $dates['m_end'] . '-' . $dates['day_end'] . ' 23:59:59';
		$date  = array(
			'start' => $start,
			'end'   => $end
		);

		//echo '<pre>'; print_r( $date ); echo '</pre>'; exit;

		$visits = affiliate_wp()->visits->get_visits( array(
			'orderby'      => 'date',
			'order'        => 'ASC',
			'date'         => $date,
			'affiliate_id' => $this->get( 'affiliate_id' )
		) );

		if( $visits ) {
			foreach( $visits as $visit ) {

				switch( $visit->status ) {

					case 'converted' :

						$converted[] = array( strtotime( $visit->date ) * 1000, $visit->url );

						break;

					case 'unconverted' :

						$unconverted[] = array( strtotime( $visit->date ) * 1000, $visit->url );

						break;

					default :

						break;

				}

			}
		}

		$data = array(
			__( 'Converted Visits', 'affiliate-wp' )   => $converted,
			__( 'Unconverted Visits', 'affiliate-wp' ) => $unconverted,
		);

		return $data;

	}

}