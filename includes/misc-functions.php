<?php
/**
 * Formatting functions for taking care of proper number formats and such
 *
 * @package     Affiliate WP
 * @subpackage  Functions/Formatting
 * @copyright   Copyright (c) 2014, Pippin Williamson
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;



/**
 * Get Currencies
 *
 * @since 1.0
 * @return array $currencies A list of the available currencies
 */
function affwp_get_currencies() {
	$currencies = array(
		'USD'  => __( 'US Dollars (&#36;)', 'affiliate-wp' ),
		'EUR'  => __( 'Euros (&euro;)', 'affiliate-wp' ),
		'GBP'  => __( 'Pounds Sterling (&pound;)', 'affiliate-wp' ),
		'AUD'  => __( 'Australian Dollars (&#36;)', 'affiliate-wp' ),
		'BRL'  => __( 'Brazilian Real (R&#36;)', 'affiliate-wp' ),
		'CAD'  => __( 'Canadian Dollars (&#36;)', 'affiliate-wp' ),
		'CZK'  => __( 'Czech Koruna', 'affiliate-wp' ),
		'DKK'  => __( 'Danish Krone', 'affiliate-wp' ),
		'HKD'  => __( 'Hong Kong Dollar (&#36;)', 'affiliate-wp' ),
		'HUF'  => __( 'Hungarian Forint', 'affiliate-wp' ),
		'ILS'  => __( 'Israeli Shekel (&#8362;)', 'affiliate-wp' ),
		'JPY'  => __( 'Japanese Yen (&yen;)', 'affiliate-wp' ),
		'MYR'  => __( 'Malaysian Ringgits', 'affiliate-wp' ),
		'MXN'  => __( 'Mexican Peso (&#36;)', 'affiliate-wp' ),
		'NZD'  => __( 'New Zealand Dollar (&#36;)', 'affiliate-wp' ),
		'NOK'  => __( 'Norwegian Krone', 'affiliate-wp' ),
		'PHP'  => __( 'Philippine Pesos', 'affiliate-wp' ),
		'PLN'  => __( 'Polish Zloty', 'affiliate-wp' ),
		'SGD'  => __( 'Singapore Dollar (&#36;)', 'affiliate-wp' ),
		'SEK'  => __( 'Swedish Krona', 'affiliate-wp' ),
		'CHF'  => __( 'Swiss Franc', 'affiliate-wp' ),
		'TWD'  => __( 'Taiwan New Dollars', 'affiliate-wp' ),
		'THB'  => __( 'Thai Baht (&#3647;)', 'affiliate-wp' ),
		'INR'  => __( 'Indian Rupee (&#8377;)', 'affiliate-wp' ),
		'TRY'  => __( 'Turkish Lira (&#8378;)', 'affiliate-wp' ),
		'RIAL' => __( 'Iranian Rial (&#65020;)', 'affiliate-wp' ),
		'RUB'  => __( 'Russian Rubles', 'affiliate-wp' )
	);

	return apply_filters( 'affwp_currencies', $currencies );
}


/**
 * Get the store's set currency
 *
 * @since 1.0
 * @return string The currency code
 */
function affwp_get_currency() {
	$currency = affiliate_wp()->settings->get( 'currency', 'USD' );
	return apply_filters( 'affwp_currency', $currency );
}

/**
 * Sanitize Amount
 *
 * Returns a sanitized amount by stripping out thousands separators.
 *
 * @since 1.0
 * @param string $amount amount amount to format
 * @return string $amount Newly sanitized amount
 */
function affwp_sanitize_amount( $amount ) {
	global $affwp_options;

	$thousands_sep = affiliate_wp()->settings->get( 'thousands_separator', ',' );
	$decimal_sep   = affiliate_wp()->settings->get( 'decimal_separator', '.' );

	// Sanitize the amount
	if ( $decimal_sep == ',' && false !== ( $found = strpos( $amount, $decimal_sep ) ) ) {
		if ( $thousands_sep == '.' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
			$amount = str_replace( $thousands_sep, '', $amount );
		} elseif( empty( $thousands_sep ) && false !== ( $found = strpos( $amount, '.' ) ) ) {
			$amount = str_replace( '.', '', $amount );
		}

		$amount = str_replace( $decimal_sep, '.', $amount );
	} elseif( $thousands_sep == ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( $thousands_sep, '', $amount );
	}

	$decimals = apply_filters( 'affwp_sanitize_amount_decimals', 2, $amount );
	$amount   = number_format( $amount, $decimals, '.', '' );

	return apply_filters( 'affwp_sanitize_amount', $amount );
}

/**
 * Returns a nicely formatted amount.
 *
 * @since 1.0
 * 
 * @param string $amount   Price amount to format
 * @param string $decimals Whether or not to use decimals.  Useful when set to false for non-currency numbers.
 * 
 * @return string $amount Newly formatted amount or Price Not Available
 */
