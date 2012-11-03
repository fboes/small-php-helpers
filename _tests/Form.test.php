<?php

require('../Tester.class.php');
require('../Form.class.php');

class FormTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new Form()), 'Form is object');
		$this->assertClassHasAttribute('formElements','Form');
	}

	public function testFormPopulation () {
		$data = array(
			'a' => 'b',
			'c' => 'd',
		);
		$f = new Form($data);
		$this->assertEquals($data, $f->defaultFormData);
	}

}

FormTest::doTest();
