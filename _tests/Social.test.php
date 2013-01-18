<?php

require('../Tester.class.php');
require('../Social.class.php');

class SocialTest extends Tester {

	public function testSimple () {
		$social = new Social('Title of URL', 'http://example.com/');

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
	}
}

SocialTest::doTest();
