<?php
# namespace fboes\SmallPhpHelpers;
# use \PDO;

/**
 * @class SuperPDO
 * Extends functionality of PDO
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   MIT License (MIT)
 */
class SuperPDO extends PDO
{
	public $lastCmd = '';
	public $lastData = array();

	/**
	 * Open Mysql-PDO
	 *
	 * @param   string  $host   like 'localhost'
	 * @param   string  $db like 'test'
	 * @param   string  $usr    MySQL-Username. Optional, defaults to NULL
	 * @param   string  $pwd    MySQL-Password. Optional, defaults to NULL
	 * @return  SuperPDO
	 */
	public static function openMysql ($host, $db, $usr = NULL, $pwd = NULL)
	{
		$dsn = 'mysql:host='.$host.';dbname='.$db;
		return new self($dsn, $usr, $pwd);
	}

	/**
	 * Open Sqlite-PDO
	 *
	 * @param   string  $file   Absolute or relative filename
	 * @return  SuperPDO
	 */
	public static function openSqlite ($file)
	{
		if (!preg_match('#^/#',$file))
		{
			$file = dirname($_SERVER['SCRIPT_FILENAME']).'/'.$file;
		}
		return new self('sqlite:'.$file);
	}

	/**
	 * Prepare DB for communication in UTF8
	 * @return [type] [description]
	 */
	public function useUtf8 () {
		if ($this->getAttribute(self::ATTR_DRIVER_NAME) === 'mysql') {
			return $this->exec('SET NAMES utf8');
		}
	}

	/**
	 * Quotes an array for inserting / updating operations
	 *
	 * @param   array   $data
	 * @param   string  $separator  Optional, defaults to ','
	 * @param   bool    $withKey    Optional, defaults to TRUE
	 * @return  string  SQL
	 */
	public function quoteArray (array $data, $separator = ',', $withKey = TRUE)
	{
		$set = array();
		foreach ($data as $key => $value)
		{
			$set[] = ($withKey)
				? addslashes($key).'='.$this->quote($value)
				: $this->quote($value)
			;
		}
		return (implode($separator,$set));
	}

	/**
	 * Convert keys of an array to SQL usable for prepared statements
	 * @param  array  $data  FIELDNAME => VALUE
	 * @param  string $table Optional
	 * @return string        TABLE.KEY = :KEY
	 */
	public function buildPreparedArray (array $data, $table = '') {
		$preparedArray = array();
		foreach ($data as $key => $value) {
			$preparedArray[] = (!empty($table) ? $table.'.' : '').$key.' = :'.$key;
		}
		return $preparedArray;
	}

	/**
	 * Insert array into table. Will do proper quoting.
	 *
	 * @param   string  $table  Name of the table
	 * @param   array   $data   associative array with FIELDNAME => FIELDVALUE
	 * @param   string  $options    Like 'DELAYED'. Optional, defaults to NULL
	 * @return  bool
	 */
	public function insert ($table, array $data, $options = NULL)
	{
		$this->lastCmd =
			'INSERT'
			.(!empty($options) ? ' '.addslashes($options) : '')
			.' INTO '.addslashes($table)
			.'('.implode(',',array_keys($data)).')'
			.' VALUES(:'.implode(',:',array_keys($data)).')'
		;
		$this->lastData = $data;
		$sth = $this->prepare($this->lastCmd);
		return $sth->execute($this->lastData);
	}

	/**
	 * Select from table. Will do proper quoting, but for order
	 *
	 * @param   string  $table  Name of the table
	 * @param   string  $where  SQL-statement for WHERE-clause; you may want to use $this->quoteArray
	 * @param   string  $order  Optional
	 * @param   int     $count  Optional
	 * @param   int     $offset Optional
	 * @return  PDOStatement
	 */
	public function select ($table, $where = '1', array $order = array(), $count = NULL, $offset = 0)
	{
		return $this->selectJoin($table, $where, NULL, $order, $count, $offset);
	}

