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
     * Contains the last inserted id
     */
    public $insertId = null; 

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
                $this->insertId = $this->dbConnection->lastInsertId();
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

    /**
     * Takes a table, and describes it's content.
     * Reading returns an array containing all the fields.
     * Updating takes 2 associative arrays, one for the data and one for the primary keys
     * Inserting takes one array and an optional primary key array (take care if it's auto increment or not)
     * The class will check that data added for insert or update matches the table structure to avoid sql injection attacks.
     */
class mariaDbTableRecord {


    protected $tableName;   //Name of the table that the class will work on
    protected $tableStructure = array(); //Associative array containing the table structure. Each key contains 2 elements: they table and the expected data type)
    protected $tableKeys = array();    //Associative array containing the primary key
    protected $tableRecord = array();  //Associative array containing the field names and the corresponding values when a record is read or used for writing
    protected $isLoaded = false;    //Flag indicating if a the class has successfully initialised
    protected $loadedRecoord = false;   //Flag indicating if a specific record has been loaded for changes

    /**
     * Set up the object by passing it a valid pdo connection object, either directly created or exported from the pdodb class.
     * @param mixed $pdoConnectionObject 
     * @param string $tableToWork The name of the database table to work on.
     * @return void 
     */
    public function __construct($pdoConnectionObject, $tableToWork) {
        debug::flow();
        $this->dbConnection = $pdoConnectionObject;
        $this->tableName = str_replace("'", "''", str_replace("''", "'", $tableToWork));
        debug::flow('Describing ' . $this->tableName);
        $ret = $this->dbConnection->query("DESCRIBE " . $this->tableName);
        //The query will return an object if it ran. Anything else is a problem.
        if(!is_object($ret)) {
            debug::err($this->dbConnection->errorInfo(), $this->tableName);
        } else {
            $this->tableName = $tableToWork;

            //Lets build the handling array.
            while ($result = $ret->fetch(PDO::FETCH_ASSOC)) {
                $fieldName = $result['Field'];
                $fieldType = $result['Type'];
                $fieldNull = $result['Null'];
                $fieldKey = $result['Key'];
                $fieldExtra = $result['Extra'];
                $fieldPrimitive = $this->returnPrimitiveDataType($fieldType);
                $this->tableStructure[$fieldName] = array('field' => $fieldName, 'type' => $fieldType, 'isNull' => $fieldNull, 'key' => $fieldKey, 'extra' => $fieldExtra, 'primitive' => $fieldPrimitive);
                $this->tableRecord[$fieldName] = array('field' => $fieldName, 'value' => null);
                if($fieldKey == 'PRI') {
                    //We have a primary key
                    $this->tableKeys[$fieldName] = array('field' => $fieldName, 'type' => $fieldType, 'value' => null);
                }                
            }
            $this->isLoaded = true;
        }
        return true;
    }

