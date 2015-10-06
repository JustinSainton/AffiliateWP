<?php

class Visit_Tests extends WP_UnitTestCase {

	function test_get_visits() {
		$this->assertFalse( false );
	}

	function test_sanitize_visit_url() {
		$referral_var = affiliate_wp()->tracking->get_referral_var();

		$this->assertEquals( affwp_sanitize_visit_url( 'http://affiliatewp.com/' . $referral_var . '/pippin/query_var' ), 'http://affiliatewp.com/query_var' );
		$this->assertEquals( affwp_sanitize_visit_url( 'http://affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1' ), 'http://affiliatewp.com/sample-page/query_var/1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'http://affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1/query_var2/2' ), 'http://affiliatewp.com/sample-page/query_var/1/query_var2/2' );
		$this->assertEquals( affwp_sanitize_visit_url( 'http://affiliatewp.com/' . $referral_var . '/pippin?query_var=1' ), 'http://affiliatewp.com?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'http://affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1' ), 'http://affiliatewp.com/sample-page/?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'http://affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1&query_var2=2' ), 'http://affiliatewp.com/sample-page/?query_var=1&query_var2=2' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/' . $referral_var . '/pippin/query_var' ), 'https://www.affiliatewp.com/query_var' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1' ), 'https://www.affiliatewp.com/sample-page/query_var/1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin/query_var/1/query_var2/2' ), 'https://www.affiliatewp.com/sample-page/query_var/1/query_var2/2' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/' . $referral_var . '/pippin?query_var=1' ), 'https://www.affiliatewp.com?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1' ), 'https://www.affiliatewp.com/sample-page/?query_var=1' );
		$this->assertEquals( affwp_sanitize_visit_url( 'https://www.affiliatewp.com/sample-page/' . $referral_var . '/pippin?query_var=1&query_var2=2' ), 'https://www.affiliatewp.com/sample-page/?query_var=1&query_var2=2' );
	}
}

