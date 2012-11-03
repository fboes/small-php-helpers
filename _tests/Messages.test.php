<?php

require('../Tester.class.php');
require('../Messages.class.php');

class MessagesTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new Messages()), 'Messages is object');
	}

}

MessagesTest::doTest();