    /**
     * Return the field's primitive data type (string or number) for a given field's set data type.
     * These data types are taken from https://mariadb.com/kb/en/data-types/ valid as of 10.3
     * Known numerical values will be returned as numerical, all others will be returned as text.
     * Dates will be considered text types.
     * @param mixed $fieldDataType Database data type to check
     * @return int 1 for numerical type, 0 for everything else (text, dates, spatial)
     */
    private function returnPrimitiveDataType($fieldDataType) {
        debug::flow();
        if(stripos($fieldDataType, 'INT') !== FALSE) {
            return 1;
        } elseif(stripos($fieldDataType, 'DEC') !== FALSE) {
            return 1;
        } elseif(stripos($fieldDataType, 'BOOL') !== FALSE) {
            return 1;
        } elseif(stripos($fieldDataType, 'NUMERIC') !== FALSE) {
            return 1;
        } elseif(stripos($fieldDataType, 'FIXED') !== FALSE) {
            return 1;
        } elseif(stripos($fieldDataType, 'NUMBER') !== FALSE) {
            return 1;
        } elseif(stripos($fieldDataType, 'FLOAT') !== FALSE) {
            return 1;
        } elseif(stripos($fieldDataType, 'DOUBLE') !== FALSE) {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * Check the value against the corresponding field's type and return a quoted or unquoted value, depending on type, ready for use in a prepared query.
     * @param string $field 
     * @param mixed $value 
     * @return mixed Quoted value for a string, unquoted for a numeric
     */
    public function prepareValue($field, $value) {
        debug::flow();
        if($this->tableStructure[$field]['primitive'] == 1) {
            if(is_numeric($value)) {
                return $value;
            } else {
                return null;
            }
        } else {
            return "'" . str_replace("'", "''", str_replace("''", "'", $value)) . "'";
        }
    }

    /**
     * Select a record from the table by providing an array of fields and values to select
     * You will r
     * @param array $selectByKeyArray Associative array of fieldnames and values to use in the select query to load one record. The format of the array
     * is array('field' => 'value') - and can contain as many fields as required to construct the select query from a single table. They don't have to be 
     * keys but any valid field name in the table, and a valid value corresponding to the data type. Values will be automatically escaped.
     * @return boolean TRUE if the value was loaded, FALSE otherwise. 
     */
    public function getOneRecordByKeys($selectByKeyArray) {
        debug::flow();
        // Example getRecordByKeys(array('id' => 1, 'something' => 'else'));
        $sql = "SELECT * FROM " . $this->tableName . " WHERE ";
        $i = 0;
        foreach($selectByKeyArray as $field => $value) {
            if($i == 0) {
                $sql .= $field  . ' = ' . $this->prepareValue($field, $value);
            } else {
                $sql .= ' AND ' . $field  . ' = ' .  $this->prepareValue($field, $value);
            }
            $i++;
        }
        $sql .= ' LIMIT 1';
        debug::flow($sql);
        $ret = $this->dbConnection->query($sql);
        if(!is_object($ret)) {
            debug::err($this->dbConnection->errorInfo(), $this->tableName);
        } else {
            //Did we get a result? Maybe we need to check this in case we didn't match anything!
            while ($result = $ret->fetch(PDO::FETCH_ASSOC)) {
                foreach($this->tableRecord as $field => $params) {
                    $this->tableRecord[$field]['value'] = $result[$field];
                    //Save the PK
                    if(isset($this->tableKeys[$field])) {
                        $this->tableKeys[$field]['value'] = $result[$field];
                    }
                }
            }
        }
        $this->loadedRecoord = true;
        return true;
    }

    /**
     * Get a value from a field from the loaded table record
     * @param mixed $fieldName Name of the field from the loaded record
     * @return mixed Value from the field, or === false if no record is set.
     */
    public function getField($fieldName) {
        debug::flow();
        if($this->isLoaded) {
            if(array_key_exists($fieldName, $this->tableRecord)) {
                return $this->tableRecord[$fieldName]['value'];
            } else {
                debug::err('Field ' . $fieldName . ' does not exist. Cannot read value');
                return false;
            }
        } else {
            debug::err('No record loaded');
            return false;
        }
    }

    /**
     * Set a new value in a field in the loaded table record. That value will be escaped by the method.
     * @param mixed $fieldName Field to insert a value into
     * @param mixed $value Value to insert
     * @return bool True on success, False on error
     */
    public function setField($fieldName, $value) {
        debug::flow();
        if($this->isLoaded) {
            if(array_key_exists($fieldName, $this->tableRecord)) {
                $this->tableRecord[$fieldName]['value'] = $value;
                return true;
            } else {
                debug::err('Field ' . $fieldName . ' does not exist. Cannot set value');
                return false;
            }
        } else {
            debug::err('No record loaded');
            return false;
        }
    }

    /**
     * Write the record back to the to database as an update. It will used the stored keys to identify the previous record to update,
     * even if the new data keys are different.
     * @return void 
     */
    public function updateRecord($forcePrimaryKeyUpdate=false) {
        debug::flow();
        if($this->isLoaded) {
            $sql = 'UPDATE ' . $this->tableName . ' SET ';
            
            $i = count($this->tableRecord);
            $j = 1;
            foreach($this->tableRecord as $field => $params) {
                $ignoreField = false;

                //Do we need to ignore the primary key field?
                if(isset($this->tableKeys[$field])) {
                    if($forcePrimaryKeyUpdate) {
                        $ignoreField = false;
                    } else {
                        $ignoreField = true;
                    }
                }

                if(!$ignoreField) {
                    $sql .= $field . " = " . $this->prepareValue($field, $params['value']); 
                    if($j < $i) {
                        $sql .= ', ';
                    }
                }
                
                $j++;
            }

            $sql .= ' WHERE ';

            foreach($this->tableKeys as $key => $value) {
                //There should only be one primary key... but it's got a name, so use a foreach to pull that identifiying key name
                $primaryKey = $this->prepareValue($key, $value['value']); 
                $sql .= $key . " = " . $primaryKey;
            }
            debug::flow($sql);
            $this->dbConnection->execute($sql);
            if($this->dbConnection->affectedRecords != 1) {
                debug::err('Number of affected records not equal to 1: ' . $sql);
                return false;
            } else { 
                return true;
            }
        } else {
            debug::err('Cannot update a record that has not been loaded first');
            return false;
        }
    }

    /**
     * Write a new record into the database as per the values set in the table record and taking into account any auto-increment keys that must be ignored as
     * they are directly managed by the database. The table record will then be re-read into memory.
     * @param mixed $autoIncrementKeys Array of fields that are auto increment. Even if these fields are set in the table record explicitly they will be ignored if they
     * are noted in this argument, as this allows the database to manage the autoincrement field instead. If no array of auto increment keys is set, then all values
     * in the table record will be used to make the insert.
     * @return bool True on success, False on failure
     */
    public function writeNewRecord() {
        debug::flow();
        
        $sql = 'INSERT INTO ' . $this->tableName . ' VALUES (';
            
        $i = count($this->tableRecord);
        $j = 1;

        foreach($this->tableRecord as $field => $params) {
            if(isset($this->tableKeys[$field])) {
                //We have auto increment fields to "ignore". Set the record values to the string 'null' before building the query.
                $sql .= 'null'; 
            } else {
                $sql .= $this->prepareValue($field, $params['value']); 
            }

            if($j < $i) {
                $sql .= ', ';
            }
            $j++;
        }
        $sql .= ")";
        debug::flow($sql);
        $this->dbConnection->execute($sql);

        if(is_null($this->dbConnection->insertId)) {
            debug::err('Null insert id returned ' . $sql);
            return false;
        } else {
            return $this->dbConnection->insertId;
        }
    }
}


?>