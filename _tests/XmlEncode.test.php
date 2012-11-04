<?php

require('../Tester.class.php');
require('../XmlEncode.class.php');

class XmlEncodeTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new XmlEncode('a')), 'XmlEncode is object');
		$this->assertClassHasAttribute('data','XmlEncode');
	}

	public function dataXml () {
		return array (
			array('a'),
			array(array(1,2,3)),
			array((object)array('a' => 'b')),
		);
	}

	public function testXml ($a) {
		$obj = new XmlEncode($a);
		$this->assertValidXml($obj->output());

	}
}

XmlEncodeTest::doTest();
