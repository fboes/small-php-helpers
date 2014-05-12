<?php

require('../Tester.php');
require('../SuperPDO.php');
require('../Entities.php');
require('../Entity.php');

class Tests extends Entities {
	protected $entityClass = 'Test';

}

class Test extends Entity {
	protected $tableName = 'test';
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
		$this->assertTrue(empty($result), 'No result returned');

		$data = new Test();
		$data->text = 'Lorem ipsum…';

		$newId = $tests->store($data);
		$this->outputLine($tests->getLastCommand());
		$this->outputLine($newId);
		$this->outputLine($data);
		$this->assertTrue(!empty($newId), 'Data written and ID returned');

		$secondNewId = $tests->store($data);
		$this->outputLine($tests->getLastCommand());
		$this->assertEquals($newId, $secondNewId);

		$result = $tests->getById($newId);
		$this->outputLine($result);
		$this->assertTrue(!empty($result), 'Result returned');
		$this->outputLine($tests->getLastCommand());
		$this->assertTrue(is_subclass_of($result, 'Entity'), 'Results is subclass of Entity');
		$this->assertTrue(!empty($result->getId()), 'Result has ID');
		$this->outputLine($result->getId());

		$this->assertTrue(!empty($tests->getFieldPrimaryIndex()), 'Primary index is not empty');
		$this->assertTrue(!empty($result->getFieldPrimaryIndex()), 'Primary index is not empty');

		$result = $tests->getByIds(array(
			$tests->getFieldPrimaryIndex() => $newId
		));
		$this->outputLine($result);
		$this->assertTrue(!empty($result), 'Result returned');
		$this->assertTrue(is_array($result), 'Results in array returned');
		$this->outputLine($tests->getLastCommand());

		$result = $tests->get(array(
			$tests->getFieldPrimaryIndex() => $newId
		));
		$this->outputLine($tests->getLastCommand());
		$this->assertTrue(!empty($result), 'Result returned');

		$result = $tests->deleteById($newId);
		$this->outputLine($tests->getLastCommand());
		$this->assertTrue($result);
	}

}

EntitiesTest::doTest();
