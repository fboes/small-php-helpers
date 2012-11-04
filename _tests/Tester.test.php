<?php

require('../Tester.class.php');

class TesterTest extends Tester {
	public $seeIfIAmHere = TRUE;

	public function testSimple () {
		$this->assertTrue(is_object(new Tester()));
		$this->assertClassHasAttribute('seeIfIAmHere','TesterTest');
	}

	public function testAssertions () {
		$this->assertNotEquals(1,2);
		$this->assertNotEquals(1,TRUE);
		$this->assertTrue(1);
		$this->assertRegExp('#a#','blabla');
	}

	public function testValidators () {
		$this->assertValidXml('<xml>bla</xml>');
		$this->assertValidHtml('x<p>bla</p>');
	}
}

TesterTest::doTest();
