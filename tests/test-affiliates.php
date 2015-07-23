<?php

class Affiliate_Tests extends WP_UnitTestCase {

	protected $_user_id = 1;
	protected $_affiliate_id = 0;
	protected $_affiliate_id2 = 0;

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
		$this->assertFalse( affwp_get_affiliate_id() );
		$this->assertEquals( $this->_affiliate_id, affwp_get_affiliate_id( $this->_user_id ) );
	}

	function test_get_affiliate_user_id() {
		$this->assertEquals( $this->_user_id, affwp_get_affiliate_user_id( $this->_affiliate_id ) );
	}

	function test_get_affiliate() {

		$affiliate = affwp_get_affiliate( $this->_affiliate_id );

		$this->assertEquals( $this->_affiliate_id, $affiliate->affiliate_id );

		$affiliate = affwp_get_affiliate( $affiliate );

		$this->assertEquals( $this->_affiliate_id, $affiliate->affiliate_id );

		$this->assertFalse( affwp_get_affiliate( null ) );
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

		$this->_affiliate_id2 = affiliate_wp()->affiliates->add( $args );

		$this->assertGreaterThan( 0, $this->_affiliate_id2 );

	}

	function test_update_affiliate() {

		$args = array(
			'affiliate_id'   => $this->_affiliate_id,
			'rate'           => '20',
			'account_email'  => 'testaccount@test.com'
		);

		$updated = affwp_update_affiliate( $args );

		$this->assertTrue( $updated );

	}

	function test_delete_affiliate() {

		affwp_delete_affiliate( $this->_affiliate_id2 );

		$affiliate = affwp_get_affiliate( $this->_affiliate_id2 );

		$this->assertNull( $affiliate );
	}

	function test_get_affiliate_status() {

		$this->assertEquals( 'active', affwp_get_affiliate_status( $this->_affiliate_id ) );

	}

	function test_set_affiliate_status() {

		$this->assertEquals( 'active', affwp_get_affiliate_status( $this->_affiliate_id ) );

		$this->assertTrue( affwp_set_affiliate_status( $this->_affiliate_id, 'inactive' ) );

		$this->assertEquals( 'inactive', affwp_get_affiliate_status( $this->_affiliate_id ) );

		$this->assertTrue( affwp_set_affiliate_status( $this->_affiliate_id, 'pending' ) );

		$this->assertEquals( 'pending', affwp_get_affiliate_status( $this->_affiliate_id ) );

		$this->assertTrue( affwp_set_affiliate_status( $this->_affiliate_id, 'rejected' ) );

		$this->assertEquals( 'rejected', affwp_get_affiliate_status( $this->_affiliate_id ) );

		$this->assertTrue( affwp_set_affiliate_status( $this->_affiliate_id, 'active' ) );

		$this->assertEquals( 'active', affwp_get_affiliate_status( $this->_affiliate_id ) );
	}

	function test_get_affiliate_rate() {

		$this->assertEquals( '0.2', affwp_get_affiliate_rate( $this->_affiliate_id ) );
		$this->assertEquals( '20%', affwp_get_affiliate_rate( $this->_affiliate_id, true ) );

	}

	function test_get_affiliate_rate_type() {

		$this->assertEquals( 'percentage', affwp_get_affiliate_rate_type( $this->_affiliate_id ) );

	}

	function test_get_affiliate_rate_types() {

		$this->assertArrayHasKey( 'percentage', affwp_get_affiliate_rate_types() );
		$this->assertArrayHasKey( 'flat', affwp_get_affiliate_rate_types() );
		$this->assertArrayNotHasKey( 'test', affwp_get_affiliate_rate_types() );

	}

	function test_get_affiliate_email() {

		$args = array(
			'affiliate_id'  => $this->_affiliate_id,
			'account_email' => 'affiliate@test.com'
		);

		affwp_update_affiliate( $args );

		$this->assertEquals( 'affiliate@test.com', affwp_get_affiliate_email( $this->_affiliate_id ) );
	}

	function test_get_affiliate_payment_email() {

		$args = array(
			'affiliate_id'  => $this->_affiliate_id,
			'payment_email' => 'affiliate-payment@test.com'
		);

		affwp_update_affiliate( $args );

		$this->assertEquals( 'affiliate-payment@test.com', affwp_get_affiliate_payment_email( $this->_affiliate_id ) );
	}

	function test_get_affiliate_earnings() {

		$this->assertEquals( 0, affwp_get_affiliate_earnings( $this->_affiliate_id ) );

	}

	function test_get_affiliate_unpaid_earnings() {

		$this->assertEquals( 0, affwp_get_affiliate_unpaid_earnings( $this->_affiliate_id ) );
		$this->assertEquals( '&#36;0', affwp_get_affiliate_unpaid_earnings( $this->_affiliate_id, true ) );

	}

	function test_adjust_affiliate_earnings() {

		$this->assertEquals( 10, affwp_increase_affiliate_earnings( $this->_affiliate_id, '10' ) );
		$this->assertFalse( affwp_increase_affiliate_earnings( 0, '10' ) );

		$this->assertEquals( 8, affwp_decrease_affiliate_earnings( $this->_affiliate_id, 2 ) );
		$this->assertFalse( affwp_decrease_affiliate_earnings( 0, '10' ) );

		$this->assertEquals( '12.2', affwp_increase_affiliate_earnings( $this->_affiliate_id, '4.2' ) );
	}

	function test_get_affiliate_referral_count() {

		$this->assertEquals( 0, affwp_get_affiliate_referral_count( $this->_affiliate_id ) );

	}

	function test_adjust_affiliate_referral_count() {

		$this->assertEquals( 1, affwp_increase_affiliate_referral_count( $this->_affiliate_id ) );
		$this->assertEquals( 2, affwp_increase_affiliate_referral_count( $this->_affiliate_id ) );
		$this->assertEquals( 1, affwp_decrease_affiliate_referral_count( $this->_affiliate_id ) );
		$this->assertFalse( affwp_decrease_affiliate_referral_count( $this->_affiliate_id2 ) );
	}

	function test_get_affiliate_visit_count() {

		$this->assertEquals( 0, affwp_get_affiliate_visit_count( $this->_affiliate_id ) );

	}

	function test_adjust_affiliate_visit_count() {

		$this->assertEquals( 1, affwp_increase_affiliate_visit_count( $this->_affiliate_id ) );
		$this->assertEquals( 2, affwp_increase_affiliate_visit_count( $this->_affiliate_id ) );
		$this->assertEquals( 1, affwp_decrease_affiliate_visit_count( $this->_affiliate_id ) );
		$this->assertFalse( affwp_decrease_affiliate_visit_count( $this->_affiliate_id2 ) );
	}

	function test_get_affiliate_conversion_rate() {
		$this->assertEquals( '0%', affwp_get_affiliate_conversion_rate( $this->_affiliate_id ) );
	}

}

