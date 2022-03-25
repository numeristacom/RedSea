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
 * MariaDB wrapper. This class wraps the PHP functions, integrating error control and
 * reporting through the static debug class and allowing handling errors through the debug class
 * in a consistant way.
 * @package RedSea
 */

namespace RedSea;

use PDO;

class pdodb {

    /**
     * Contains the DSN string
     * @internal 
     */
    protected $DSN = null;
    
    /**
     * Contains the database connection object
     * @internal 
     */
    protected $dbConnection = null;

    /**
     * Opens a connection to MariaDB.
     * @param string $dbtype Database type to connect to: mariadb or sqlite
     * @param string $dbname Name of the data base to open. For an SQLite database, it will
     * be the path and name of the SQLite database file. 
     * @param string $host Hostname or IP address of the database server to connect to. Not required for SQLite database.
     * @param string $username Username credential for the database. Not required for SQLite database.
     * @param string $password Password credential for the database. Not required for SQLite database.
     * @param int $port If not specified, the MariaDB default port 3306 will be used, but can be specified to any other number.
     * @return void
     * In case of error, the method will raise a fatal error to output.
     */
    public function __construct($dbtype, $dbname, $host=null, $username=null, $password=null, $port=3306)  {
        debug::flow();
        
        switch ($dbtype) {
            case "mariadb":
                $this->DSN = "mysql:dbname=$dbname;host=$host;port=$port;charset=utf8";
                break;
            case "sqlite":
                $this->DSN = "sqlite:$dbname";
                break;
            default:
                debug::fatal('Database type not recognised', $dbtype);
        }

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        $this->dbConnection = new \PDO($this->DSN, $username, $password, $options);
        
        if(!is_object($this->dbConnection)) {
            //Something went wrong
            debug::fatal("PDO connection object not returned for specified db", $this->DSN);
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

    public function getDBConnection() {
        return $this->dbConnection;
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

class singleTableRecord {

    /**
     * Contains the database connection object
     * @internal 
     */
    protected $dbConnection = null;
    protected $tableSetup = array();
    protected $whereArgs = array();
    private $isLoaded = false;
    protected $tableName = null;

    /**
     * Set up the object by passing it a valid pdo connection object, either directly created or exported from the pdodb class.
     * @param mixed $pdoConnectionObject 
     * @param string $tableToWork The name of the database table to work on.
     * @return void 
     */
    public function __construct($pdoConnectionObject, $tableToWork) 
    {
        $this->dbConnection = $pdoConnectionObject;
        $this->tableName = str_replace("'", "''", str_replace("''", "'", $tableToWork));

        $ret = $this->dbConnection->query("SHOW FULL COLUMNS FROM " . $this->tableName);
        //The query will return an object if it ran. Anything else is a problem.
        if(!is_object($ret)) {
            debug::err($this->dbConnection->errorInfo(), $this->tableName);
        } else {
            //Lets build the handling array.
            while ($result = $ret->fetch(PDO::FETCH_ASSOC)) {
                $fieldDicDesc = explode('|', $result['Comment']);
                $currentField = $result['Field'];
                // Set the coherency check flag to false if we miss any data in the description.
                if(count($fieldDicDesc) < 3) {
                    $this->coherencyCheck = false;
                    debug::err("Less than 3 fields for $tableToWork.$currentField - Missing description data");
                    return false;
                } else {
                    $enumArray = array();
                    if($fieldDicDesc['1'] == "e") {
                        //We have an enum field to customise.
                        $enumData = explode("&", $fieldDicDesc[2]);
                        foreach($enumData as $enumValue) {
                            $ev = explode('=', $enumValue);
                            $enumArray[$ev[0]] = $ev[1];
                        }
                    }
                    $this->tableSetup[$currentField] = array("value" => null, "dataType" => $fieldDicDesc[0], "dataFormat" => $fieldDicDesc[1], "enumArray" => $enumArray);
                }
            }
        }
    }

    /**
     * Add the where conditions to an internal structure which will be used to generate a where query for reading or writing a table record.
     * @param mixed $field Field in the current table to filter on
     * @param mixed $value Value to use for the clause.
     * @return void 
     */
    public function addWhere($field, $value) {
        $field = $this->forceCleanValue('str', $field);
        if(array_key_exists($field, $this->tableSetup)) {
            $this->whereArgs[$field] = $this->forceCleanValue($this->tableName[$field]["dataType"], $value);
        }
    }

    /**
     * Return a query ready value that depends on the expected data type of the field. 
     * @param mixed $expectedType num for Numerical fields (including boolean data), or str for String or text data.
     * @param mixed $value Value to check
     * @param bool $preQuote If true, any string value will be returned pre-quote delimited for use in a query in addition to being escaped, for example if you
     * have a value abc'def passed as the value, then the method will return this value quoted and escaped as 'abc''def'. If false the string will only be escaped
     * but will not be quoted.
     * @return mixed Processed value
     */
    private function forceCleanValue($expectedType, $value, $preQuote = true) {
        if($expectedType == "num") {
            if(!is_numeric($value)) {
                return null;
            } else {
                return $value;
            }
        } else {
            if($preQuote) {
                $quoteChar = "'";
            } else {
                $quoteChar = null;
            }
            return $quoteChar . str_replace("'", "''", str_replace("''", "'", $value)) . $quoteChar;
        }
    }

    /**
     * read a record from the table
     * @return void 
     */
    public function readRecord() {
        $sql = "SELECT * FROM " . $this->tableName . " WHERE";
        $i = 0;
        foreach($this->whereArgs as $field => $value) {
            if($i == 0) {
                $sql .= " $field = $value";
            } else {
                $sql .= " AND $field = $value";
            }
            $i++;
        }
        $ret = $this->dbConnection->query($sql);
        if(!is_object($ret)) {
            debug::err($this->dbConnection->errorInfo(), $sql);
        } else {
            //Lets build the handling array.
            while ($result = $ret->fetch(PDO::FETCH_ASSOC)) {
               foreach($this->tableSetup as $col => $settings) {
                $this->tableSetup[$col]["value"] = $result[$col];
               }
            }            
        }
    }

    /**
     * Set a new value into the tableSetup array
     * @param string $field Name of the column to update in the databse.
     * @param mixed $value Value to set for that column
     * @return boolean True on success, false on failure.
     */
    public function setValue($field, $value) {
        if(array_key_exists($field, $this->tableSetup)) {
            $this->tableName[$field]["value"] = $this->forceCleanValue($this->tableName[$field]["dataType"], $value);
        } else {
            debug::err("Unknown field", $field);
            return false;
        }
    }

    /**
     * 
     * @return void 
     */
    public function writeRecord() {
        
    }

}


?>