<?php

require('../Tester.class.php');
require('../HtmlEncode.class.php');

class HtmlEncodeTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new HtmlEncode('a')), 'HtmlEncode is object');
	}

}

HtmlEncodeTest::doTest();
