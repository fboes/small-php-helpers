<?php
# namespace fboes\SmallPhpHelpers;

/**
 * @class XmlEncode
 * Convert PHP construct to XML
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */
class XmlEncode {
	public $data;

	/**
	 * [__construct description]
	 * @param mixed $data [description]
	 */
	public function __construct ($data) {
		$this->data = $data;
	}

	/**
	 * [output description]
	 * @return string [description]
	 */
	public function output () {
		$type = "";
		if (is_array($this->data)) {
			$type = ' type="array"';
		}
		elseif (is_object($this->data)) {
			$type = ' type="object"';
		}
		return '<?xml version="1.0" encoding="UTF-8"?><response'.$type.'>'.$this->outputNode($this->data).'</response>';
	}

	/**
	 * [outputNode description]
	 * @param  mixed $data [description]
	 * @return string       [description]
	 */
	protected function outputNode ($data) {
		$return = NULL;
		if (is_bool($data)) {
			$return = $data ? 1 : 0;
		}
		elseif (is_scalar($data)) {
			$return = htmlspecialchars($data);
		}
		elseif (is_array($data)) {
			foreach ($data as $key => $value) {
				$type = "";
				if (is_array($value)) {
					$type = ' type="array"';
				}
				elseif (is_object($value)) {
					$type = ' type="object"';
				}
				elseif (is_integer($value)) {
					$type = ' type="integer"';
				}
				elseif (is_bool($value)) {
					$type = ' type="boolean"';
				}
				if (is_integer($key) || preg_match('#^[0-9]#',$key)) {
					$return .= '<item key="'.htmlspecialchars($key).'"'.$type.'>'.$this->outputNode($value).'</item>';
				}
				else {
					$return .= '<'.$key.$type.'>'.$this->outputNode($value).'</'.$key.'>';
				}
			}
		}
		elseif (is_object($data)) {
			$objData = get_object_vars($data);
			$return .= $this->outputNode($objData);
		}
		return $return;
	}

	/**
	 * Static constructor with output. Converting PHP construct to XML string
	 * @param  mixed $data [description]
	 * @return string       [description]
	 */
	public static function encode ($data) {
		$xml = new self($data);
		return $xml->output();
	}

}