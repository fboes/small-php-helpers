<?php
# namespace fboes\SmallPhpHelpers;

/**
 * @class CsvInterface
 * Load CSV files and write CSV files
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */

class CsvInterface {
	protected $delimiter = ';';
	protected $enclosure = '"';
	protected $fp;

	protected $keys = array();
	protected $filename;
	protected $data = array();

	protected $encodingFile;
	protected $encodingScript = 'UTF-8';

	/**
	 * Create / Open file
	 * @param string $filename [description]
	 * @param array  $keys     [description]
	 */
	public function __construct ($filename, array $keys = array()) {
		$this->filename = $filename;
		$this->fp = fopen($this->filename, 'r+');
		$this->keys = $keys;
		if (!$this->fp) {
			throw new Exception('Error creating file handle for '.$this->filename);

		}
	}

	/**
	 * See http://de1.php.net/mb_convert_encoding & http://de1.php.net/manual/de/mbstring.supported-encodings.php
	 * @param string $encodingFile e.g. 'Windows-1252'
	 */
	public function setEncoding ($encodingFile) {
		$this->encodingFile = $encodingFile;
	}

	/**
	 * Close file handler
	 */
	public function __destruct () {
		fclose($this->fp);
	}

	/**
	 * Read entire CSV file into an (associative) array
	 * @return array of arrays containing the CSV data
	 */
	public function getContents () {
		if (!empty($this->keys)) {
			$keyCount = count($this->keys);
		}
		$this->data = array();
		while (($line = fgetcsv($this->fp, 1000, $this->delimiter, $this->enclosure)) !== FALSE) {
			if ($keyCount > 0) {
				$line =  (count($line) === $keyCount)
					? array_combine($this->keys, $line)
					: NULL
				;
			}
			if (!empty($line)) {
				if (!empty($this->encodingFile)) {
					foreach ($line as $key => $value) {
						$line[$key] = mb_convert_encoding($value,$this->encodingScript,$this->encodingFile);
					}
				}
				$this->data[] = $line;
			}
		}
		return $this->data;
	}

	/**
	 * Remoe a line from $this->data where $key = $value
	 * @param  string  $key   [description]
	 * @param  string  $value [description]
	 * @return integer        Number of lines removed
	 */
	public function removeLineByData ($key, $value) {
		$deleted = 0;
		if (!empty($this->data)) {
			foreach ($this->data as $lineKey => $line) {
				if (!empty($line[$key]) && $line[$key] == $value) {
					unset($this->data[$lineKey]);
					$deleted ++;
				}
			}
		}
		return $deleted;
	}

	/**
	 * Add single lines to internal representation of data
	 * @param  array   $line [description]
	 * @return boolean       [description]
	 */
	public function addLine (array $line) {
		$storeData = $this->normalizeLine($line);
		$this->data[] = $storeData;
		return TRUE;
	}

	/**
	 * Add multiple lines to internal representation of data
	 * @param  array   $lines [description]
	 * @return boolean        [description]
	 */
	public function addLines (array $lines) {
		$success = TRUE;
		foreach ($lines as $line) {
			$success = $this->addLine($line) && $success;
		}
		return $success;
	}

	/**
	 * Write a single array to CSV line. If a key array was given on __construct, the array will be forced into this structure
	 * @param  array   $line    [description]
	 * @param  boolean $locking Enable file locking while writing
	 * @return boolean          [description]
	 */
	public function writeLine (array $line, $locking = TRUE) {
		$storeData = $this->normalizeLine($line);
		fseek($this->fp, 0, SEEK_END);
		if ($locking) {
			flock($this->fp, LOCK_EX);
		}
		if (!empty($this->encodingFile)) {
			foreach ($storeData as $name => $value) {
				$storeData[$name] = mb_convert_encoding($value,$this->encodingFile,$this->encodingScript);
			}
		}
		$success = fputcsv($this->fp, $storeData, $this->delimiter, $this->enclosure);
		if ($locking) {
			flock($this->fp, LOCK_UN);
		}
		$this->data[] = $storeData;
		return $success;
	}

	/**
	 * Write mutiple lines via $this->writeLine
	 * @param  array   $lines [description]
	 * @return boolean        [description]
	 */
	public function writeLines (array $lines, $locking = TRUE) {
		$success = TRUE;
		if ($locking) {
			flock($this->fp, LOCK_EX);
		}
		foreach ($lines as $line) {
			$success = $this->writeLine($line, FALSE) && $success;
		}
		if ($locking) {
			flock($this->fp, LOCK_UN);
		}
		return $success;
	}

	/**
	 * Copy contents of $this->data to file
	 * @return boolean [description]
	 */
	public function writeEntireFile () {
		flock($this->fp, LOCK_EX);
		ftruncate($this->fp, 0);
		$success = $this->writeLines($this->data, FALSE);
		flock($this->fp, LOCK_UN);
		return $sucess;
	}

	/**
	 * Convert array to an array matching $this->keys
	 * @param  array  $line [description]
	 * @return [type]       [description]
	 */
	protected function normalizeLine (array $line) {
		$storeData = array();
		if (!empty($this->keys)) {
			foreach ($this->keys as $name) {
				$storeData[$name] = !empty($line[$name])
					? $line[$name]
					: ''
				;
			}
		}
		else {
			$storeData = $line;
		}
		return $storeData;
	}
}