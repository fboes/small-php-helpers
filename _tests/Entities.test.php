<?php

require('../Tester.php');
require('../SuperPDO.php');
require('../Entities.php');
require('../Entity.php');
require('../EntityFile.php');

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

class TestFiles extends Tests {
	protected $entityClass = 'TestFile';

}
class TestFile extends EntityFile {
	protected $tableName = 'test';
	public $id;
	public $text;
	public $date_update;
	public $date_create;
	protected $documentRoot = __DIR__;
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
		$this->assertTrue(!empty($newId), 'Data written and ID returned for ->store');

		$secondNewId = $tests->store($data);
		$this->outputLine($tests->getLastCommand());
		$this->assertEquals($newId, $secondNewId);

		$result = $tests->getById($newId);
		$this->outputLine($result);
		$this->assertTrue(!empty($result), 'Result returned for ->getById');
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
		$this->assertTrue(!empty($result), 'Result returned for ->getByIds');
		$this->assertTrue(is_array($result), 'Results in array returned');
		$this->outputLine($tests->getLastCommand());

		$result = $tests->getTotalCountForLastGet();
		$this->outputLine($tests->getLastCommand());
		$this->assertTrue(is_integer($result), 'Result returned for ->getTotalCountForLastGet is integer');
		$this->assertTrue($result >= 1, 'Result returned for ->getTotalCountForLastGet is >= 1');

		$result = $tests->get(array(
			$tests->getFieldPrimaryIndex() => $newId
		));
		$this->outputLine($tests->getLastCommand());
		$this->assertTrue(!empty($result), 'Result returned for ->get');

		$result = $tests->deleteById($newId);
		$this->outputLine($tests->getLastCommand());
		$this->assertTrue($result);
	}

	public function testFiles () {
		$tmpfile = __DIR__.'/temp.txt';
		touch($tmpfile);
		$this->assertTrue(file_exists($tmpfile), 'Temp file created');

		$tests = new TestFiles('mysql:host=localhost;dbname=test','test','test');
		$this->assertTrue(is_object($tests), 'Entities is object');
		#$this->outputLine($tests);

		$data = new TestFile();
		$data->text = 'Lorem ipsum…';
		$newId = $tests->store($data);

		$newId = $tests->store($data);
		$this->outputLine($newId);
		$this->outputLine($data);
		$this->outputLine($tests->getLastCommand());
		$this->assertTrue(!empty($newId) && $newId >= 0, 'Data written and ID returned for ->store');

		$filename = $data->getFilename('test.txt');
		$this->outputLine($filename);
		$this->assertTrue(!empty($filename), 'Filename conversion works');
		$this->assertEquals(basename($filename), 'test.txt');

		$filename = $data->getFilename('öäütest.txt');
		$this->outputLine($filename);
		$this->assertTrue(!empty($filename));
		$this->assertEquals(basename($filename), 'test.txt');

		$filename = $data->getFilename('../../test.txt');
		$this->outputLine($filename);
		$this->assertTrue(!empty($filename));
		$this->assertEquals(basename($filename), 'test.txt');

		$filename = $data->getFilename('blafasel/öäütest.txt');
		$this->outputLine($filename);
		$this->assertTrue(!empty($filename));
		$this->assertEquals(basename($filename), 'test.txt');

		$filename = $data->getFilename('blafasel/öäütest.txt', TRUE);
		$this->outputLine($filename);
		$filename = $data->getFilename('blafasel/öäütest.txt');
		$this->outputLine($filename);

		$data->storeFile($tmpfile, 'test.txt');
		$this->outputLine($data->getFilename('test.txt', TRUE));
		$this->assertTrue(!file_exists($tmpfile), 'Temp file was moved…');
		$this->assertTrue(file_exists($data->getFilename('test.txt', TRUE)), '…to storage area');
		$this->assertTrue(!empty($data->getFile('test.txt', TRUE)));

		$files = $data->getAllFiles();
		$this->outputLine($files);
		$this->assertTrue(is_array($files), 'Files listing is an array');
		$this->assertEquals(count($files), 1);

		$success = $data->deleteAllFiles();
		$this->assertTrue($success == 1, 'Al files have been deleted');

	}

}

EntitiesTest::doTest();
