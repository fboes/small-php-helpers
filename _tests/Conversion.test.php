<?php

require('../Tester.class.php');
require('../Conversion.class.php');

class ConversionTest extends Tester {
	public function testDistanceOutput () {
		$distance = 100000;
		foreach (array(NULL,'km','ft','yd','mls', 'mi') as $unit) {
			$distString = Conversion::returnDistance($distance, $unit);
			$this->assertTrue(is_string($distString), 'Expecting distance to be string');
			$this->outputLine($distString);
		}
	}


	public function dataBearingOutput () {
		return array(
			'Simple' => array( array('N','E','S', 'W') ),
			'Simple 2' => array( array('North','East','South', 'West') ),
			'More sophisticated' => array( array('North','Northeast','East','Southeast','South','Southeast', 'West', 'Northwest') ),
			'Relative' => array( array('Front','Right','Back','Left') ),
			'Clock' => array( range(1,12) ),
		);
	}

	public function testBearingOutput ($directions) {
		foreach (array(0,1,12,90,134,135,136,180,270,359) as $bearing) {
			$bearingString = Conversion::returnBearing($bearing, $directions);
			$this->assertTrue(is_string($bearingString), 'Expecting bearing to be string for deg '.$bearing);
			$this->outputLine($bearingString);
		}
	}
}

ConversionTest::doTest();
