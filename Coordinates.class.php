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

	protected $planetMeanRadius = 6371009.0;
	protected $memoDeg2Rad = array();

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
	static public function onSpecialPlanet ($equatorialRadius, $polarRadius) {
		return new static((2 * (float)$equatorialRadius + (float)$polarRadius) / 3);
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

	public function getPlanetMeanRadius () {
		return $this->planetMeanRadius;
	}

	/**
	 * Return current latitude as radians
	 * @return float
	 */
	public function latitudeRad () {
		return $this->returnDeg2Rad($this->latitude);
	}

	/**
	 * Return current longitude as radians
	 * @return float
	 */
	public function longitudeRad () {
		return $this->returnDeg2Rad($this->longitude);
	}

	public function returnDeg2Rad ($deg) {
		if (empty($this->memoDeg2Rad[$deg])) {
			$this->memoDeg2Rad[$deg] =  deg2rad($deg);
		}
		return $this->memoDeg2Rad[$deg];
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
		$this->latitude  = (float)$latitude;
		$this->longitude = (float)$longitude;
		$this->keepCoordinatesInBounds();
		$this->altitude  = (float)$altitude;
		$this->title     = (string)$title;
		return $this;
	}

	/**
	 * Return distance between this coordinates and given coordinates. Uses haversine formula
	 * @see http://www.movable-type.co.uk/scripts/latlong.html
	 * @param  Coordinates $coordinates [description]
	 * @return float                   in meters
	 */
	public function getDistanceToCoordinates (Coordinates $coordinates) {
		if ($this->getPlanetMeanRadius() != $coordinates->getPlanetMeanRadius()) {
			throw new Exception('MeanRadius does not match, coordinates seem to be on different planets');
		}
		$dLat = deg2rad($coordinates->latitude - $this->latitude);
		$dLon = deg2rad($coordinates->longitude - $this->longitude);
		$lat1 = $this->latitudeRad();
		$lat2 = $coordinates->latitudeRad();

		$a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2);
		$c = 2 * atan2(sqrt($a), sqrt(1-$a));
		return ($this->planetMeanRadius + $this->altitude - $coordinates->altitude) * $c;
	}

	/**
	 * Get bearing at current position to move to given coordinates
	 * @see http://www.movable-type.co.uk/scripts/latlong.html
	 * @param  Coordinates $coordinates [description]
	 * @return float                   in decimal degrees
	 */
	public function getInitialBearingToCoordinates (Coordinates $coordinates) {
		$lat1 = $this->latitudeRad();
		$lon1 = $this->longitudeRad();
		$lat2 = $coordinates->latitudeRad();
		$lon2 = $coordinates->longitudeRad();

		$y = sin($dLon) * cos($lat2);
		$x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);

		return (rad2deg(atan2($y, $x)) + 360) % 360;
	}


	/**
	 * Get new coordinates relative to current coordinates by using given distance and initial bearing
	 * @see http://www.movable-type.co.uk/scripts/latlong.html
	 * @param float $distance in meters
	 * @param float $bearing in deciaml degrees
	 * @return Coordinates [description]
	 */
	public function getRelativeCoordinates ($distance, $bearing) {
		$distance = (float)$distance;
		$bearing  = (float)$bearing;

		$brng = deg2rad($bearing);
		$d    = $distance;
		$lat1 = $this->latitudeRad();
		$lon1 = $this->longitudeRad();
		$R    = $this->planetMeanRadius;

		$lat2 = asin( sin($lat1) * cos($d/$R) + cos($lat1) * sin($d/$R) * cos($brng) );
		$lon2 = $lon1 + atan2(sin($brng) * sin($d/$R) * cos($lat1), cos($d/$R) - sin($lat1) * sin($lat2));

		$newCoordinates = new static($this->planetMeanRadius);
		$newCoordinates->setCoordinates(rad2deg($lat2), rad2deg($lon2), $this->altitude,
			sprintf(_('%s ° & %s m from "%s"'), $bearing, $distance, $this->title)
		);
		return $newCoordinates;
	}

	/**
	 * Check if current coordinates are in bounds. If latitude is out of bounds this function will also convert longitude, implying the given coordinates are on a line extending beyond the pole.
	 * @return self [description]
	 */
	protected function keepCoordinatesInBounds () {
		while ($this->latitude > 90) {
			$this->latitude  = 180 - $this->latitude;
			$this->longitude -= 180;
		}
		while ($this->latitude < -90) {
			$this->latitude  = 180 + $this->latitude;
			$this->longitude += 180;
		}
		while ($this->longitude > 180) {
			$this->longitude -= 360;
		}
		while ($this->longitude <= -180) {
			$this->longitude += 360;
		}
		return $this;
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