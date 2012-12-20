<?php
/**
 * @class Coordinates
 * Mini-Unit-Test (in case PhpUnit ist not available)
 * Extend this class for doing the real test. Methods with "test" prefixed get tested. Methods with 'data' prefixed are used as data providers for corresponding "test"-methods. "data"-methods MUST return an array of arrays.
 * This class intentionally has direct HTML output.
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */

class Coordinates {
	public $latitude  = NULL;
	public $longitude = NULL;
	public $altitude  = 0;
	public $title     = NULL;

	public $planetEquatorialRadiusMeters = 63781370.0;
	public $planetPolarRadiusMeters      = 63567523.0;

	/**
	 * Define planet. Defaults to Earth
	 * @param float $planetEquatorialRadiusMeters aka semi-major axis, in meters
	 * @param float $planetPolarRadiusMeters      aka semi-minor axis, in meters
	 */
	public function __construct ($planetEquatorialRadiusMeters = 63781370.0, $planetPolarRadiusMeters =  63567523.0) {
		$this->planetEquatorialRadiusMeters = (float)$planetEquatorialRadiusMeters;
		$this->planetPolarRadiusMeters      = !empty($planetPolarRadiusMeters) ? (float)$planetPolarRadiusMeters : $planetEquatorialRadiusMeters;
	}

	static public function set ($latitude, $longitude, $altitude = 0.0, $title = NULL) {
		$obj = new static();
		return $obj->setCoordinates($latitude, $longitude, $altitude, $title);
	}

	/**
	 * [setCoordinates description]
	 * @param float  $latitude  [description]
	 * @param float  $longitude [description]
	 * @param float  $altitude  [description]
	 * @param string  $title     [description]
	 * @return  self [description]
	 */
	public function setCoordinates ($latitude, $longitude, $altitude = 0.0, $title = NULL) {
		$this->latitude  = static::stayInRange((float)$latitude,  -90.0,   90.0);
		$this->longitude = static::stayInRange((float)$longitude, -180.0, 180.0);
		$this->altitude  = (float)$altitude;
		$this->title     = (string)$title;
		return $this;
	}

	static protected function stayInRange ($value, $min, $max) {
		if ($min > $max) {
			throw new Exception('$min must be smaller than $max in '.__METHOD__);
		}
		$fullRange = abs($max - $min);
		while ($value < $min) {
			$value += $fullRange;
		}
		while ($value > $max) {
			$value -= $fullRange;
		}
		return $value;
	}

	/**
	 * Return latitude for output
	 * @see  returnCoordinate()
	 * @param  string $format see sprintf() for options. The first parameter is decimal degrees, the second parameter the hemisphere identifier
	 * @return string         [description]
	 */
	public function returnLatitude  ($format = '%1$09.5f %2$s') {
		return static::returnCoordinate($this->latitude, _('N'), _('S'), $format);
	}

	/**
	 * Return longitude for output
	 * @see  returnCoordinate()
	 * @param  string $format see sprintf() for options. The first parameter is decimal degrees, the second parameter the hemisphere identifier
	 * @return string         [description]
	 */
	public function returnLongitude ($format = '%1$09.5f %2$s') {
		return static::returnCoordinate($this->longitude, _('E'), _('W'), $format);
	}

	static public function returnCoordinate ($value, $plusPrefix, $minusPrefix, $format = '%1$09.5f %2$s') {
		return sprintf($format, abs($value), static::convertPrefix($value, $plusPrefix, $minusPrefix));
	}

	static protected function convertPrefix ($value, $plusPrefix, $minusPrefix) {
		return ($value < 0)
			? $minusPrefix
			: $plusPrefix
		;
	}
}