function affwp_format_amount( $amount, $decimals = true ) {
	global $affwp_options;

	$thousands_sep = affiliate_wp()->settings->get( 'thousands_separator', ',' );
	$decimal_sep   = affiliate_wp()->settings->get( 'decimal_separator', '.' );

	// Format the amount
	if ( $decimal_sep == ',' && false !== ( $found = strpos( $amount, $decimal_sep ) ) ) {
		$whole = substr( $amount, 0, $sep_found );
		$part = substr( $amount, $sep_found + 1, ( strlen( $amount ) - 1 ) );
		$amount = $whole . '.' . $part;
	}

	// Strip , from the amount (if set as the thousands separator)
	if ( $thousands_sep == ',' && false !== ( $found = strpos( $amount, $thousands_sep ) ) ) {
		$amount = str_replace( ',', '', $amount );
	}

	if ( empty( $amount ) ) {
		$amount = 0;
	}
	
	$decimals  = apply_filters( 'affwp_format_amount_decimals', $decimals ? 2 : 0, $amount );
	$formatted = number_format( $amount, $decimals, $decimal_sep, $thousands_sep );

	return apply_filters( 'affwp_format_amount', $formatted, $amount, $decimals, $decimal_sep, $thousands_sep );
}


/**
 * Formats the currency display
 *
 * @since 1.0
 * @param string $amount amount
 * @return array $currency Currencies displayed correctly
 */
function affwp_currency_filter( $amount ) {

	$currency = affwp_get_currency();
	$position = affiliate_wp()->settings->get( 'currency_position', 'before' );

	$negative = $amount < 0;

	if( $negative ) {
		$amount = substr( $amount, 1 ); // Remove proceeding "-" -
	}

	if ( $position == 'before' ):
		switch ( $currency ):
			case "GBP" :
				$formatted = '&pound;' . $amount;
				break;
			case "BRL" :
				$formatted = 'R&#36;' . $amount;
				break;
			case "EUR" :
				$formatted = '&euro;' . $amount;
				break;
			case "USD" :
			case "AUD" :
			case "CAD" :
			case "HKD" :
			case "MXN" :
			case "SGD" :
				$formatted = '&#36;' . $amount;
				break;
			case "JPY" :
				$formatted = '&yen;' . $amount;
				break;
			default :
			    $formatted = $currency . ' ' . $amount;
				break;
		endswitch;
		$formatted = apply_filters( 'affwp_' . strtolower( $currency ) . '_currency_filter_before', $formatted, $currency, $amount );
	else :
		switch ( $currency ) :
			case "GBP" :
				$formatted = $amount . '&pound;';
				break;
			case "BRL" :
				$formatted = $amount . 'R&#36;';
				break;
			case "EUR" :
				$formatted = $amount . '&euro;';
				break;
			case "USD" :
			case "AUD" :
			case "CAD" :
			case "HKD" :
			case "MXN" :
			case "SGD" :
				$formatted = $amount . '&#36;';
				break;
			case "JPY" :
				$formatted = $amount . '&yen;';
				break;
			default :
			    $formatted = $amount . ' ' . $currency;
				break;
		endswitch;
		$formatted = apply_filters( 'affwp_' . strtolower( $currency ) . '_currency_filter_after', $formatted, $currency, $amount );
	endif;

	if( $negative ) {
		// Prepend the mins sign before the currency sign
		$formatted = '-' . $formatted;
	}

	return $formatted;
}

/**
 * Set the number of decimal places per currency
 *
 * @since 1.4.2
 * @param int $decimals Number of decimal places
 * @return int $decimals
*/
function affwp_currency_decimal_filter( $decimals = 2 ) {
	global $affwp_options;

	$currency = affwp_get_currency();

	switch ( $currency ) {
		case 'RIAL' :
		case 'JPY' :
		case 'TWD' :

			$decimals = 0;
			break;
	}

	return $decimals;
}
add_filter( 'affwp_sanitize_amount_decimals', 'affwp_currency_decimal_filter' );
add_filter( 'affwp_format_amount_decimals', 'affwp_currency_decimal_filter' );


/**
 * Convert an object to an associative array.
 *
 * Can handle multidimensional arrays
 *
 * @since 1.0
 *
 * @param unknown $data
 * @return array
 */
function affwp_object_to_array( $data ) {
	if ( is_array( $data ) || is_object( $data ) ) {
		$result = array();
		foreach ( $data as $key => $value ) {
			$result[ $key ] = edd_object_to_array( $value );
		}
		return $result;
	}
	return $data;
}

/**
 * Month Num To Name
 *
 * Takes a month number and returns the name three letter name of it.
 *
 * @since 1.0
 *
 * @param unknown $n
 * @return string Short month name
 */
function affwp_month_num_to_name( $n ) {
	$timestamp = mktime( 0, 0, 0, $n, 1, 2005 );

	return date_i18n( "M", $timestamp );
}