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
            PDO::ATTR_EMULATE_PREPARES   => FALSE,
            PDO::MYSQL_ATTR_FOUND_ROWS   => TRUE,
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
     * Runs a query on the database that returns a result set.
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

class singleRecordCommon {
    protected $dbCnx = null;
    protected $tableName = null;
    protected $tableStructure = array();      // array: field name => array(primitive type, null, key, auto increment)
    protected $whereFields = array();         // fields used to hold fields to generate a "where" condition for a query.
    protected $pkField = null;                // If the table contains a primary key, we will store it, as this allows us to run an update only on the unique PK field as a key rather than relying on WHERE conditions.
    protected $recordIsLoaded = false;        // Set flag to true when a record is correctly loaded
    
    public function __construct($cnx, $tableName) {
        $this->dbCnx = $cnx;
        $this->tableName = $tableName;

        if($this->describeTable($tableName) !== true) {     //Crash and burn.
            debug::fatal("Could not load table structure for table", $tableName);
        }
    }

    protected function describeTable($table) {
        $table = str::sqlString($table);
        $sql = "show columns from " . $table;
        $ret = $this->dbCnx->query($sql);

        if(!is_object($ret)) {
            debug::fatal($this->dbCnx->errorInfo(), $sql);
            return false;
       } else {
           //Here we go. Return the result object.
            $result = $this->result->fetch(PDO::FETCH_ASSOC);
            if($result === false) {
                debug::flow("End of Recordset");
                return true;
            } else {

                //Store the PK field if we find it. It will make our life easier later!
                if($result['Key'] == 'PRI') {
                    $this->pkField = $result['Field'];
                }

                $this->tableStructure[$result['Field']] = array(
                    'primitive' => $this->returnPrimitiveDataType($result['Type']), //Primitive data type to counter sql injection. isString or isNumber
                    'nullAllowed' => $result['Null'],   //Is null value allowed?
                    'key' => $result['Key'],            //Is there a key or index here? (PRI = primary key. If auto increment, ignore this value in insert / update unless forced.)
                    'ai' => $this->isAutoIncrement($result['Extra']),   //Is this an auto increment field?
                    'fieldValue' => null                //Store the field value here when a value is loaded, or store values for an insert.
                );
            }
       }
    }
    
    /**
     * Return the field's primitive data type (string or number) for a given field's set data type.
     * These data types are taken from https://mariadb.com/kb/en/data-types/ valid as of 10.3
     * Known numerical values will be returned as numerical, all others will be returned as text.
     * Dates will be considered text types.
     * @param mixed $fieldDataType Database data type to check
     * @return int 1 for numerical type, 0 for everything else (text, dates, spatial)
     */
    private function returnPrimitiveDataType($typeRecord) {
        debug::flow();
        if(stripos($typeRecord, 'int') !== FALSE) {
            return 'NUM';
        } elseif(stripos($typeRecord, 'decimal') !== FALSE) {
            return 'NUM';
        } else {
            return 'STR';
        }
    }

    /**
     * Check if the Extra column contains an auto_increment modifier
     * @param mixed $extraData Value from the Extra column from a show columns from table statement
     * @return bool True if the field contains an auto_increment modifier, otherwise False.
     */
    private function isAutoIncrement($extraData) {
        if(stripos($extraData, 'auto_increment') === TRUE) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Get the value from a loaded record if the field exists.
     * Note 1) MySQL/MariaDB could have case sensitive field names, so you MUST match the case of the loaded field names or the method will raise a fatal error.
     * Note 2) This method will return the data as it is stored in the database, unescaped.
     * @param string $fieldName Case sensitive name of the loaded field containing the value you want to return
     */
    public function getField($fieldName) {
        if(array_key_exists($fieldName, $this->tableStructure)) {
            return $this->tableStructure[$fieldName]['fieldValue'];
        } else {
            debug::fatal('Requested field to read does not exist in the table or does not match case sensitivity', $fieldName);
        }
    }

    /**
     * Escape and quote a value for a specific field according to it's type
     * @param mixed $field Name of the field to check
     * @param mixed $value Value of the field that may need escaping
     * @return mixed If the expected type is numerical, then the value will be returned as is. If it is a string, it will be quoted and escaped.
     */
    protected function escapeQuoteValueByType($field, $value) {
        if($this->tableStructure[$field]['primitive'] == 'NUM') {
            if(!is_numeric($value)) {
                debug::fatal('Non-numeric data supplied. Numeric expected', array($field, $value));
            } else {
                return $value;
            }
        } else {
            return "'" . str::sqlString($value) . "'";
        }
    }
}

/**
 * @package RedSea
 * Loads a known record from the database into the tableStructure array for reading and updating.
 */
class readUpdateSingleRecord extends singleRecordCommon {
    
