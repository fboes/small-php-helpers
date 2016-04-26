<?php

require('../Tester.php');
require('../Page.php');

class PageTest extends Tester {

	public function testSimple () {
		$social = new Page('Title of URL', 'http://example.com/test');

		$url = $social->facebookUrl();
		$this->assertTrue(is_string($url), 'URL is string');
		$this->outputLine($url);

		$url = $social->twitterUrl();
		$this->assertTrue(is_string($url), 'URL is string');
		$this->outputLine($url);

		$url = $social->emailUrl();
		$this->assertTrue(is_string($url), 'URL is string');
		$this->outputLine($url);

		$url = $social->printUrl();
		$this->assertTrue(is_string($url), 'URL is string');
		$this->outputLine($url);

		$url = $social->basicMeta();
		$this->assertTrue(is_string($url), 'Meta is string');
		$this->outputLine($url);

		$url = $social->opengraphMeta();
		$this->assertTrue(is_string($url), 'Meta is string');
		$this->outputLine($url);
	}
}

PageTest::doTest();
