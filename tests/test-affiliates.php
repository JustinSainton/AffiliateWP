<?php

class Affiliate_Tests extends WP_UnitTestCase {

	protected $_affiliate_id = 0;

	function setUp() {
		parent::setUp();

		$args = array(
			'user_id' => 1
		);

		$this->_affiliate_id = affiliate_wp()->affiliates->add( $args );

	}

	function test_is_affiliate() {
		$this->assertFalse( affwp_is_affiliate() );
	}

	function test_get_affiliate_id() {
		$this->assertFalse( affwp_is_affiliate( 0 ) );
	}

	function test_add_affiliate() {

		$args = array(
			'user_id'  => 1
		);

		$affiliate_id = affiliate_wp()->affiliates->add( $args );

		$this->assertFalse( $affiliate_id );

		$args = array(
			'user_id'  => 2
		);

		$affiliate_id = affiliate_wp()->affiliates->add( $args );

		$this->assertTrue( $affiliate_id );

	}
}

