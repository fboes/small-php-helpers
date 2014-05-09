<?php
# namespace fboes\SmallPhpHelpers;

/**
 * @class Entity
 * Represents a single row of data from a (SQL) table
 * Extend this class, define public variables matching the field in a single row
 */
class Entity {
	/**
	 * Set this variable to match the col containing the Primary Index
	 * @var string
	 */
	protected $fieldIndex = 'id';
	protected $joinStatement; # TODO: Handling of JOINs - and handling of results from JOINs

	/**
	 * [__construct description]
	 */
	public function __construct () {
	}

	/**
	 * Return public variables of Entity as array for storage in DB.
	 * You may want to extend this function to convert some data before storing.
	 * @return array with FIELDNAME => VALUE
	 */
	public function getStorableArray ($isNew = FALSE) {
		$data = (array)$this;
		foreach ($data as $key => $value) {
			if ($key == $this->fieldIndex || strpos($key, '*') === 1) {
				unset($data[$key]);
			}
		}
		$data['date_update'] = date('Y-m-d H:i:s');
		if (!isset($data['date_create']) && $isNew) {
			$data['date_create'] = date('Y-m-d H:i:s');
		}
		elseif (isset($data['date_create']) || is_null($data['date_create'])) {
			unset ($data['date_create']);
		}
		return $data;
	}

	/**
	 * Get fieldname of Entity that can be used as primary index.
	 * @return string fieldname
	 */
	public function getFieldIndex () {
		return $this->fieldIndex;
	}

	/**
	 * Get (SQL) statement to use as JOIN-component of a SELECT-Statement.
	 * This may be useful to automatically join other entities
	 * @return string SQL
	 */
	public function getJoinStatement () {
		return $this->joinStatement;
	}

	/**
	 * Get the current ID fo this Entity
	 * @return string [description]
	 */
	public function getId () {
		if (!empty($this->fieldIndex)) {
			#return $this->$this->fieldIndex; # TODO: This is wrong
		}
		return NULL;
	}
}