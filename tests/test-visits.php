<?php

class Visit_Tests extends WP_UnitTestCase {

	function test_get_visits() {
		$this->assertFalse( false );
	}

	function test_long_campaign() {

		// The 2 should get trimmed off as it is the 51st character
		$campaign = '111111111111111111111111111111111111111111111111112';
		$visit_id = affiliate_wp()->visits->add( array( 'campaign' => $campaign, 'affiliate_id' => 1 ) );
		$visit    = affiliate_wp()->visits->get( $visit_id );

		$this->assertEquals( 50, strlen( $visit->campaign ) );
		$this->assertEquals( '11111111111111111111111111111111111111111111111111', $visit->campaign );

	}

	function test_sanitize_visit_url() {
		$referral_var = affiliate_wp()->tracking->get_referral_var();

		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/' . $referral_var . '/pippin/query_var' ), 'https://affiliatewp.com/query_var' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1' ), 'https://affiliatewp.com/sample-page/query_var/1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1/query_var2/2' ), 'https://affiliatewp.com/sample-page/query_var/1/query_var2/2' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/' . $referral_var . '/pippin?query_var=1' ), 'https://affiliatewp.com?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1' ), 'https://affiliatewp.com/sample-page?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1&query_var2=2' ), 'https://affiliatewp.com/sample-page?query_var=1&query_var2=2' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/' . $referral_var . '/pippin/query_var' ), 'https://www.affiliatewp.com/query_var' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1' ), 'https://www.affiliatewp.com/sample-page/query_var/1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1/query_var2/2' ), 'https://www.affiliatewp.com/sample-page/query_var/1/query_var2/2' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/' . $referral_var . '/pippin?query_var=1' ), 'https://www.affiliatewp.com?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1' ), 'https://www.affiliatewp.com/sample-page?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1&query_var2=2' ), 'https://www.affiliatewp.com/sample-page?query_var=1&query_var2=2' );
	}

}