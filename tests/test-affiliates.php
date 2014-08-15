<?php

class Affiliate_Tests extends WP_UnitTestCase {

	function test_is_affiliate() {
		$this->assertFalse( affwp_is_affiliate() );
	}
}

