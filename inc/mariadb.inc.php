<?php
/**
 * This file defines some basic DB libraries.
 * @author Daniel Page <daniel@danielpage.com>
 * @copyright Copyright (c) 2021, Daniel Page
 * @license Licensed under the EUPL v1.2 - https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * 
 * The MySQL classes don't add much visible differences to stock MySQL PDO php functions, but they do add
 * debug services to the objects that can be activated on demand along with error notification in line with
 * the other classes in RedSea.
 */

/** Usage example
* $db = new mariadb(localhost, 'mydb', 'user', 'password');
* $rs = new recordset($db->query('select count(*) as cnt from myTable'));
* while ($ret = $rs->fetchArray()) {
*    echo($ret['cnt']);
* }
*/

/**
 * MariaDB wrapper. This class wraps the PHP  functions, integrating error control and
 * reporting through the static debug class and allowing handling errors through the debug class
 * in a consistant way.
 * @package RedSea
 */

namespace RedSea;

use PDO;

class mariadb {

    /**
     * Contains the DSN string
     * @internal 
     */
    protected $mariaDSN = null;
    
    /**
     * Contains the database connection object
     * @internal 
     */
    protected $dbConnection = null;

    /**
     * Opens a connection to MariaDB.
     * @param string $host Hostname or IP address of the database server to connect to
     * @param string $username Username credential for the database
     * @param string $password Password credential for the database 
     * @param string $dbname Name of the data base to open
     * @param int $port If not specified, the MariaDB default port 3306 will be used, but can be specified to any other number.
     * @return void
     * In case of error, the method will raise a fatal error to output.
     */
    public function __construct($host, $username, $password, $dbname, $port=3306)  {
        debug::flow();
        $this->mariaDSN = "mysql:dbname=$dbname;host=$host;port=$port;charset=utf8";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->dbConnection = new \PDO($this->mariaDSN, $username, $password, $options);
        
        if(!is_object($this->dbConnection)) {
            //Something went wrong
            debug::fatal("SQLite connection object not returned for specified db", $this->mariaDSN);
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
     * On success, the system will set the parameters affected Records and lastInsertID
     */
    public function execute($sql) {
        debug::flow($sql);
        if(empty($sql)) {
            debug::err("No query submitted");
            return false;
        } else {
            $ret = $this->dbConnection->exec($sql);
            if(!$ret) {
                debug::err($sql, $this->dbConnection->errorInfo());
                return false;
            } else {
                $this->affectedRecords = $ret;
                $this->lastInsertID = $this->dbConnection->lastInsertId();
                return true;
            }
        }
    }

    /**
     * Executes a query on the database that returns a result set.
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
                debug::err($sql, $this->dbConnection->errorInfo(), $sql);
           } else {
               //Here we go. Return the result object.
               return $ret;
           }
        }
    }
}

/**
 * wrapper for result sets, including consistant error reporting through the debug class
 * */
class recordset {    

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
    public function __construct($dbResult=null) {
        debug::flow();
        if(!is_object($dbResult)) {
            debug::err("No result object passed", $dbResult);
            return false;
        } else {
            $this->result = $dbResult;
            return true;
        }
    }

    /**
     * Controls how the next row of the result set be returned to the caller
     * @param mixed|null $recordType Default MYSQLI_ASSOC.
     * This value must be one of either PDO::FETCH_ASSOC, PDO::FETCH_NUM, or PDO::FETCH_BOTH.
     * - SQLITE3_ASSOC: returns an array indexed by column name as returned in the corresponding result set
     * - SQLITE3_NUM: returns an array indexed by column number as returned in the corresponding result set, starting at column 0
     * - SQLITE3_BOTH: returns an array indexed by both column name and number as returned in the corresponding result set, starting at column 0
     * @return array Contents of the current record or false if there is nothing to return.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag property will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    public function fetchArray($recordType=PDO::FETCH_ASSOC) {
        $ret = $this->result->fetch($recordType);
        if($ret === false) {
            $this->end = true;
            debug::flow("End of Recordset");
            return false;
        } else {
            return $ret;
        }
    }
}

?>