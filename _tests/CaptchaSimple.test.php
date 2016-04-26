<?php

require('../Tester.php');
require('../Captcha/Simple.php');

class CaptchaSimpleTest extends Tester {
	public function testSimple () {
		$this->assertTrue(is_object(new CaptchaSimple('a')), 'CaptchaSimple is object');
	}

	public function testHtml () {
		$f = new CaptchaSimple('a');

		$output = $f->getHtml();
		$this->outputLine($output);

		$this->assertTrue(is_string($output), 'Expecting HTML output to be string');
		$this->assertValidHtml($output);
	}
}

CaptchaSimpleTest::doTest();
