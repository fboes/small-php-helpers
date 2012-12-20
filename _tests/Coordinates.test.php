<?php

require('../Tester.class.php');
require('../Coordinates.class.php');

class CoordinatesTest extends Tester {
	public function dataSimple () {
		return array(
			'Zero/Zero' => array(0,0),
			'Lighthouse' => array(55.330297,10.96776),
			'Out of bounds' => array(671,471),
		);
	}

	public function testSimple ($lat, $lon) {
		$coords = Coordinates::set($lat, $lon,0,'test');
		$this->assertTrue(is_object($coords), 'Expecting Coordinates::set to return object');

		$this->assertTrue(isset($coords->latitude), 'Expecting latitude to exist');
		$this->assertTrue(isset($coords->longitude), 'Expecting longitude to exist');
		$this->assertTrue(isset($coords->altitude), 'Expecting altitude to exist');

		$this->assertTrue(is_float($coords->latitude), 'Expecting latitude to be float');
		$this->assertTrue(is_float($coords->longitude), 'Expecting longitude to be float');
		$this->assertTrue(is_float($coords->altitude), 'Expecting altitude to be float');

		$this->outputLine($coords);
		$this->outputLine($coords->returnLatitude());
		$this->outputLine($coords->returnLongitude());
	}
}

CoordinatesTest::doTest();
