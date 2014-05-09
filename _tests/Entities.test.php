<?php

require('../Tester.php');
require('../SuperPDO.php');
require('../Entities.php');
require('../Entity.php');

class Tests extends Entities {
	protected $entityClass = 'Test';
	protected $tableName = 'test';

}

class Test extends Entity {
	public $id;
	public $text;
	public $date_update;
	public $date_create;
}

class EntitiesTest extends Tester {
	public function testSimple () {
		$tests = new Tests('mysql:host=localhost;dbname=test','test','test');
		$this->assertTrue(is_object($tests), 'Entities is object');
		#$this->outputLine($tests);

		$result = $tests->getById(1);
		$this->assertTrue(empty($result));

		$data = new Test();
		$data->text = 'Lorem ipsum…';

		$newId = $tests->store($data);
		$this->assertTrue(!empty($newId), 'Id is returned');
		$this->outputLine($newId);

		$result = $tests->getById($newId);
		$this->assertTrue(!empty($result));
		$this->outputLine($tests->getLastCommand());

		$result = $tests->deleteById($newId);
		$this->assertTrue($result);
		$this->outputLine($tests->getLastCommand());
	}

}

EntitiesTest::doTest();
