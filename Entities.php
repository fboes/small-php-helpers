<?php
namespace fboes\SmallPhpHelpers;

#use fboes\SmallPhpHelpers\SuperPDO;

/**
 * @class Entities
 * Manipulate a (SQL) table full of entities
 * Extend this class
 */
class Entities
{
    protected $dbDsn;
    protected $dbUser;
    protected $dbPassword;
    /**
     * Set this string to match the class name of an instance of Entities.
     * Entities has to match the row structure of a single row.
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
     * @throws \Exception
     */
    public function __construct($dbDsn, $dbUser, $dbPassword)
    {
        $this->dbDsn       = $dbDsn;
        $this->dbUser      = $dbUser;
        $this->dbPassword  = $dbPassword;
        if (empty($this->entityClass)) {
            throw new \Exception ('No entityClass set in '.get_class($this));
        } elseif (!is_subclass_of($this->entityClass, 'fboes\SmallPhpHelpers\Entity')) {
            throw new \Exception ('entityClass has to be a sublass of Entity in '.get_class($this));
        }
        $this->entityPrototype = new $this->entityClass;
    }

    /**
     * Get reusable PDO
     * @return SuperPDO [description]
     */
    protected function getDb()
    {
        if (empty($this->db)) {
            $this->db = new SuperPDO($this->dbDsn, $this->dbUser, $this->dbPassword);
            $this->db->useUtf8();
            $this->db->setAttribute(SuperPDO::ATTR_DEFAULT_FETCH_MODE, SuperPDO::FETCH_CLASS);
        }
        return $this->db;
    }

    // --------------------------------------------------
    // CREATE / UPDATE
    // --------------------------------------------------

    /**
     * Update or insert entity into table
     * @param  Entity $entity as reference; after storing your Entity may be altered (e.g. new ID, dates changed, etc.)
     * @return string         new ID of row in DB
     */
    public function store(&$entity)
    {
        $id = $entity->getId();
        if (empty($id)) {
            $id = $this->insert($entity);
        }
        else {
            $this->update($entity, $id);
        }
        return $id;
    }

    /**
     * Insert entity into DB
     * @param  Entity  $entity [description]
     * @return integer         ID of new row, or '-1' if operation failed
     * @throws \Exception
     */
    public function insert(&$entity)
    {
        if (get_class($entity) != $this->entityClass) {
            throw new \Exception ('Entity has to be of type '.$this->entityClass.' in '.get_class($this));
        }
        $this->getDb();
        if ($this->db->insert(
            $entity->getTableName(),
            $entity->getStorableArray(true)
        )) {
            $entity->{$entity->getFieldPrimaryIndex()} = $this->db->lastInsertId();
            return $entity->{$entity->getFieldPrimaryIndex()};
        }
        else {
            return -1;
        }
    }

    /**
     * Update entity in DB
     * @param  Entity  $entity [description]
     * @param  Mixed   $id     [description]
     * @return Boolean         [description]
     * @throws \Exception
     */
    public function update(&$entity, $id)
    {
        if (get_class($entity) != $this->entityClass) {
            throw new \Exception ('Entity has to be of type '.$this->entityClass.' in '.get_class($this));
        }
        $this->getDb();
        return $this->db->update(
            $entity->getTableName(),
            $entity->getStorableArray(),
            $entity->getFieldPrimaryIndex().'='.$this->db->quote($id)
        );
    }

    // --------------------------------------------------
    // READ
    // --------------------------------------------------

    /**
     * Select multiple Entities
     * @param  array   $whereArray  [description]
     * @param  array   $order       [description]
     * @param  integer $count       [description]
     * @param  integer $offset      [description]
     * @return array                of Entities
     */
    public function get(array $whereArray = array(), array $order = array(), $count = null, $offset = 0)
    {
        $this->getDb();
        $values = $this->entityPrototype->getExpressionSelect();
        $join   = $this->entityPrototype->getStatementJoin();
        $this->db->lastCmd =
            'SELECT '.$values
            .' FROM '.addslashes($this->entityPrototype->getTableName())
        ;
        if (!empty($join)) {
            $this->db->lastCmd .= ' '.$join;
        }
        if (!empty($whereArray)) {
            $this->db->lastCmd .= ' WHERE '.implode(' AND ', $this->db->buildPreparedArray($whereArray, $this->entityPrototype->getTableName()));
        }
        if (!empty($order)) {
            $this->db->lastCmd .= ' ORDER BY '.$this->entityPrototype->getTableName().'.'.implode(', '.$this->entityPrototype->getTableName().'.', $order);
        }
        if (!empty($count)) {
            $this->db->lastCmd .= ' LIMIT '.(int)$offset.','.(int)$count;
        }
        $this->db->lastData = $whereArray;
        $sth = $this->db->prepare($this->db->lastCmd);
        $sth->execute($this->db->lastData);
        $results = $sth->fetchAll( SuperPDO::FETCH_CLASS, $this->entityClass );
        foreach ($results as &$r) {
            $r->postFetch();
        }
        return $results;
    }

