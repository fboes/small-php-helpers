<?php

require('../Tester.class.php');
require('../HttpApi.class.php');

class HttpApiTest extends Tester {
	public function testObjectInvocation () {
		$baseUrl = 'http://3960.org/';

		$api = new HttpApi($baseUrl, HttpApi::RETURN_TYPE_HTML);
		$this->assertTrue(is_object($api));
		$this->outputLine($api);

		$result = $api->get(array('a' => 'b'));
		$this->assertTrue(!empty($result));
	}
}

HttpApiTest::doTest();
