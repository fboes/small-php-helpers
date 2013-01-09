<?php

require('../Tester.class.php');
require('../Coordinates.class.php');

class CoordinatesTest extends Tester {
	public function dataSimple () {
		return array(
			'Zero/Zero' => array(0,0),
			'Strings' => array("2","4"),
			'Lighthouse' => array(55.330297,10.96776),
			'Out of bounds' => array(671,471,49.0,111.0),
			'At the pole' => array(91,0,89.0,180.0),
		);
	}

	public function testSimple ($lat, $lon, $expLat = NULL, $expLon = NULL) {
		if (empty($expLat)) {
			$expLat = (float)$lat;
		}
		if (empty($expLon)) {
			$expLon = (float)$lon;
		}

		$coords = Coordinates::set($lat, $lon,0,'test');
		$this->assertTrue(is_object($coords), 'Expecting Coordinates::set to return object');
		$this->outputLine($coords);

		$this->assertTrue(isset($coords->latitude), 'Expecting latitude to exist');
		$this->assertTrue(isset($coords->longitude), 'Expecting longitude to exist');
		$this->assertTrue(isset($coords->altitude), 'Expecting altitude to exist');

		$this->assertTrue(is_float($coords->latitude), 'Expecting latitude to be float');
		$this->assertTrue(is_float($coords->longitude), 'Expecting longitude to be float');
		$this->assertTrue(is_float($coords->altitude), 'Expecting altitude to be float');

		$this->assertEquals($coords->latitude, $expLat);
		$this->assertEquals($coords->longitude, $expLon);

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

		// getDistanceToCoordinates
		$distance = $coords1->getDistanceToCoordinates($coords2);
		$this->outputLine($distance);
		$this->assertTrue(is_float($distance), 'Expecting distance to be numeric');
		$this->assertTrue($distance > 0.45 * $coords1->getPlanetMeanRadius(), 'Expecting distance to be a greater than 2887 km');
		$this->assertTrue($distance < 0.46 * $coords1->getPlanetMeanRadius(), 'Expecting distance to be a less than 2888 km');

		// getInitialBearingToCoordinates
		$initialBearing = $coords1->getInitialBearingToCoordinates($coords2);
		$this->outputLine($initialBearing);
		$this->assertTrue(is_numeric($initialBearing), 'Expecting bearing to be numeric');

		// getRelationToCoordinates
		$relation = $coords1->getRelationToCoordinates($coords2);
		$this->assertTrue(is_array($relation));
		$this->assertTrue(!empty($relation['distance']));
		$this->assertEquals($relation['distance'], $distance);
		$this->assertTrue(!empty($relation['bearing']));
		$this->assertEquals($relation['bearing'], $initialBearing);

		// getRelativeCoordinates
		$coords3 = $coords1->getRelativeCoordinates($distance, $initialBearing);
		$this->outputLine($coords3);
		$this->assertTrue(is_object($coords3), 'Expecting return value to be object');
		$this->assertTrue(abs($coords3->latitude  - $coords2->latitude ) < 0.000001, 'Expecting latitude to be close to reference point (lat)');
		$this->assertTrue(abs($coords3->longitude - $coords2->longitude) < 0.000001, 'Expecting latitude to be close to reference point (lon)');

	}

	/**
	 * See http://www.movable-type.co.uk/scripts/latlong.html
	 * @return [type] [description]
	 */
	public function testBearing () {
		$coords1 = Coordinates::set(35, 45, 0, 'Baghdad');
		$coords2 = Coordinates::set(35, 135, 0, 'Osaka');

		$initialBearing = $coords1->getInitialBearingToCoordinates($coords2);
		$this->outputLine($initialBearing);

		$this->assertTrue(is_float($initialBearing), 'Expecting bearing to be numeric');
		$this->assertEquals(round($initialBearing), (float)60, 'Expecting bearing to be ~= 60');
	}

	public function testPolygon () {
		$coords1 = Coordinates::set(0,0,0, 'Center');

		$moreCoords = $coords1->getRegularPolygon(1000 * 1000,4,45);
		$this->assertTrue(is_array($moreCoords), 'Expecting method to return an array');
		$this->assertEquals(count($moreCoords), 4);
		foreach ($moreCoords as $c) {
			$this->assertTrue($c instanceof Coordinates, 'Expecting return items of array to be Coordinates objects');
		}
		$this->outputLine($moreCoords);
	}

	public function testHexagon () {
		$coords1 = Coordinates::set(0,0,0, 'Center');

		$moreCoords = $coords1->getRegularPolygon(400 * 1000,6);
		$this->assertTrue(is_array($moreCoords), 'Expecting method to return an array');
		$this->assertEquals(count($moreCoords), 6);
		foreach ($moreCoords as $c) {
			$this->assertTrue($c instanceof Coordinates, 'Expecting return items of array to be Coordinates objects');
		}
		$this->outputLine($moreCoords);
	}
}

CoordinatesTest::doTest();
