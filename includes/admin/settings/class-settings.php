<?php

class Affiliate_WP_Settings {

	private $options;

	public function __construct() {


		$this->options = array();

	}

	public function get( $key, $default ) {
		$value = ! empty( $options[ $key ] ) ? $options[ $key ] : $default;
		return $value;
	}

}