    // This class's constructor will call the parent constructor as it's inherited.
    public function __construct($cnx, $tableName) {
        parent::__construct($cnx, $tableName);
    }

    /**
     * Generate "field = value" pairs to be used in an SQL "where" clause. The field must exist, and the value must match the field's datatype. 
     * Strings sent will be automatically sql escapaed.
     * @param mixed $field Field to be used as a condition in a where clause 
     * @param mixed $whereValue Value linked to the field in the where clause. Text data will be escaped automatically. 
     * @return void Method will generate a fatal error if you try to set a where clause on a non existing field or use the incorrect data type.
     */
    public function addWhere($field, $whereValue) {
        //Does the field even exist?
        if(array_key_exists($field, $this->tableStructure)) {
            //Is this field a numerical or text field?
            if($this->tableStructure[$field]['primitive'] == 'NUM') {
                if(is_numeric($whereValue)) {
                    $this->whereFields[$field] = $whereValue;
                } else {
                    debug::fatal("Type error for field '$field'", $whereValue);
                }
            } else {
                //Process as text
                $this->whereFields[$field] = str::sqlString($whereValue);
            }
        } else {
            //Error: specified field does not exist. Die.
            debug::fatal("Field '$field' does not exist on table", $this->tableName);
        }
    }

    /**
     * Sets the value of the field of a loaded record for UPDATE, as long as it does not conflict with the loaded where list or auto_increment primary key.
     * Text values set here will not be escaped at this level (this is done when the update method is called.)
     * Note 1) MySQL/MariaDB could have case sensitive field names, so you MUST match the case of the loaded field names or the method will return a fatal error.
     * Note 2) This method will set the data as it is provided, but escaping will only happen on update or insert for text values
     * Note 3) The method will raise a fatal error if you attempt to:
     * - Update the primary key value (if a primary key is present)
     * - Update values that are set in the WHERE clause, as these are needed to identify the correct record to update
     * - Set a value that does not match the field's type (numerical or text)
     *  
     * @param mixed $fieldName 
     * @param mixed $fieldValue 
     * @return void 
     */
    public function setField($fieldName, $fieldValue) {
        if(array_key_exists($fieldName, $this->tableStructure)) {
            //Is this a numerical field but a non numeric value?
            if($this->tableStructure[$fieldName]['primitive'] == "NUM" && !is_numeric($fieldValue)) {
                debug::fatal('Field value does not match field data type', array($fieldName, $fieldValue, $this->tableStructure[$fieldName]['primitive']));
            }
            
            //Is there a primary key in the table? If so, we can ignore the where conditions. 
            //If not, then we must rely on the where conditions to identify the unique record.

            //Are we attempting to update the primary key?
            if(!is_null($this->pkField)) {
                //We have a primary key. We can ignore attempts to update on WHERE fields as the PK is enough to uniquely identify the record we want to update.
                if($fieldName == $this->pkField) {
                    debug::fatal('Attempting to update the primary key', $fieldName);
                }
            } else {
                //No PK, so the only way to update a unique record is by the provided WHERE records, so we must reject any attempts to update a field that is used in the where condition.
                if(array_key_exists($fieldName, $this->whereFields)) {
                    //Is this field in the primary key list?
                    debug::fatal('Attempting to update a field used in the WHERE condition and no table contains no primary key', $fieldName);
                }
            }
            
            //So... If we have not failed out... set the value!
            $this->tableStructure[$fieldName]['fieldValue'] = $fieldValue;            
        } else {
            debug::fatal('Requested field to set does not exist in the table or does not match field name case sensitivity', $fieldName);
        }
    }

    /**
     * Read a record from the database, taking the filter conditions into account that have been set through the addWhere method, if any.
     * If the query does not return EXACTLY one record from the system, there will be a fatal error: The method does not force a limit 1, as if more than
     * one record is returned, you have no guarantee that you are working on the correct record, and so the method will error out if your where conditions
     * are not sufficiently precise, and if you have zero records, then you have nothing to do and there is a problem with your query or your database table.
     * @return void 
     */
    private function readRecord() {
        $sql = "select * from " . $this->tableName;
        // Are there any where conditions? 
        $where = null;
        $i = 0;
        $and = null;
        foreach($this->whereFields as $field => $value) {
            if($i == 0) {
                $where = " WHERE ";
                $i++;
            }

            if($i > 0) {
                $and = " AND ";
            }

            $where .= $and . $this->escapeQuoteValueByType($field, $value);
        }

        $sql .= $where;
        $ret = $this->dbCnx->query($sql);

        if(!is_object($ret)) {
            debug::fatal($this->dbCnx->errorInfo(), $sql);
            return false;
        } else {
            //Did we only get one row?
            $returnedRows = $this->result->rowCount();
            if($returnedRows != 1) {
                debug::fatal('Query returned $returnedRows rows. Only 1 row is expected', $sql);
            }
            //Here we go. Return the result object.
            $result = $this->result->fetch(PDO::FETCH_ASSOC);
            if($result === false) {
                debug::flow("End of Recordset");
                return true;
            } else {
                //Load each of the values for the fields defined in the the table structure into the corresponding table structure fieldValue element.
                foreach($this->tableStructure[$field] as $fieldNames) {
                    $this->tableStructure[$fieldNames]['fieldValue'] = $result[$fieldNames];
                }
                $this->recordIsLoaded = true;       // Set the flag so we can run other processes.
                return true;
            }
        }
    }

