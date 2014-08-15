<?php

class Referral_Tests extends WP_UnitTestCase {

	function test_get_referral() {
		$this->assertNull( affwp_get_referral( 0 ) );
	}
}

