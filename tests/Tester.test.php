<?php

require('../Tester.class.php');

class TesterTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new Tester()));
		$this->assertNotEquals(1,2);
	}

	public function testEvenSimpler () {
		$this->assertNotEquals(1,TRUE);
		$this->assertTrue(1);
	}
}

TesterTest::doTest();
