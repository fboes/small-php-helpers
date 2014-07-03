<?php

require('../Tester.php');
require('../XmlEncode.php');

class XmlEncodeTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new XmlEncode('a')), 'XmlEncode is object');
		$this->assertClassHasAttribute('data','XmlEncode');
	}

	public function dataXml () {
		return array (
			'string' => array('a'),
			'array' => array(array(1,2,3)),
			'object' => array((object)array('a' => 'b')),
			'self' => array($this),
		);
	}

	public function testXml ($a) {
		$obj = new XmlEncode($a);
		$output = $obj->output();

		$this->outputLine($output);
		$this->assertValidXml($output);
	}
}

XmlEncodeTest::doTest();
