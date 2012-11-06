<?php

require('../Tester.class.php');
require('../Form.class.php');

class FormTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new Form()), 'Form is object');
		$this->assertClassHasAttribute('formElements','Form');
	}

	public function dataFormPopulation () {
		return array(
			array (
				array(
					'a' => 'b',
					'c' => 'd',
				)
			),
			array (
				array(
					'input' => 'output',
					'alpha' => 1,
					'omega' => array(1,2)
				)
			),
		);
	}

	public function testFormPopulation ($a) {
		$f = new Form($a);
		$this->assertEquals($a, $f->defaultFormData);
	}

	public function DataHtmlOutput () {
		return array(
			'Numerical options'  => array(
				array(1,2,3)
			),
			'Hash' => array (
				array('a' => 'b', 'c' => 'd')
			),
		);
	}

	/**
	 * Test generic HTML output
	 * @param  [type] $options [description]
	 */
	public function testHtmlOutput ($options) {
		$f = Form::init()
			->start('<form>')
			->input('<input data-label="a" name="a" />')
			->input('<input data-label="b" type="hidden" name="b" />')
			->textarea('<textarea data-label="c" name="c" />')
			->select('<select data-label="d" name="d">', $options)
			->select('<select data-label="e" name="e" multiple="multiple">', $options)
			->checkbox('<input data-label="f" name="f">', $options)
			->checkbox('<input data-label="g" name="g" type="radio">', $options)
			->end('</form>')
		;

		$output = $f->returnHTML();
		#$this->outputLine($output);

		$this->assertTrue(is_string($output), 'Expecting HTML output to be string');
		$this->assertValidHtml($output);
		$this->assertValidXml($output);

		$this->assertRegExp('#<label>#', $output, 'Expecting labels to be present');
		$this->assertRegExp('#<option#', $output, 'Expecting options to be present');
		$this->assertRegExp('#type="radio"#', $output, 'Expecting radio-buttons to be present');
		$this->assertRegExp('#type="checkbox"#', $output, 'Expecting checkboxes to be present');

	}

	/**
	 * Test data set functionality (HTML5)
	 */
	public function testDataSet () {
		$f = Form::init()
			->start('<form>')
			->input('<input data-label="a" name="a" />', array(1,2,3))
			->end('</form>')
		;

		$output = $f->returnHTML();
		#$this->outputLine($output);

		$this->assertTrue(is_string($output), 'Expecting HTML output to be string');
		#$this->assertValidHtml($output); // Datalist is HTML5
		$this->assertValidXml($output);
	}

	/**
	 * Check pattern check
	 */
	public function testPatternRecognition () {
		$f = Form::init()
			->start('<form>')
			->input('<input type="url" name="url" />')
			->end('</form>')
		;
		$output = $f->returnHTML();
		#$this->outputLine($output);

		$this->assertRegExp('#data-pattern=".+"#', $output);
	}


	public function dataValidator () {
		return array(
			'Required wrong' => array('<input value="" name="required" required="required" />', 'required'),
			'Required right' => array('<input value="i am here" name="required" required="required" />'),
			'Required right' => array('<input value="0" name="required" required="required" />'),
			'E-Mail wrong' => array('<input type="email" value="no-email" name="email" />','pattern'),
			'E-Mail right' => array('<input type="email" value="i.am@an.email.com" name="email" />'),
			'URL wrong' => array('<input type="url" value="ftp://i am not an url" name="url" />','pattern'),
			'URL right' => array('<input type="url" value="http://example.com" name="url" />'),
			'Number wrong' => array('<input type="number" value="no-number" name="number" />','pattern'),
			'Number right' => array('<input type="number" value="-12.34" name="number" />'),
			'Range wrong max' => array('<input type="number" min="0" max="1" value="2" name="number" />','max'),
			'Range wrong min' => array('<input type="number" min="0" max="1" value="-2" name="number" />','min'),
			'Range right' => array('<input type="number" min="0" max="1" value="0" name="number" />'),
		);

	}

	/**
	 * Test error handling of form tags
	 * @param  [type] $tag   [description]
	 * @param  [type] $error [description]
	 */
	public function testValidator ($tag, $error = NULL) {
		$f = Form::init()
			->start('<form>')
			->input($tag)
			->end('</form>')
		;

		$output = $f->returnHTML();
		#$this->outputLine($output);

		$this->assertValidXml($output);

		if (!empty($error)) {
			$this->assertRegExp('#class=".+?error"#', $output, 'Expecting error to be present');
			$this->assertRegExp('#class=".+?error-'.$error.'"#', $output);

		}
		else {
			$this->assertTrue (!(bool)preg_match('#class=".+?error#', $output), 'Expecting no error to be present');
		}

	}


}

FormTest::doTest();
