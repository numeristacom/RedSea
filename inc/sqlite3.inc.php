<?php
/**
 * This file defines some basic DB libraries.
 * @author Daniel Page <daniel@danielpage.com>
 * @copyright Copyright (c) 2021, Daniel Page
 * @license Licensed under the EUPL v1.2 - https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * 
 * I recommend using PHP Data Objects (PDO), though the methods below can save some
 * time running general queries and getting responses against MySQL/MariaDB and SQLite databases.
 * 
 * The SQLite classes don't add much visible differences to stock SQLite3 php functions, but they do add
 * debug services to the objects that can be activated on demand along with error notification in line with
 * the other classes in RedSea.
 */

namespace RedSea;

use SQLite3;

/**
 * SQLite wrapper. This class wraps the PHP SQLIte 3 functions, integrating error control and
 * reporting through the static debug class and allowing handling errors through the debug class
 * in a consistant way.
 * @package RedSea
 */
class SQLite {

    /**
     * Contains the connection object to the database once the class is instanciated.
     * @internal 
     */
    protected $dbConnection = null;
    
    /**
     * Number of affected records by an execute query.
     */
    public $affectedRecords = null;
    
    /**
     * If a query inserts records into a table with an automatically updated primary key column,
     * this property will contain the last ID created from a query.
     */
    public $lastInsertID = null;
    
    /**
     * Opens a connection to an SQLite DB file, or creates it if non-existant.
     * @param string $pathToSqliteDb Path to the database file
     * @param int $openFlags Optional: Contants to open the database in a specific way:
     * - SQLITE3_OPEN_READONLY: Open the database in read only mode.
     * - SQLITE3_OPEN_READWRITE: Open the database in read / write mode.
     * - SQLITE3_OPEN_CREATE: Create the database if it does not exist.
     * If this value is null/not set, the default SQLite settings (SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE) will be used
     * @param $password Optional: If the database is password protected, you can specify the password to use here.
     * @return bool TRUE on success, FALSE on failure.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    public function __construct($pathToSqliteDb)
    {
        debug::flow();
        $this->dbConnection = new \SQLite3($pathToSqliteDb);
        if(!is_object($this->dbConnection)) {
            //Something went wrong
            debug::err("SQLite connection object not returned for specified db", $pathToSqliteDb);
        }
    }

    /**
     * Executes a single query on the database that does not return a result object.
     * @param string $sqlQry SQL query to execute.
     * @return bool TRUE on success, FALSE on failure.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    public function execute($sql) {
        debug::flow($sql);
        if(empty($sql)) {
            debug::err("No query submitted");
            return false;
        } else {
            $ret = $this->dbConnection->exec($sql);
            if(!$ret) {
                debug::err($this->dbConnection->lastErrorMsg(), $sql);
                return false;
            } else {
                $this->affectedRecords = $this->dbConnection->changes();
                $this->lastInsertID = $this->dbConnection->lastInsertRowID();
                return true;
            }
        }
    }

    /**
     * Executes a query on the database that does return a result set.
     * @param string $sql 
     * @return false|object On success, an SQLite result set is returned, otherwise FALSE. 
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    public function query($sql) {
        debug::flow();
        if(empty($sql)) {
            debug::err("No query submitted");
            return false;
        } else {
           $ret = $this->dbConnection->query($sql);
           //The query will return an object if it ran. Anything else is a problem.
           if(!is_object($ret)) {
            debug::err($this->dbConnection->lastErrorMsg(), $sql);
           } else {
               //Here we go. Return the result object.
               return $ret;
           }
        }
    }
}

/**
 * SQLite wrapper for result sets, including consistant error reporting through the debug class
 * */
class recordset {    

    public $rows = null; 
    public $cols = null;
    public $affectedRecords = null;
    public $rs = null;
    /**
     * If TRUE, the recordset has reached the end. 
     */
    public $end = false;

    private $result = null;
    
    /**
     * Set a local object for procesing by passing an SQLite result set to the class.
     * @param object $sqliteObject SQLIte result object
     * @return mixed|bool Ifsuccessful, the method will return TRUE, otherwise FALSE.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag property will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    public function __construct($sqliteObject=null) {
        debug::flow();
        if(!is_object($sqliteObject)) {
            debug::err("No result object passed", $sqliteObject);
            return false;
        } else {
            $rows = $sqliteObject->numColumns();
            $this->result = $sqliteObject;
            return true;
        }
    }

    /**
     * Controls how the next row of the result set be returned to the caller
     * @param mixed|null $recordType Default SQLITE3_ASSOC.
     * This value must be one of either SQLITE3_ASSOC, SQLITE3_NUM, or SQLITE3_BOTH.
     * - SQLITE3_ASSOC: returns an array indexed by column name as returned in the corresponding result set
     * - SQLITE3_NUM: returns an array indexed by column number as returned in the corresponding result set, starting at column 0
     * - SQLITE3_BOTH: returns an array indexed by both column name and number as returned in the corresponding result set, starting at column 0
     * @return array Contents of the current record or false if there is nothing to return.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag property will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    public function fetchArray($recordType=SQLITE3_ASSOC) {
        debug::flow();
        $ret = $this->result->fetchArray($recordType);
        if($ret === false) {
            $this->end = true;
            debug::err("End of Recordset");
            return false;
        } else {
            return $ret;
        }
    }
}

?>

