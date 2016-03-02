<?php

require('../Tester.php');
require('../String.php');

class StringTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new String('X')), 'String is object');
		$this->assertClassHasAttribute('string','String');
	}

	public function testChaining () {
		$test = 'Ich bin ein längerer Test';
		$string = (string)String::init($test)->paragraph();
		$this->outputLine($string);
		$this->assertTrue(is_string($string), 'String is string');
		$this->assertTrue(!empty($string), 'String is not empty');
	}
}

StringTest::doTest();
