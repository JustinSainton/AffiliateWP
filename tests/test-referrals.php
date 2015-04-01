<?php

class Referral_Tests extends WP_UnitTestCase {

	protected $_affiliate_id = 0;
	protected $_referral_id = 0;

	function setUp() {
		parent::setUp();

		$args = array(
			'user_id' => 1
		);

		$this->_affiliate_id = affiliate_wp()->affiliates->add( $args );


		$args = array(
			'affiliate_id' => $this->_affiliate_id,
			'amount'       => 10,
			'status'       => 'pending',
			'context'      => 'tests',
			'custom'       => 4,
			'reference'    => 5
		);

		$this->_referral_id = affiliate_wp()->referrals->add( $args );

	}

	function test_get_referral() {
		$this->assertNull( affwp_get_referral( 0 ) );
		$this->assertNotEmpty( affwp_get_referral( $this->_referral_id ) );
	}

	function test_get_referral_status() {
		$this->assertEquals( 'pending', affwp_get_referral_status( $this->_referral_id ) );
	}

	function test_get_referral_status_label() {
		$this->assertEquals( 'Pending', affwp_get_referral_status_label( $this->_referral_id ) );
	}

	function test_set_referral_status() {
		$this->assertEquals( 'pending', affwp_get_referral_status( $this->_referral_id ) );
		affwp_set_referral_status( $this->_referral_id, 'unpaid' );
		$this->assertEquals( 'unpaid', affwp_get_referral_status( $this->_referral_id ) );
		affwp_set_referral_status( $this->_referral_id, 'rejected' );
		$this->assertEquals( 'rejected', affwp_get_referral_status( $this->_referral_id ) );
	}
}

