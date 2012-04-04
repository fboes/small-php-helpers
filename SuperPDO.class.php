<?php

/**
 * @class SuperPDO
 * Extends functionaliyt of PDO
 *
 * @author      Frank Bo"es <info@3960.org>
 * @copyright   Creative Commons Attribution 3.0 Unported (CC BY 3.0)
 */
class SuperPDO extends PDO 
{
    public lastCmd = '';

    
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
        return self($dsn, $username, $password);
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
}
?>