<?php

require('../Tester.php');
require('../Form.php');
#require('../toolshed.php');
#activate_translations('de','DE');

class FormTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new Form()), 'Form is object');
		$this->assertClassHasAttribute('formElements','Form');
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
			->select('<select data-label="e" data-hint="hint" name="e" multiple="multiple">', $options)
			->checkbox('<input data-label="f" name="f">', $options)
			->checkbox('<input data-label="g" name="g" type="radio">', $options)
			#->input('<input data-label="h" name="h" />', $options)
			->end('</form>')
		;

		$output = $f->returnHTML();
		$this->outputLine($output);

		$this->assertTrue(is_string($output), 'Expecting HTML output to be string');
		$this->assertValidHtml($output);
		$this->assertValidXml($output);

		$this->assertRegExp('#<label>#', $output, 'Expecting labels to be present');
		$this->assertRegExp('#id="[a-z]"#', $output, 'Expecting ids to be present');
		$this->assertRegExp('#<option#', $output, 'Expecting options to be present');
		$this->assertRegExp('#>hint<#', $output, 'Expecting hint to be present');
		$this->assertRegExp('#type="radio"#', $output, 'Expecting radio-buttons to be present');
		$this->assertRegExp('#type="checkbox"#', $output, 'Expecting checkboxes to be present');

	}

	public function dataDataSet () {
		return array(
			array(array(1,2,3,4)),
			array(array('a' => 'b', 'c' => 'd')),
		);
	}

	/**
	 * Test data set functionality (HTML5)
	 */
	public function testDataSet (array $data) {
		$f = Form::init()
			->start('<form>')
			->input('<input data-label="a" name="a" />', $data)
			->end('</form>')
		;

		$output = $f->returnHTML();
		#$this->outputLine($output);

		$this->assertTrue(is_string($output), 'Expecting HTML output to be string');
		#$this->assertValidHtml($output); // Datalist is HTML5
		$this->assertValidXml($output);
	}

	public function dataFormPopulation () {
		return array(
			'Simple' => array (
				array(
					'a' => 'b',
					'c' => 'd',
				)
			),
			'Arrays and time' => array (
				array(
					'input' => 'output',
					'alpha' => time(),
					'omega' => array(1,2)
				)
			),
		);
	}

	public function testFormPopulation ($a) {
		$f = new Form($a);
		foreach ($a as $key => $value) {
			$this->assertEquals($value, $f->defaultElementAttributes[$key]['value']);
		}
	}

	public function testSimpleFieldPopulation () {
		$searchValue = md5(time());
		$searchField = 'test';
		$defaultValues = array(
			$searchField => $searchValue,
			'another-'.$searchField => 'another-'.$searchValue,
		);
		$selectValues = array_values($defaultValues);

		// Positive tests

		$f = Form::init($defaultValues)
			->start('<form>')
			->input('<input name="'.htmlspecialchars($searchField).'" />')
			->end('</form>')
		;
		$this->assertRegExp('#value="'.$searchValue.'"#', $f->returnHTML(), 'Expecting default value to be visible in HTML for input');

		$f = Form::init($defaultValues)
			->start('<form>')
			->textarea('<input name="'.htmlspecialchars($searchField).'" />')
			->end('</form>')
		;
		$this->assertRegExp('#>'.$searchValue.'<#', $f->returnHTML(), 'Expecting default value to be visible in HTML for textarea');

		$f = Form::init($defaultValues)
			->start('<form>')
			->select('<input name="'.htmlspecialchars($searchField).'" />',$selectValues)
			->end('</form>')
		;
		$this->assertRegExp('#selected="selected">'.$searchValue.'#', $f->returnHTML(), 'Expecting default value to be visible in HTML for select');

		$f = Form::init($defaultValues)
			->start('<form>')
			->checkbox('<input name="'.htmlspecialchars($searchField).'" type="radio" />',$selectValues)
			->end('</form>')
		;
		$this->assertRegExp('#value="'.$searchValue.'" checked="checked"#', $f->returnHTML(), 'Expecting default value to be visible in HTML for radio button');

		// Negative tests

		$f = Form::init($defaultValues)
			->start('<form>')
			->input('<input name="'.htmlspecialchars('not-'.$searchField).'" />')
			->end('</form>')
		;
		$this->assertTrue(!preg_match('#value="'.$searchValue.'"#', $f->returnHTML()), 'Expecting not matching default value not to be visible in HTML for input');

		$f = Form::init($defaultValues)
			->start('<form>')
			->textarea('<input name="'.htmlspecialchars('not-'.$searchField).'" />')
			->end('</form>')
		;
		$this->assertTrue(!preg_match('#>'.$searchValue.'<#', $f->returnHTML()), 'Expecting not matching default value not to be visible in HTML for textarea');

		$f = Form::init($defaultValues)
			->start('<form>')
			->select('<input name="'.htmlspecialchars('not-'.$searchField).'" />',$selectValues)
			->end('</form>')
		;
		$this->assertTrue(!preg_match('#value="'.$searchValue.'" selected="selected"#', $f->returnHTML()), 'Expecting not matching default value not to be visible in HTML for select');

		$f = Form::init($defaultValues)
			->start('<form>')
			->checkbox('<input name="'.htmlspecialchars('not-'.$searchField).'" type="radio" />',$selectValues)
			->end('</form>')
		;
		$this->assertTrue(!preg_match('#value="'.$searchValue.'" checked="checked"#', $f->returnHTML()), 'Expecting not matching default value not to be visible in HTML for radio button');

	}

	public function testMultiFieldPopulation () {
		$searchValue = md5(time());
		$searchField = 'test';
		$selectValues = array($searchValue, 'another-'.$searchValue);
		$defaultValues = array(
			$searchField => $selectValues,
		);

		// Positive tests

		$f = Form::init($defaultValues)
			->start('<form>')
			->select('<input name="'.htmlspecialchars($searchField).'" multiple="multiple" />',$selectValues)
			->end('</form>')
		;
		$this->assertTrue(2 == preg_match_all('#selected="selected"#', $f->returnHTML()), 'Expecting default valuse to be visible in HTML for multi-select');

		$f = Form::init($defaultValues)
			->start('<form>')
			->checkbox('<input name="'.htmlspecialchars($searchField).'" type="checkbox" />',$selectValues)
			->end('</form>')
		;
		$this->assertTrue(2 == preg_match_all('#checked="checked"#', $f->returnHTML()), 'Expecting default values to be visible in HTML for checkbox');

		// Negative tests

		$f = Form::init($defaultValues)
			->start('<form>')
			->select('<input name="'.htmlspecialchars('not-'.$searchField).'" multiple="multiple" />',$selectValues)
			->end('</form>')
		;
		$this->assertTrue(!preg_match('#selected="selected"#', $f->returnHTML()), 'Expecting not matching default values not to be visible in HTML for multi-select');

		$f = Form::init($defaultValues)
			->start('<form>')
			->checkbox('<input name="'.htmlspecialchars('not-'.$searchField).'" type="checkbox" />',$selectValues)
			->end('</form>')
		;
		$this->assertTrue(!preg_match('#checked="checked"#', $f->returnHTML()), 'Expecting not matching default values not to be visible in HTML for checkbox');

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
			'Number right 2' => array('<input type="number" value="-.34" name="number" />'),
			'Number right 3' => array('<input type="number" value="-12.34e5" name="number" />'),
			'Range wrong max' => array('<input type="number" min="0" max="1" value="2" name="number" />','max'),
			'Range wrong min' => array('<input type="number" min="0" max="1" value="-2" name="number" />','min'),
			'Range right' => array('<input type="number" min="0" max="1" value="0" name="number" />'),
			'Maxlength wrong' => array('<input type="number" maxlength="10" value="12345678901" name="maxlength" />','maxlength'),
			'Maxlength right' => array('<input type="number" maxlength="10" value="1234567890" name="maxlength" />'),
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
			$this->assertRegExp('#class="[^"]*?invalid#', $output, "Expecting 'invalid' to be present in class");
			$this->assertRegExp('#class="[^"]*?invalid-'.$error.'#', $output, "Expecting 'invalid-$error' to be present in class");

		}
		else {
			$this->assertTrue (!(bool)preg_match('#class=".+?invalid#', $output), "Expecting no 'invalid' to be present in class");
		}

	}

	public function dataFromAttributes () {
		return array (
			array(
				array(
					'value' => md5(time()) . '-value',
					'data-label' => _('I am legend')
				)
			),
		);
	}

	public function testFromAttributes ($attributes) {
		$fieldname = md5(time());
		$attributes = array(
			$fieldname => $attributes
		);

		$f = Form::init()
			->setDefaultElementAttributes($attributes)
			->start('<form>')
			->input('<input name="'.$fieldname.'" />')
			->end('</form>')
		;

		$this->assertTrue(is_array($attributes), 'Expecting attributes to be an array');
		$this->assertTrue(!empty($f->defaultElementAttributes), 'Expecting default attributes to be set');
		$this->assertTrue($attributes == $f->defaultElementAttributes, 'Expecting default attributes to match given attributes');

		$output = $f->returnHTML();
		#$this->outputLine($output);

		$this->assertValidXml($output);
		foreach ($attributes[$fieldname] as $key => $value) {
			$this->assertTrue(strpos($output, htmlspecialchars($value)) !== FALSE, "Expecting '".$value."' to be present");
		}
	}

	public function testDateOptionslist () {
		$options = Form::getDateOptionslist();
		$this->assertTrue(is_array($options), 'Expecting options to be an array');
		$this->outputLine($options);

		$f = Form::init()
			->start('<form>')
			->input('<input type="date" name="date" />', $options)
			->end('</form>')
		;

		$output = $f->returnHTML();
		#$this->outputLine($output);

		$this->assertValidXml($output);
	}

	public function testCurrency () {
		$f = Form::init()
			->start('<form>')
			->input('<input type="currency" name="currency" />')
			->input('<input type="iban" name="iban" />')
			->input('<input type="bic" name="bic" />')
			->input('<input type="creditcard-number" name="creditcard-number" />')
			->input('<input type="username" name="username" />')
			->end('</form>')
		;

		$output = $f->returnHTML();
		#$this->outputLine($output);

		$this->assertValidXml($output);
	}

	public function testFauxHttpMethod () {
		$f = Form::init()
			->start('<form method="GET">')
			->end('</form>')
		;

		$output = $f->returnHTML();
		$this->assertValidXml($output);
		$this->assertRegExp('#method="get"#', $output, "Expecting lower case GET");

		$f = Form::init()
			->start('<form method="PUT">')
			->end('</form>')
		;
		$output = $f->returnHTML();
		#$this->outputLine($output);
		$this->assertValidXml($output);
		$this->assertRegExp('#method="post"#', $output, "Expecting POST instead of PUT");
	}

	public function testDatalist () {
		$f = Form::init()
			->start('<form>')
			->input('<input type="hidden" name="a" />')
			->datalist('<datalist id="x" />', array('a','b','c'))
			->end('</form>')
		;

		$output = $f->returnHTML();
		$this->outputLine($output);
		$this->assertValidXml($output);
		$this->assertRegExp('#<datalist.*>#', $output, "Expecting datalist");
	}

	/**
	 * Test generic HTML output
	 * @param  [type] $options [description]
	 */
	public function testHtmlOptgroup () {
		$options = array(
			'A' => array(1,2,3),
			'B' => array(4,5,6),
		);
		$f = Form::init()
			->start('<form>')
			->select('<select data-optgroup="optgroup" data-label="d" name="d">', $options)
			->select('<select data-optgroup="optgroup" data-label="e" data-hint="hint" name="e" multiple="multiple">', $options)
			->checkbox('<input data-optgroup="optgroup" data-label="f" name="f">', $options)
			->checkbox('<input data-optgroup="optgroup" data-label="g" name="g" type="radio">', $options)
			->end('</form>')
		;

		$output = $f->returnHTML();
		$this->outputLine($output);

		$this->assertTrue(is_string($output), 'Expecting HTML output to be string');
		$this->assertValidHtml($output);
		$this->assertValidXml($output);
	}
}

FormTest::doTest();
