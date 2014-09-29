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
				$this->data[] = $line;
			}
		}
		return $this->data;
	}

	/**
	 * Write a single array to CSV line. If a key array was given on __construct, the array will be forced into this structure
	 * @param  array   $line    [description]
	 * @param  boolean $locking Enable file locking while writing
	 * @return boolean          [description]
	 */
	public function writeLine (array $line, $locking = TRUE) {
		$storeData = $this->normalizeLine($line);
		if ($locking) {
			fseek($this->fp, 0, SEEK_END);
		}
		flock($this->fp, LOCK_EX);
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
	public function writeLines (array $lines) {
		$success = TRUE;
		foreach ($lines as $line) {
			$success = $this->writeLine($line) && $success;
		}
		return $success;
	}

	/**
	 * Copy contents of $this->data to file
	 * @return boolean [description]
	 */
	public function writeEntireFile () {
		$sucess = true;
		flock($this->fp, LOCK_EX);
		ftruncate($this->fp, 0);
		foreach ($this->data as $data) {
			$this->writeLine($data, FALSE);
		}
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