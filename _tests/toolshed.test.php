<?php

require('../Tester.class.php');
require('../toolshed.php');

class toolshedTest extends Tester {
	public function testSimple () {
		$this->assertFunctionExists('_');
		$this->assertFunctionExists('_echo');
	}

	public function testParagraph () {
		$this->assertFunctionExists('_paragraph');		
	}

	public function testSprint () {
		$this->assertFunctionExists('_sprintf');
		$this->assertFunctionExists('_vsprintf');
	}	

	public function testIsBlank () {
		$this->assertFunctionExists('is_blank');		

		$a = '';
		$this->assertTrue(is_blank($a));
		$a = NULL;
		$this->assertTrue(is_blank($a));
		$a = array();
		$this->assertTrue(is_blank($a));
		
		$a = 0;
		$this->assertTrue(empty($a));
		$this->assertTrue(!is_blank($a));
	}

}

toolshedTest::doTest();
