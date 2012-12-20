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
		$this->outputLine($coords);

		$this->assertTrue(isset($coords->latitude), 'Expecting latitude to exist');
		$this->assertTrue(isset($coords->longitude), 'Expecting longitude to exist');
		$this->assertTrue(isset($coords->altitude), 'Expecting altitude to exist');

		$this->assertTrue(is_float($coords->latitude), 'Expecting latitude to be float');
		$this->assertTrue(is_float($coords->longitude), 'Expecting longitude to be float');
		$this->assertTrue(is_float($coords->altitude), 'Expecting altitude to be float');

		$this->outputLine($coords->returnLatitude());
		$this->outputLine($coords->returnLongitude());
	}

	/**
	 * See http://en.wikipedia.org/wiki/Great-circle_distance
	 * @return [type] [description]
	 */
	public function testDistance () {
		$coords1 = Coordinates::set(36.12, -86.67, 0, 'BNA');
		$coords2 = Coordinates::set(33.94, -118.40, 0, 'LAX');

		$distance = $coords1->getDistanceToCoordinates($coords2);
		$this->outputLine($distance);

		$this->assertTrue(is_float($distance), 'Expecting distance to be a floating point number');
		$this->assertTrue($distance > 0.45 * $coords1->planetMeanRadius, 'Expecting distance to be a greater than 2887 km');
		$this->assertTrue($distance < 0.46 * $coords1->planetMeanRadius, 'Expecting distance to be a less than 2888 km');
	}
}

CoordinatesTest::doTest();
