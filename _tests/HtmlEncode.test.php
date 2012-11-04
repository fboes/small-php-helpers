<?php

require('../Tester.class.php');
require('../HtmlEncode.class.php');

class HtmlEncodeTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new HtmlEncode('a')), 'HtmlEncode is object');
		$this->assertClassHasAttribute('data','HtmlEncode');
	}

	public function dataHtml () {
		return array (
			array('a'),
			array(array(1,2,3)),
			array((object)array('a' => 'b')),
		);
	}

	public function testHtml ($a) {
		$obj = new HtmlEncode($a);
		$this->assertValidHtml($obj->output());

	}
}

HtmlEncodeTest::doTest();
