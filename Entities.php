<?php
# namespace fboes\SmallPhpHelpers;
# use fboes\SmallPhpHelpers\SuperPDO;

/**
 * @class Entities
 * Manipulate a (SQL) table full of entities
 * Extend this class
 */
class Entities {
	protected $dbDsn;
	protected $dbUser;
	protected $dbPassword;

	/**
	 * Set this string to match the (SQL)-tablename containing the rows
	 * @var string
	 */
	protected $tableName = '';
	/**
	 * Set this string to match the class name of an instance of Entities. Entities has to match the row structure of a single row.
	 * @var string
	 */
	protected $entityClass = '';

	protected $entityPrototype;
	protected $db;

	/**
	 * Set DB parameters
	 * @param string $dbDsn      e.g. 'mysql:host=localhost;dbname=test'
	 * @param string $dbUser     e.g. 'test'
	 * @param string $dbPassword e.g. 'password'
	 */
	public function __construct ($dbDsn, $dbUser, $dbPassword) {
		$this->dbDsn       = $dbDsn;
		$this->dbUser      = $dbUser;
		$this->dbPassword  = $dbPassword;
		if (empty($this->tableName)) {
			throw new \Exception ('Tablename has to be set for '.get_class($this).', "'.$this->tableName.'" given');
		}
		if (empty($this->entityClass)) {
			throw new \Exception ('No entityClass set in '.get_class($this));
		} elseif (!is_subclass_of($this->entityClass, 'Entity')) {
			throw new \Exception ('entityClass has to be of type Entity in '.get_class($this));
		}
		$this->entityPrototype = new $this->entityClass;
	}

	/**
	 * Get reusable PDO
	 * @return SuperPDO [description]
	 */
	protected function getDb () {
		if (empty($this->db)) {
			$this->db = new SuperPDO($this->dbDsn, $this->dbUser, $this->dbPassword);
			$this->db->useUft8();
			$this->db->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_CLASS);
		}
		return $this->db;
	}

	/**
	 * Get a single Entities by ID
	 * @param  integer $id [description]
	 * @return Entity      or NULL
	 */
	public function getById ($id) {
		$results = $this->getByIds(array($id));
		return (!empty($results)) ? $results[0] : NULL;
	}

	/**
	 * Get multiple Entities by ID
	 * @param  array  $ids   [description]
	 * @param  array  $order [description]
	 * @return array         [description]
	 */
	public function getByIds (array $ids, array $order = array()) {
		$this->getDb();
		$values = '*';
		$idsArray = array();
		foreach ($ids as $key => $value) {
			$idArray['id_'.$key] = $value;
		}
		$idFieldname = $this->entityPrototype->getFieldIndex();
		$this->db->lastCmd =
			'SELECT '.$values
			.' FROM '.addslashes($this->tableName)
			.' WHERE '.$this->tableName.'.'.$idFieldname.' IN (:'.implode(',:', array_keys($idArray)).')'
		;
		if (!empty($order)) {
			$this->db->lastCmd .= ' ORDER BY '.$this->tableName.'.'.implode(', '.$this->tableName.'.', $order);
		}
		$this->db->lastData = $idArray;
		$sth = $this->db->prepare($this->db->lastCmd);
		$sth->execute($this->db->lastData);
		return $sth->fetchAll( \PDO::FETCH_CLASS, $this->entityClass );
	}

	/**
	 * Delete an Entity by ID
	 * @param  integer $id [description]
	 * @return boolean
	 */
	public function deleteById ($id) {
		return $this->deleteByIds(array($id));
	}

	/**
	 * Delete multiple Entities by Id
	 * @param  array  $ids   [description]
	 * @return array         [description]
	 */
	public function deleteByIds (array $ids) {
		$this->getDb();
		$idsArray = array();
		foreach ($ids as $key => $value) {
			$idArray['id_'.$key] = $value;
		}
		$idFieldname = $this->entityPrototype->getFieldIndex();
		$this->db->lastCmd =
			'DELETE '
			.' FROM '.addslashes($this->tableName)
			.' WHERE '.$this->tableName.'.'.$idFieldname.' IN (:'.implode(',:', array_keys($idArray)).')'
		;
		$this->db->lastData = $idArray;
		$sth = $this->db->prepare($this->db->lastCmd);
		return $sth->execute($this->db->lastData);
	}

	/**
	 * Select multiple Entities
	 * @param  array   $where  [description]
	 * @param  array   $order  [description]
	 * @param  integer $count  [description]
	 * @param  integer $offset [description]
	 * @return array           of Entities
	 */
	public function get (array $where = array(), array $order = array(), $count = NULL, $offset = 0) {
		$this->getDb();
		$whereArray = array();
		foreach ($where as $key => $value) {
			$whereArray[] = $this->tableName.'.'.$key.' = :'.$key;
		}
		$this->db->lastCmd =
			'SELECT '.$values
			.' FROM '.addslashes($this->tableName)
		;
		if (!empty($join)) {
			$this->db->lastCmd .= ' '.$join;
		}
		if (!empty($whereArray)) {
			$this->db->lastCmd .= ' WHERE '.implode(' AND ', $whereArray);
		}
		if (!empty($order)) {
			$this->db->lastCmd .= ' ORDER BY '.$this->tableName.'.'.implode(', '.$this->tableName.'.', $order);
		}
		if (!empty($count)) {
			$this->db->lastCmd .= ' LIMIT '.(int)$offset.','.(int)$count;
		}
		$this->db->lastData = $where;
		$sth = $this->db->prepare($this->db->lastCmd);
		$sth->execute($this->db->lastData);
		return $sth->fetchAll( \PDO::FETCH_CLASS, $this->db->entityClass );
	}

	/**
	 * Update or insert entity into table
	 * @param  Entity $entity [description]
	 * @return string         new ID of row in DB
	 */
	public function store ($entity) {
		if (get_class($entity) != $this->entityClass) {
			throw new \Exception ('Entity has to be of type '.$this->entityClass.' in '.get_class($this));
		}
		$id = $entity->getId();
		$this->getDb();
		if (empty($id)) {
			$this->db->insert(
				$this->tableName,
				$entity->getStorableArray(TRUE)
			);
			$id = $this->db->lastInsertId();
		}
		else {
			$this->db->update(
				$this->tableName,
				$entity->getStorableArray(),
				$entity->getFieldIndex().'='.$this->db->quote($id)
			);
		}
		return $id;
	}

	/**
	 * Use for debugging last SQL query
	 * @return string SQL
	 */
	public function getLastCommand () {
		#var_dump($this->db);
		if (!empty($this->db)) {
			return $this->db->getLastCommand();
		}
		return NULL;
	}
}