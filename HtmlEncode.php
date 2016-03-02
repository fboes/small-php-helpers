<?php
# namespace fboes\SmallPhpHelpers;

/**
 * @class HtmlEcnode
 * Convert PHP construct to XML
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */
class HtmlEncode {
	public $data;

	/**
	 * [__construct description]
	 * @param mixed $data [description]
	 */
	public function __construct ($data) {
		$this->data = $data;
	}

	/**
	 * Return HTML if object is casted into string
	 * @return string [description]
	 */
	public function __toString() {
		return $this->output();
	}

	/**
	 * [output description]
	 * @return string [description]
	 */
	public function output () {
		$type = "";
		if (is_array($this->data)) {
			$type = ' array';
		}
		elseif (is_object($this->data)) {
			$type = ' object';
		}
		return '<div class="html-encode'.$type.'">'.$this->outputNode($this->data).'</div>';
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
			$return .= '<ul>';
			foreach ($data as $key => $value) {
				$type = "";
				if (is_array($value)) {
					$type = ' class="array"';
				}
				elseif (is_object($value)) {
					$type = ' class="object"';
				}
				elseif (is_integer($value)) {
					$type = ' class="integer"';
				}
				elseif (is_bool($value)) {
					$type = ' class="boolean"';
				}
				$return .= '<li'.$type.'><strong>'.htmlspecialchars($key).'</strong>: <span>'.$this->outputNode($value).'</span></li>';
			}
			$return .= '</ul>';
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
