<?php

require('../Tester.class.php');
require('../HttpApi.class.php');

class HttpApiTest extends Tester {
	public function testSimple () {
		$baseUrl = 'http://3960.org/';

		$this->assertMethodExists('get',                'HttpApi');
		$this->assertMethodExists('post',               'HttpApi');
		$this->assertMethodExists('put',                'HttpApi');
		$this->assertMethodExists('delete',             'HttpApi');
		$this->assertMethodExists('doRequest',          'HttpApi');
		$this->assertMethodExists('setHttpCredentials', 'HttpApi');

		$api = new HttpApi($baseUrl, HttpApi::REPLY_TYPE_HTML);
		$this->assertTrue(is_object($api), 'Expecting object');

	}

	public function testObjectInvocation () {
		$baseUrl = 'http://www.3960.org';
		$api = new HttpApi($baseUrl, HttpApi::REPLY_TYPE_HTML);
		$result = $api->get(array('a' => 'b'));
		$this->assertTrue(!empty($result), 'Result received');
		$this->assertTrue(!$api->isLastRequestError(), 'Last request was no error');
		#$this->outputLine($api);
	}

	public function testXmlConversion () {
		$baseUrl = 'http://3960.org/';

		$api = new HttpApi($baseUrl, HttpApi::REPLY_TYPE_XML);

		$result = $api->get(array(), 'index.xml');
		$this->assertTrue(!empty($result), 'Result received');
		$this->assertTrue(!$api->isLastRequestError(), 'Last request was no error');
		$this->assertEquals(get_class($result), 'SimpleXMLElement');
		#$this->outputLine($api);
	}

	public function testRedirect () {
		$baseUrl = 'http://www.3960.org/';

		$api = new HttpApi($baseUrl);

		$result = $api->get();
		$this->assertTrue(!empty($result), 'Result received');
		$this->assertTrue(!$api->isLastRequestError(), 'Last request was no error');
		$this->assertNotEquals($baseUrl, $api->getLastUrl());
		#$this->outputLine($api);
	}

	public function testError () {
		$baseUrl = 'http://3960.org/';

		$api = new HttpApi($baseUrl);

		$result = $api->get(array(), '404.html');
		$this->assertTrue(!empty($result), 'Result received');
		$this->assertTrue($api->isLastRequestError(), 'Last request was error');
		#$this->outputLine($api);
	}

	/*
	public function testMemoization () {
		$baseUrl = 'http://3960.org/';

		require_once('../Memoization.class.php');

		$api = new HttpApi($baseUrl, HttpApi::REPLY_TYPE_HTML);
		$api->setMemoization(new Memoization());
		$this->assertTrue(is_object($api));
		$this->outputLine($api);

		$result = $api->get(array('a' => 'b'));
		$this->assertTrue(!empty($result));
	}
	*/
}

HttpApiTest::doTest();
