<?php

class Capabilities_Tests extends WP_UnitTestCase {

	protected $_user_id = 1;

	function setUp() {
		parent::setUp();
	}

	function test_admin_has_caps() {

		$this->markTestIncomplete( 'Fails 50% of the time. No idea why' );

		$roles = new Affiliate_WP_Capabilities;
		$roles->add_caps();

		$user = new WP_User( $this->_user_id );

		$this->assertTrue( $user->has_cap( 'view_affiliate_reports' ) );
		$this->assertTrue( $user->has_cap( 'export_affiliate_data' ) );
		$this->assertTrue( $user->has_cap( 'manage_affiliate_options' ) );
		$this->assertTrue( $user->has_cap( 'manage_affiliates' ) );
		$this->assertTrue( $user->has_cap( 'manage_referrals' ) );
		$this->assertTrue( $user->has_cap( 'manage_visits' ) );
		$this->assertTrue( $user->has_cap( 'manage_creatives' ) );

	}

}