    /**
     * Take the field values from the tableStructure array and update the values in the database, either from the PK or from the where condition.
     * @return void 
     */
    public function update() {

        if(!$this->recordIsLoaded) {
            debug::setLastError('No record loaded to update');
            return false;
        }

        $sql = "UPDATE " . $this->tableName . " SET ";
        $and = null;
        $i = 0;
        

        foreach($this->tableStructure as $field => $fieldData) {
            //Reset the skip flag
            $skipField = false;

            if(!is_null($this->pkField)) {
                //Update on the loaded PK
                if($field = $this->pkField) {
                    $skipField = true;
                }
            } else {
                //update on the where condition used to select the content.
                if(array_key_exists($field, $this->whereFields)) {
                    $skipField = true;
                }
            }

            if(!$skipField) {
                $sql .= $and . $field . " = " . $this->escapeQuoteValueByType($field, $fieldData['fieldValue']);
            }

            $i++;
            
            if($i > 0) {
                $and = " AND ";
            }
        }

        //We can run the generated query.
        $this->dbCnx->query($sql);
        // Get the number of affected rows. If 0: problem, if 1: ok, if more than one: WTF.
        if($this->dbCnx->affectedRecords != 1) {
            debug::fatal('Update query did not match exactly 1 record', array($sql, $this->dbCnx->affectedRecords));
        }
    }
}
    
class writeNewRecord extends singleRecordCommon {

    // This class's constructor will call the parent constructor as it's inherited.
    public function __construct($cnx, $tableName) {
        parent::__construct($cnx, $tableName);
    }
    
    public function insert() {
        if(!$this->recordIsLoaded) {
            debug::fatal('No record loaded to update');
            return false;
        }
    }

    /**
     * Sets the value of the field of a loaded record for INSERT, as long as it does not conflict with the loaded where list or auto_increment primary key.
     * Text values set here will not be escaped at this level (this is done when the update method is called.)
     * Note 1) MySQL/MariaDB could have case sensitive field names, so you MUST match the case of the loaded field names or the method will return a fatal error.
     * Note 2) This method will set the data as it is provided, but escaping will only happen on update or insert for text values
     * Note 3) The method will raise a fatal error if you attempt to:
     * - Update the primary key value (if a primary key is present)
     * - Update values that are set in the WHERE clause, as these are needed to identify the correct record to update
     * - Set a value that does not match the field's type (numerical or text)
     *  
     * @param mixed $fieldName 
     * @param mixed $fieldValue 
     * @return void 
     */
    public function setField($fieldName, $fieldValue) {
        if(array_key_exists($fieldName, $this->tableStructure)) {
            //Is this a numerical field but a non numeric value?
            if($this->tableStructure[$fieldName]['primitive'] == "NUM" && !is_numeric($fieldValue)) {
                debug::fatal('Field value does not match field data type', array($fieldName, $fieldValue, $this->tableStructure[$fieldName]['primitive']));
            }
            
            //Is there a primary key in the table? If so, we can ignore the where conditions. 
            //If not, then we must rely on the where conditions to identify the unique record.

            //Are we attempting to update the primary key?
            if(!is_null($this->pkField)) {
                //We have a primary key. We can ignore attempts to update on WHERE fields as the PK is enough to uniquely identify the record we want to update.
                if($fieldName == $this->pkField) {
                    debug::fatal('Attempting to update the primary key', $fieldName);
                }
            } else {
                //No PK, so the only way to update a unique record is by the provided WHERE records, so we must reject any attempts to update a field that is used in the where condition.
                if(array_key_exists($fieldName, $this->whereFields)) {
                    //Is this field in the primary key list?
                    debug::fatal('Attempting to update a field used in the WHERE condition and no table contains no primary key', $fieldName);
                }
            }
            
            //So... If we have not failed out... set the value!
            $this->tableStructure[$fieldName]['fieldValue'] = $fieldValue;            
        } else {
            debug::fatal('Requested field to set does not exist in the table or does not match field name case sensitivity', $fieldName);
        }
    }

}

?>