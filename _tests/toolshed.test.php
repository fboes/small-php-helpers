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

		$this->assertTrue(is_blank($a),'Expecting is_blank on undefined being TRUE');
		$a = NULL;
		$this->assertTrue(is_blank($a),'Expecting is_blank on NULL being TRUE');
		$a = '';
		$this->assertTrue(is_blank($a),'Expecting is_blank on empty string being TRUE');
		$a = array();
		$this->assertTrue(is_blank($a),'Expecting is_blank on empty array being TRUE');

		$a = 0;
		$this->assertTrue(empty($a));
		$this->assertTrue(!is_blank($a), 'Expecting is_blank on 0 to be FALSE');

		$a = 'a';
		$this->assertTrue(!is_blank($a), 'Expecting is_blank on not empty string to be FALSE');
		$a = array(1);
		$this->assertTrue(!is_blank($a), 'Expecting is_blank on not empty array to be FALSE');
	}

}

toolshedTest::doTest();
