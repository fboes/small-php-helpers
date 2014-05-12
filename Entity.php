<?php
# namespace fboes\SmallPhpHelpers;

/**
 * @class Entity
 * Represents a single row of data from a (SQL) table
 * Extend this class, define public variables matching the field in a single row
 */
class Entity {
	/**
	 * Set this string to match the (SQL)-tablename containing the rows
	 * @var string
	 */
	protected $tableName         = '';
	/**
	 * Set this variable to match the col containing the Primary Index
	 * @var string
	 */
	protected $fieldPrimaryIndex = 'id';
	/**
	 * Optional (SQL) join statement to be used on every select query
	 * @var string
	 */
	protected $statementJoin     = '';
	/**
	 * Optional: Which (SQL) fields to fetch
	 * @var string
	 */
	protected $expressionSelect  = '*';

	# add more variables here

	/**
	 * [__construct description]
	 */
	public function __construct () {
		if (empty($this->tableName)) {
			throw new \Exception ('Tablename has to be set for '.get_class($this).', "'.$this->tableName.'" given');
		}
	}

	/**
	 * Return public variables of Entity as array for storage in DB.
	 * You may want to extend this function to convert some data before storing.
	 * @return array with FIELDNAME => VALUE
	 */
	public function getStorableArray ($isNew = FALSE) {
		$data = (array)$this;
		foreach ($data as $key => $value) {
			if ($key == $this->fieldPrimaryIndex || strpos($key, '*') === 1) {
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
		# add more operations here
		return $data;
	}

	/**
	 * Convert variables after fetching, e.g. convert SQL datetime to PHP timestamp, or convert strings to integer
	 * @return Entity $this
	 */
	public function postFetch () {
		if (!empty($this->date_create)) {
			$this->date_create = strtotime($this->date_create);
		}
		if (!empty($this->date_update)) {
			$this->date_update = strtotime($this->date_update);
		}
		# add more operations here
		return $this;
	}

	/**
	 * Get fieldname of Entity that can be used as primary index.
	 * @return string fieldname
	 */
	public function getFieldPrimaryIndex () {
		return $this->fieldPrimaryIndex;
	}

	/**
	 * Get (SQL) statement to use as JOIN-component of a SELECT-Statement.
	 * This may be useful to automatically join other entities
	 * @return string SQL
	 */
	public function getStatementJoin () {
		return $this->statementJoin;
	}

	/**
	 * Get (SQL) select expression (fields to fetch) for a regular select
	 * @return string SQL
	 */
	public function getExpressionSelect () {
		return $this->expressionSelect;
	}

	/**
	 * Get (SQL) table name for all queries
	 * @return string SQL
	 */
	public function getTableName () {
		return $this->tableName;
	}

	/**
	 * Get the current ID fo this Entity
	 * @return string [description]
	 */
	public function getId () {
		if (!empty($this->fieldPrimaryIndex)) {
			return $this->{$this->fieldPrimaryIndex};
		}
		return NULL;
	}
}