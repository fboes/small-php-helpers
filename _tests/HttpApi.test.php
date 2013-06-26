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

		$api = new HttpApi($baseUrl, HttpApi::RETURN_TYPE_HTML);
		$this->assertTrue(is_object($api));

	}

	public function testObjectInvocation () {
		$baseUrl = 'http://3960.org/';
		$api = new HttpApi($baseUrl, HttpApi::RETURN_TYPE_HTML);
		$result = $api->get(array('a' => 'b'));
		$this->assertTrue(!empty($result));
	}

	public function testXmlConversion () {
		$baseUrl = 'http://3960.org/';

		$api = new HttpApi($baseUrl, HttpApi::RETURN_TYPE_XML);

		$result = $api->get(array(), 'index.xml');
		$this->assertTrue(!empty($result));
		$this->assertEquals(get_class($result), 'SimpleXMLElement');
		$this->outputLine($api);
	}
}

HttpApiTest::doTest();