	/**
	 * Select from table. Will do proper quoting, but for order
	 *
	 * @param   string  $table  Name of the table
	 * @param   string  $where  SQL-statement for WHERE-clause; you may want to use $this->quoteArray
	 * @param   string  $join   some unquoted SQL
	 * @param   string  $order  Optional
	 * @param   int     $count  Optional
	 * @param   int     $offset Optional
	 * @param   string  $values Optional, defaults to '*'
	 * @return  PDOStatement
	 */
	public function selectJoin ($table, $where = '1', $join = '', array $order = array(), $count = NULL, $offset = 0, $values = '*')
	{
		$this->lastCmd =
			'SELECT '.$values
			.' FROM '.addslashes($table)
		;
		if (!empty($join)) {
			$this->lastCmd .= ' '.$join;
		}
		if (!empty($where)) {
			$this->lastCmd .= ' WHERE '.$where;
		}
		if (!empty($order)) {
			$this->lastCmd .= ' ORDER BY '.$table.'.'.implode(', '.$table.'.', $order);
		}
		if (!empty($count)) {
			$this->lastCmd .= ' LIMIT '.(int)$offset.','.(int)$count;
		}
		$this->lastData = NULL;
		$sth = $this->prepare($this->lastCmd);
		$sth->execute();
		return $sth->fetchAll(self::FETCH_ASSOC);
	}

	/**
	 * Insert multiple datasets. Will do proper quoting.
	 *
	 * @param   string  $table  Name of the table
	 * @param   array   $multiData   array with associative arrays with
	 *  FIELDNAME => FIELDVALUE
	 * @param   string  $options    Like 'DELAYED'. Optional, defaults to NULL
	 * @return  bool
	 */
	public function multipleInsert ($table, array $multiData, $options = NULL)
	{
		$this->beginTransaction();
		foreach ($multiData as $data)
		{
			$this->insert($table,$data,$options);
		}
		return $this->commit();
	}

	/**
	 * Replace array into table. Will do proper quoting.
	 *
	 * @param   string  $table  Name of the table
	 * @param   array   $data   associative array with FIELDNAME => FIELDVALUE
	 * @param   string  $options    Like 'DELAYED'. Optional, defaults to NULL
	 * @return  bool
	 */
	public function replace ($table, array $data, $options = NULL)
	{
		$this->lastCmd =
			'REPLACE'
			.(!empty($options) ? ' '.addslashes($options) : '')
			.' INTO '.addslashes($table)
			.'('.implode(',',array_keys($data)).')'
			.' VALUES(:'.implode(',:',array_keys($data)).')'
		;
		$this->lastData = $data;
		$sth = $this->prepare($this->lastCmd);
		return $sth->execute($this->lastData);
	}

	/**
	 * Update array into table. Will do proper quoting.
	 *
	 * @param   string  $table  Name of the table
	 * @param   array   $data   associative array with FIELDNAME => FIELDVALUE
	 * @param   string  $where  SQL-statement for WHERE-clause; you may want to use $this->quoteArray
	 * @param   string  $options    Like 'DELAYED'. Optional, defaults to NULL
	 * @return  bool
	 */
	public function update ($table, array $data, $where, $options = NULL)
	{
		$this->lastCmd =
			'UPDATE'
			.(!empty($options) ? ' '.addslashes($options) : '')
			.' '.addslashes($table)
			.' SET '.implode(',',$this->buildPreparedArray($data))
			.' WHERE '.$where
		;
		$this->lastData = $data;
		$sth = $this->prepare($this->lastCmd);
		return $sth->execute($this->lastData);
	}

	/**
	 * Delete from table. Will do proper quoting
	 *
	 * @param   string  $table  Name of the table
	 * @param   string  $where  SQL-statement for WHERE-clause; you may want to use $this->quoteArray
	 * @return  PDOStatement
	 */
	public function delete ($table, $where)
	{
		$this->lastCmd =
			'DELETE'
			.' FROM '.addslashes($table)
			.' WHERE '.$where;
		;
		$this->lastData = $where;
		$sth = $this->prepare($this->lastCmd);
		return $sth->execute();
	}

	/**
	 * Returns any given string as proper date / datetime representation to
	 * be inserted into db. Will change time format according to DB type.
	 *
	 * @param   string  $someDate   date in unknown format
	 * @param   bool    $asDatetime Return string is date or datetime. Optional,
	 *  defaults to TRUE, meaning this will be datetime
	 * @return  string
	 */
	public function returnDate ($someDate, $asDatetime = TRUE)
	{
		$ts = strtotime($someDate);
		switch ($this->getAttribute(self::ATTR_DRIVER_NAME))
		{
			case 'mysql':
				return date('Y-m-d'.($asDatetime ? ' H:i:s' : ''), $ts);
				break;
			default:
				return $ts;
				break;
		}
	}

	/**
	 * Debug last command by showing prepared statements as excecutable statements
	 * @return string SQL
	 */
	public function getLastCommand () {
		$cmd = $this->lastCmd;
		foreach ($this->lastData as $key => $value) {
			$cmd = str_replace(':'.$key, $this->quote($value), $cmd);
		}
		return $cmd;
	}
}