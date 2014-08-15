<?php

class Referral_Tests extends WP_UnitTestCase {

	function test_get_referral() {
		$this->assertFalse( affwp_get_referral() );
	}
}

