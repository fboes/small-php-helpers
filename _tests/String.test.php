<?php

require('../Tester.php');
require('../String.php');

class StringTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new String('X')), 'String is object');
		$this->assertClassHasAttribute('string','String');
	}

	public function testChaining () {
		$test = 'I am a lengthy test and chain all methods for testing.';
		$string = (string)String::init($test)
			->paragraph()
			->textile()
			->markdown()
			->addingTubes()
			->sprintf('x')
			->vsprintf(array('x'))
			->htmlallchars()
			->str_shorten()
			->str_nice_shorten()
			->asciify()
			->make_id()
			->improve_typography()
			->externalLinks()
		;
		$this->outputLine($string);
		$this->assertTrue(is_string($string), 'String is string');
		$this->assertTrue(!empty($string), 'String is not empty');
	}

	public function testMarkdown () {
		$test = 'X... Y 2x2 "xx" *test*';
		$string = (string)String::init($test)->improve_typography()->markdown();
		$this->outputLine($string);
		$this->assertTrue(is_string($string), 'String is string');
		$this->assertTrue(!empty($string), 'String is not empty');
		$this->assertTrue($string !== $test, 'String is not in its original state');
	}

	public function testNormalize () {
		$test = '1. IDs füngen nie mit Züffern an!';
		$string = (string)String::init($test)->make_id();
		$this->outputLine($string);
		$this->assertTrue(is_string($string), 'String is string');
		$this->assertTrue(!empty($string), 'String is not empty');
		$this->assertTrue($string !== $test, 'String is not in its original state');

		$string = (string)String::init($test)->asciify();
		$this->outputLine($string);
		$this->assertTrue(is_string($string), 'String is string');
		$this->assertTrue(!empty($string), 'String is not empty');
		$this->assertTrue($string !== $test, 'String is not in its original state');
	}
}

StringTest::doTest();
