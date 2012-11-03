<?php

require('../Tester.class.php');
require('../XmlEncode.class.php');

class XmlEncodeTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new XmlEncode('a')), 'XmlEncode is object');
		$this->assertClassHasAttribute('data','XmlEncode');
	}

}

XmlEncodeTest::doTest();
