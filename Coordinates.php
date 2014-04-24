<?php
# namespace fboes\SmallPhpHelpers;

/**
 * @class Coordinates
 * Mini-Unit-Test (in case PhpUnit ist not available)
 * Extend this class for doing the real test. Methods with "test" prefixed get tested. Methods with 'data' prefixed are used as data providers for corresponding "test"-methods. "data"-methods MUST return an array of arrays.
 * This class intentionally has direct HTML output.
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
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
	 * @param float $latitude  [description]
	 * @param float $longitude [description]
	 * @param float  $altitude  [description]
	 * @param string $title     [description]
	 * @return  self [description]
	 */
	static public function set ($latitude, $longitude, $altitude = 0.0, $title = NULL) {
		$obj = new static();
		return $obj->setCoordinates((float)$latitude, (float)$longitude, (float)$altitude, $title);
	}

	/**
	 * Get mean radius of current planet.
	 * @return float in meters
	 */
	public function getPlanetMeanRadius () {
		return (float)$this->planetMeanRadius;
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

	/**
	 * Convert decimal degrees into radians. Uses memoization for performance improvement.
	 * @param  float $deg [description]
	 * @return float      [description]
	 */
	public function returnDeg2Rad ($deg) {
		$deg = (float)$deg;
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
	 * Return distance between this coordinates and given coordinates. Uses haversine formula, ignores altitude.
	 * @see http://www.movable-type.co.uk/scripts/latlong.html
	 * @param  Coordinates $coordinates [description]
	 * @return float                   in meters
	 */
	public function getDistanceToCoordinates (Coordinates $coordinates) {
		if ($this->getPlanetMeanRadius() != $coordinates->getPlanetMeanRadius()) {
			throw new Exception('Mean radius does not match, coordinates seem to be on different planets');
		}
		$dLat = deg2rad($coordinates->latitude  - $this->latitude);
		$dLon = deg2rad($coordinates->longitude - $this->longitude);
		$lat1 = $this->latitudeRad();
		$lat2 = $coordinates->latitudeRad();

		$a = sin($dLat/2) * sin($dLat/2) + sin($dLon/2) * sin($dLon/2) * cos($lat1) * cos($lat2);
		$c = 2 * atan2(sqrt($a), sqrt(1-$a));
		return $this->planetMeanRadius * $c;
	}

	/**
	 * Get bearing at current position to move to given coordinates, ignoring altitude.
	 * @see http://www.movable-type.co.uk/scripts/latlong.html
	 * @param  Coordinates $coordinates [description]
	 * @return float                   in decimal degrees
	 */
	public function getInitialBearingToCoordinates (Coordinates $coordinates) {
		$lat1 = $this->latitudeRad();
		$lon1 = $this->longitudeRad();
		$lat2 = $coordinates->latitudeRad();
		$lon2 = $coordinates->longitudeRad();

		$dLon = $lon2 - $lon1;

		$y = sin($dLon) * cos($lat2);
		$x = cos($lat1) * sin($lat2) - sin($lat1) * cos($lat2) * cos($dLon);

		$deg = $this->keepDegreesInBounds(rad2deg(atan2($y, $x)));
		return $deg;
	}

	public function getRelationToCoordinates (Coordinates $coordinates) {
		return array(
			'distance' => $this->getDistanceToCoordinates($coordinates),
			'bearing'  => $this->getInitialBearingToCoordinates($coordinates),
		);
	}


	/**
	 * Get new coordinates relative to current coordinates by using given distance and initial bearing, ignoring altitude.
	 * @see http://www.movable-type.co.uk/scripts/latlong.html
	 * @param float $distance in meters
	 * @param float $bearing in deciaml degrees
	 * @return Coordinates [description]
	 */
	public function getRelativeCoordinates ($distance, $bearing) {
		$distance = (float)$distance;
		$bearing  = $this->keepDegreesInBounds((float)$bearing);

		$brng = deg2rad($bearing);
		$d    = $distance;
		$lat1 = $this->latitudeRad();
		$lon1 = $this->longitudeRad();
		$R    = $this->planetMeanRadius;

		$lat2 = asin( sin($lat1) * cos($d/$R) + cos($lat1) * sin($d/$R) * cos($brng) );
		$lon2 = $lon1 + atan2(sin($brng) * sin($d/$R) * cos($lat1), cos($d/$R) - sin($lat1) * sin($lat2));

		$newCoordinates = new static($this->planetMeanRadius);
		$newCoordinates->setCoordinates(rad2deg($lat2), rad2deg($lon2), $this->altitude,
			sprintf(_('%s m with direction %s deg from "%s"'), round($distance), round($bearing), $this->title)
		);
		return $newCoordinates;
	}

	/**
	 * Build regular ploygon with current coordinates in center.
	 * @param  float  $distance in meters
	 * @param  int $vertices number of vertices, set 4 for a square
	 * @param  float $offsetDeg rotate polygon by x degrees initially. For a regular square this would be 45
	 * @return array            of Coordinates
	 */
	public function getRegularPolygon ($distance, $vertices = 4, $offsetDeg = 0) {
		$verticeCoords = array();
		$step = 360 / $vertices;
		for ($curVertice = 0; $curVertice <= ($vertices-1); $curVertice ++) {
			$verticeCoords[] = $this->getRelativeCoordinates( $distance, ($curVertice * $step) + $offsetDeg );
		}
		return $verticeCoords;
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

	protected function keepDegreesInBounds ($deg) {
		return $this->keepInBounds($deg,360,0);
	}

	protected function keepInBounds ($value, $max, $min = 0) {
		while ($value >= $max) {
			$value -= $max;
		}
		while ($value < $min) {
			$value += $max;
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