<?php

require('../Tester.php');
require('../Messages.php');

class MessagesTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new Messages()), 'Messages is object');
	}

}

MessagesTest::doTest();