    /**
     * After doing a select this will give you the number of total rows (without LIMIT) affected by this query
     * @return integer numer of rows or -1 if no appropiate command was found
     */
    public function getTotalCountForLastGet()
    {
        $newCmd = $this->db->lastCmd;
        if (strpos($newCmd, 'SELECT') !== false) {
            if (strpos($newCmd, ' SQL_CALC_FOUND_ROWS ') !== false) {
                $this->db->lastCmd  = 'SELECT FOUND_ROWS()';
                $this->db->lastData = array();
            }
            else {
                $newCmd = preg_replace('# LIMIT \d.*$#is', '', $newCmd);
                $newCmd = preg_replace('#^(SELECT )(.+)( FROM)#is', '$1COUNT(*)$3', $newCmd);

                $this->db->lastCmd  = $newCmd;

            }
            $sth = $this->db->prepare($this->db->lastCmd);
            $sth->execute($this->db->lastData);
            $results = $sth->fetchAll( SuperPDO::FETCH_BOTH );

            return !empty($results[0][0]) ? (int)$results[0][0] : -1;
        }
        return -1;
    }

    /**
     * Get a single Entities by ID
     * @param  Mixed  $id [description]
     * @return Entity      or null
     */
    public function getById($id)
    {
        $results = $this->getByIds(array($id));
        return (!empty($results)) ? $results[0] : null;
    }

    /**
     * Get multiple Entities by ID
     * @param  array  $ids   [description]
     * @param  array  $order [description]
     * @return array         [description]
     */
    public function getByIds(array $ids, array $order = array())
    {
        $this->getDb();
        $values = $this->entityPrototype->getExpressionSelect();
        $join   = $this->entityPrototype->getStatementJoin();
        $idsArray = array();
        foreach ($ids as $key => $value) {
            $idArray['id_'.$key] = $value;
        }
        $idFieldname = $this->entityPrototype->getFieldPrimaryIndex();
        $this->db->lastCmd =
            'SELECT '.$values
            .' FROM '.addslashes($this->entityPrototype->getTableName())
            .' WHERE '.$this->entityPrototype->getTableName().'.'.$idFieldname.' IN (:'.implode(',:', array_keys($idArray)).')'
        ;
        if (!empty($order)) {
            $this->db->lastCmd .= ' ORDER BY '.$this->entityPrototype->getTableName().'.'.implode(', '.$this->entityPrototype->getTableName().'.', $order);
        }
        $this->db->lastData = $idArray;
        $sth = $this->db->prepare($this->db->lastCmd);
        $sth->execute($this->db->lastData);
        $results = $sth->fetchAll( SuperPDO::FETCH_CLASS, $this->entityClass );
        foreach ($results as &$r) {
            $r->postFetch();
        }
        return $results;
    }

    // --------------------------------------------------
    // DELETE
    // --------------------------------------------------

    /**
     * Delete Entities by condition(s)
     * @param  array  $whereArray FIELDNAME => condition
     * @return boolean            [description]
     */
    public function delete(array $whereArray)
    {
        $this->getDb();
        $this->db->lastCmd =
            'DELETE'
            .' FROM '.addslashes($this->entityPrototype->getTableName())
        ;
        if (!empty($whereArray)) {
            $this->db->lastCmd .= ' WHERE '.implode(' AND ', $this->db->buildPreparedArray($whereArray, $table));
        }
        $this->db->lastData = $whereArray;
        $sth = $this->db->prepare($this->db->lastCmd);
        return $sth->execute($this->db->lastData);
    }

    /**
     * Delete an Entity by ID
     * @param  Mixed $id [description]
     * @return boolean
     */
    public function deleteById($id)
    {
        return $this->deleteByIds(array($id));
    }

    /**
     * Delete multiple Entities by Id
     * @param  array  $ids   [description]
     * @return array         [description]
     */
    public function deleteByIds(array $ids)
    {
        $this->getDb();
        $idsArray = array();
        foreach ($ids as $key => $value) {
            $idArray['id_'.$key] = $value;
        }
        $idFieldname = $this->entityPrototype->getFieldPrimaryIndex();
        $this->db->lastCmd =
            'DELETE'
            .' FROM '.addslashes($this->entityPrototype->getTableName())
            .' WHERE '.$this->entityPrototype->getTableName().'.'.$idFieldname.' IN (:'.implode(',:', array_keys($idArray)).')'
        ;
        $this->db->lastData = $idArray;
        $sth = $this->db->prepare($this->db->lastCmd);
        return $sth->execute($this->db->lastData);
    }

    // --------------------------------------------------
    // OTHER STUFF
    // --------------------------------------------------

    /**
     * Use for debugging last SQL query
     * @return string SQL
     */
    public function getLastCommand($extended = false)
    {
        if (!empty($this->db)) {
            return $extended
                ? print_r($this->db, 1)
                : $this->db->getLastCommand()
            ;
        }
        return null;
    }

    /**
     * Get field name of Entity in which the primary index is stored
     * @return string Fieldname
     */
    public function getFieldPrimaryIndex()
    {
        return $this->entityPrototype->getFieldPrimaryIndex();
    }
}
