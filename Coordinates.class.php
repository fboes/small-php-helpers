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

	public $planetMeanRadius = 63781370.0;

	/**
	 * Define planet. Defaults to Earth
	 * @param float $planetMeanRadius in meters
	 */
	public function __construct ($planetMeanRadius =   6371009.0) {
		$this->planetMeanRadius = (float)$planetMeanRadius;
	}

	/**
	 * Will set $this->planetMeanRadius for astronomical bodies
	 * http://en.wikipedia.org/wiki/Earth_radius#Mean_radius
	 * @param float $equatorialRadius in meters. Aka semi-major axis
	 * @param float $polarRadius      in meters. Aka semi-minor axis
	 * @return  self [description]
	 */
	public function setPlanetaryRadius ($equatorialRadius, $polarRadius) {
		$this->planetMeanRadius = (2 * (float)$equatorialRadius + (float)$polarRadius) / 3;
		return $this;
	}

	/**
	 * Static inovation of object for chaining
	 * @see  $this->setCoordinates()
	 * @param [type] $latitude  [description]
	 * @param [type] $longitude [description]
	 * @param float  $altitude  [description]
	 * @param [type] $title     [description]
	 * @return  self [description]
	 */
	static public function set ($latitude, $longitude, $altitude = 0.0, $title = NULL) {
		$obj = new static();
		return $obj->setCoordinates($latitude, $longitude, $altitude, $title);
	}

	/**
	 * [setCoordinates description]
	 * @param float  $latitude  in decimal degrees
	 * @param float  $longitude in decimal degrees
	 * @param float  $altitude  in meters
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

	/**
	 * Return distance between this coordinates and given coordinates
	 * http://stackoverflow.com/questions/365826/calculate-distance-between-2-gps-coordinates
	 * @param  Coordinates $coordinates [description]
	 * @return float                   in meters
	 */
	public function getDistanceToCoordinates (Coordinates $coordinates) {
		if ($this->planetMeanRadius != $coordinates->planetMeanRadius) {
			throw new Exception('MeanRadius does not match, coordinates seem to be on different planets');
		}
		$dLat = deg2rad($coordinates->latitude - $this->latitude);
		$dLon = deg2rad($coordinates->longitude - $this->longitude);
		$lat1 = deg2rad($this->latitude);
		$lat2 = deg2rad($coordinates->latitude);

		$a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2);
		$c = 2 * atan2(sqrt($a), sqrt(1-$a));
		return ($this->planetMeanRadius + $this->altitude - $coordinates->altitude) * $c;
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