<?php

require('../Tester.class.php');

class TesterTest extends Tester {
	public $seeIfIAmHere = TRUE;

	public function testSimple () {
		$this->assertTrue(is_object(new Tester()));
		$this->assertMethodExists('testSimple','TesterTest');
		$this->assertClassHasAttribute('globalAssertionsSuccess','TesterTest');
		$this->assertClassHasAttribute('seeIfIAmHere','TesterTest');
	}

	public function testAssertions () {
		$this->assertNotEquals(1,2);
		$this->assertNotEquals(1,TRUE);
		$this->assertRegExp('#a#','blabla');
	}

	public function dataProviders () {
		return array(
			array ('a'),
			array ('b', 'b'),
			array ('c', 'c'),
		);
	}

	public function testProviders ($a, $b = 'a') {
		$this->assertEquals ($a, $b);
	}

	public function testValidators () {
		$this->assertValidXml('<xml>bla</xml>');
		$this->assertValidHtml('x<p>bla</p>');
	}
}

TesterTest::doTest();
