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
}

ConversionTest::doTest();
