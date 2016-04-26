<?php

require('../Tester.php');
require('../HtmlEncode.php');

class HtmlEncodeTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new HtmlEncode('a')), 'HtmlEncode is object');
		$this->assertClassHasAttribute('data','HtmlEncode');
	}

	public function dataHtml () {
		return array (
			'string' => array('a'),
			'array' => array(array(1,2,3)),
			'object' => array((object)array('a' => 'b')),
			'self' => array($this),
		);
	}

	public function testHtml ($a) {
		$obj = new HtmlEncode($a);
		$this->assertValidHtml($obj->output());

	}
}

HtmlEncodeTest::doTest();
