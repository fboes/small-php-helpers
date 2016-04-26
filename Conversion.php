<?php
# namespace fboes\SmallPhpHelpers;

class Conversion {
	const UNIT_MILE_INT = 1609.344;
	const UNIT_MILE_NAUTICAL = 1852;
	const UNIT_FOOT = 0.3048;

	/**
	 * [returnDistance description]
	 * @param  float $meters  [description]
	 * @param  string $unit   like 'm', 'meter', 'km', etc.
	 * @param  int $precision with negative values the current $meters will be rounded to the nearest 10, 100, etc meters
	 * @return string         [description]
	 */
	static public function returnDistance ($meters, $unit = 'm', $precision = 0) {
		switch (strtolower($unit)) {
			case 'ft':
			case 'feet':
			case 'foot':
				$distance = $meters/self::UNIT_FOOT;
				break;
			case 'yd':
			case 'yard':
			case 'yards':
				$distance = $meters/self::UNIT_FOOT/3;
				break;
			case 'mi':
			case 'nautical mile':
			case 'nautical miles':
				$distance = $meters/self::UNIT_MILE_NAUTICAL;
				break;
			case 'mls':
			case 'mile':
			case 'miles':
				$distance = $meters/self::UNIT_MILE_INT;
				break;
			case 'km':
			case 'kilometer':
			case 'kilometers':
				$distance = $meters/1000;
				break;
			default:
				$distance = $meters;
				$unit = 'm';
				break;
		}
		return round($distance, $precision) . ' '. _($unit);
	}

	/**
	 * Convert bearing into plain text bearing description. Will use _() for translation afterwards. Can also be used for relative bearing
	 * @param  float $deg [description]
	 * @param  array $directions [description]
	 * @param  float $fullCircle full circle relative to $deg
	 * @return  string [description]
	 */
	static public function returnBearing ($deg, $directions = array('North','East','South', 'West'), $fullCircle = 360) {
		$directionsCount = count($directions);
		$segmentSize = $fullCircle / $directionsCount;
		$key = round($deg / $segmentSize);
		while ($key >= $directionsCount) {
			$key -= $directionsCount;
		}
		return _($directions[$key]);
	}
}
