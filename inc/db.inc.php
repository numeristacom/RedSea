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
 * 
*/

namespace RedSea;
use PDO;

/**
 * RedSea Database helper class. Based on PDO, this class simplifies connections to SQLite, 
 * and provides methods to execute direct SQL queries without returning a result, return a result set for parsing, and
 * can return the current PDO connection as a standalone object.
 */
class rsdb {

    /**
     * Contains the DSN connection string
     * @internal 
     */
    protected $DSN = null;
    
    /**
     * Contains the database connection object
     * @internal 
     */
    protected $dbConnection = null;

    /**
     * Contains the last inserted id that is updated after a query that adds a record into a table with an auto-number primary key
     */
    public $insertId = null; 

    /**
     * Contains the number of affected records
     */
    public $affectedRecords = null;

    /**
     * Contains the database type as used when creating the DSN, used by other classes to adapt to database differences between MariaDB and SQLite.
     * @var string
     */
    public $selectedDbType = null;

    /**
     * Opens a connection to MariaDB or SQLite
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
                $this->selectedDbType = "mariadb";
                break;
            case "sqlite":
                $this->DSN = "sqlite:$dbname";
                $this->selectedDbType = "sqlite";
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
     * @param string $sql SQL query to execute.
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

    /**
     * Return the currently opened DB connection as an object.
     * @return PDO connection object
     */
    public function getDBConnection() {
        return $this->dbConnection;
    }
}

/**
 * wrapper for result sets, including consistant error reporting through the debug class, allows counting the number of results returned in a query result
 * and avoids script overloading when working on a large result set by returning data record by record.
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

    /**
     * Return the record count from a PDO result set
     * @return mixed False if no records, otherwise the integer number of records queried.
     */
    public function recordCount() {
        //die(var_dump($this->result->rowCount()));
        return $this->result->rowCount();
    }
}

/**
 * Class that contains the common data and methods for full single record operations.
 * Single Record Operations are defined as full record inserts, or full record updates to a specific table, generally based of form entered data.
 * This class requires a pre-opened RS DB object and the name of the table to describe as parameters to the constructor.
 * Note: Your table may have a unique fields. This class does not manage these. It will only manage data types, pk ai and not null
 */
class singleRecordCommon {
    public $dbCnx = null;
    public $tableName = null;
    public $tableStructure = array();      // array: field name => array(primitive type, null, key, auto increment)
    public $whereFields = array();         // fields used to hold fields to generate a "where" condition for a query.
    public $pkField = null;                // If the table contains a primary key, we will store it, as this allows us to run an update only on the unique PK field as a key rather than relying on WHERE conditions.
    public $recordIsLoaded = false;        // Set flag to true when a record is correctly loaded
    public $shadowTableStructure = null;   // Keep a copy of the loaded table structure so this can be reset without reinstantiating the object 
    
    /**
     * Class constructor.
     * @param mixed $cnx RS DB database connection
     * @param string $tableName Name of the table accessible through the connection to describe and work on.
     * @return void 
     */
    public function __construct($cnx, $tableName) {
        $this->dbCnx = $cnx;
        $this->tableName = $tableName;
        if($this->describeTable($tableName) !== true) {     //Crash and burn.
            debug::fatal("Could not load table structure for table", $tableName);
        } else {
            //Cache a copy of the table structure for future use in loops.
            $this->shadowTableStructure = $this->tableStructure;
            $this->recordIsLoaded = true;
        }
    }

    /**
     * Describe the structure of a database table, and load it into the tableStructure array, including field names, values, auto increment, primary key and not null flags.
     * @param mixed $table Table to describe 
     * @return bool|void 
     */
    protected function describeTable($table) {
        $table = str::sqlString($table);
        $ret = false;
        // MariaDB or SQLite? We need to run different types of queries to get the same information.
        $dbType = $this->dbCnx->getAttribute(PDO::ATTR_DRIVER_NAME);
        debug::flow('DB type', $dbType);
        switch ($dbType) {
            case "mysql":
                //No break, just MariaDB will connect as MySQL internally, so don't break and roll into the mariadb part.
            case "mariadb":
                //Easy with MariaDB/MySql
                $sql = "show columns from " . $table;
                $rs = new recordset($this->dbCnx->query($sql));
        
                while ($result = $rs->fetchArray()) {
                    //Store the PK field if we find it. It will make our life easier later!
                    if($result['Key'] == 'PRI') {
                        $this->pkField = $result['Field'];
                    }
        
                    $this->tableStructure[$result['Field']] = array(
                        'primitive' => $this->returnPrimitiveDataType($result['Type']), //Primitive data type to counter sql injection. isString or isNumber
                        'nullAllowed' => $result['Null'],   //Is null value allowed?
                        'key' => $result['Key'],            //Is there a key or index here? (PRI = primary key. If auto increment, ignore this value in insert / update unless forced.)
                        'ai' => $this->isAutoIncrement($result['Extra']),   //Is this an auto increment field?
                        'fieldValue' => null,                //Store the field value here when a value is loaded, or store values for an insert.
                        'valueChanged' => false
                    );
                }
                $ret = true;
                break;
            case "sqlite":
                //Not as easy with SQLite unfortunately. We need 2 queries. One for the structure,
                //another for auto inc.
                $sql = "pragma table_info('$table')";   // Describes PK and not null
                $rs = new recordset($this->dbCnx->query($sql));
        
                while ($result = $rs->fetchArray()) {
                    
                    $sqlitepk = false;
                    if($result['pk'] == 1) { //Store the PK field if we find it. It will make our life easier later!
                        $this->pkField = $result['name'];
                        //Is any auto increment set at all?
                        $sqlpk = "SELECT count(*) as num FROM sqlite_master WHERE type='table' AND name='sqlite_sequence'";
                        $rspk = new recordset($this->dbCnx->query($sqlpk));
                        while ($resultpk = $rspk->fetchArray()) {
                            if($resultpk['num'] == 1) {
                                // Does this PK field have an auto increment? 
                                $sqlpk = "SELECT COUNT(*) as num FROM sqlite_sequence WHERE name='$table'";
                                $rspk1 = new recordset($this->dbCnx->query($sqlpk));
                                while ($resultpk1 = $rspk1->fetchArray()) {
                                    if($resultpk1['num'] == 1) {
                                        // The pk field is auto increment.
                                        $sqlitepk = true;
                                    }
                                }
                            }
                        }
                        unset($rspk);
                    }

                    $this->tableStructure[$result['name']] = array(
                        'primitive' => $this->returnSqlitePrimitiveDataType($result['type']), //Primitive data type to counter sql injection. isString or isNumber
                        'nullAllowed' => $this->sqliteNotnull($result['notnull']),   //Is null value allowed?
                        'key' => $this->sqlitePk($result['pk']),            //We will only look for PK's here.
                        'ai' => $sqlitepk,
                        'fieldValue' => null   //Store the field value here when a value is loaded, or store values for an insert.
                    );
                }
                $ret = true;
                break;
            default:
                debug::fatal('Database type not recognised', $this->dbCnx->selectedDbType);
        }
        unset($rs);
        //Now copy the table structure array to a shadow copy.
        $this->shadowTableStructure = $this->tableStructure;
        return $ret;
    }

    /**
     * Takes the notnull field from the pragma table_info command from sqlite and returns the expected not null value
     * @param mixed $result notnull value (0 or 1)
     * @return string YES if null is allowed, NO if not.
     */
    private function sqliteNotnull($result) {
        if($result == 1) {
            return 'YES';
        } else {
            return 'NO';
        }
    }

    /**
     * Takes the PK field and checks if it's set to 1 (PK) or not.
     */
    private function sqlitePk($result) {
        if($result == 1) {
            return 'PRI';
        } else {
            return null;
        }
    }

    /**
     * Return a MariaDB field's primitive data type (string or number) for a given field's set data type.
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
        } elseif(stripos($typeRecord, 'double') !== FALSE) {
            return 'NUM';
        } elseif(stripos($typeRecord, 'decimal') !== FALSE) {
            return 'NUM';
        } elseif(stripos($typeRecord, 'bit') !== FALSE) {
            return 'NUM';
        } else {
            return 'STR';
        }
    }

    /**
     * Return an SQLite field's primitive data type (string or number) for a given field's set data type.
     * This library expects STRICT tables, where the value actually does match the column's data type.
     * - or at least where the table users respect these types.
     * These data types are taken from https://www.sqlite.org/datatype3.html valid as of 3
     * Known numerical values will be returned as numerical, all others will be returned as text.
     * Dates will be considered text types.
     * BLOB's are undefined... but will best be considered text, and insert hex strings into them rather than raw binary!
     * @param mixed $fieldDataType Database data type to check
     * @return int 1 for numerical type, 0 for everything else (text, dates, spatial)
     */
    private function returnSqlitePrimitiveDataType($typeRecord) {
        debug::flow();
        if(stripos($typeRecord, 'int') !== FALSE) {
            return 'NUM';
        } elseif(stripos($typeRecord, 'real') !== FALSE) {
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
        if(stripos($extraData, 'auto_increment') !== FALSE) {
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
            if(is_null($value)) {
                return 'NULL';
            } else if(is_numeric($value)) {
                return $value;
            } else {
                debug::fatal('Field value does not match field data type', array($field, $value));
            }
        } else {
            return "'" . str::sqlString($value) . "'";
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

     // setField - needs to be independant depending if this is an insert or update and not added to the table structure.
    public function setField($fieldName, $fieldValue) {
        if(array_key_exists($fieldName, $this->tableStructure)) {
            //Is this a numerical field but a non numeric value?
            if($this->tableStructure[$fieldName]['primitive'] == "NUM" && !is_numeric($fieldValue)) {
                if(!is_null($fieldValue)) {
                    debug::fatal('Field value does not match field data type', array('Field' => $fieldName, "Value" => $fieldValue, "Type" => $this->tableStructure[$fieldName]['primitive']));
                }
            }

            if($this->tableStructure[$fieldName]['primitive'] == "NUM") {
                $fieldValue = str::sqlNumber($fieldValue);
            } else {
                $fieldValue = str::sqlString($fieldValue);
            }

            //So... If we have not failed out... set the value!
            $this->tableStructure[$fieldName]['fieldValue'] = $fieldValue;
            $this->tableStructure[$fieldName]['valueChanged'] = true;        
        } else {
            debug::fatal('Requested field to set does not exist in the table (or does not match field name case sensitivity)', $fieldName);
        }

    }

    /**
     * Check if there is a table described and loaded into the class, and fail with a fatal error if not.
     */
    public function failIfTableStructureNotLoaded() {
        if(!$this->recordIsLoaded) {
            debug::fatal('No table structure loaded. Insert not possible');
        }
    }
}

class recordUpsert extends singleRecordCommon {
    public $pkid = null;
    private $operationToExecute = null;

    // This class's constructor will call the parent constructor as it's inherited.
    public function __construct($cnx, $tableName, $pkid=null) {
        parent::__construct($cnx, $tableName);
        //We can target using where or a unique id in the table. Guaranteed 100% is the pkid record read. 
        //If this value is not null, then we don't need to do anything else.
        if(is_null($pkid)) {
            $this->operationToExecute = "INSERT";
        } else {
            if(is_numeric($pkid)) {
                $this->pkid = $pkid;
                $this->operationToExecute = "UPDATE";
            } else {
                debug::fatal("Non numeric PK id for the table was provided.");
            }
        }
    }

    public function upsert() {
        $this->failIfTableStructureNotLoaded();
        $i = 0;
        if($this->operationToExecute == 'UPDATE') {
            $sql = "UPDATE " . $this->tableName . " SET ";
            $and = null;
            foreach($this->tableStructure as $field => $fieldData) {
                if($fieldData['valueChanged'] == true) {
                    if(is_null($fieldData['fieldValue']) && $fieldData['nullAllowed'] == 'NO') {
                        debug::fatal('Attempting to insert null value into not null field', array($field, $fieldData));
                    }

                    if($i > 0) {
                        $and = ", ";
                    }
                    
                    $sql .= $and . $field . " = " . str::sqlUpsertValue($fieldData['fieldValue'], $fieldData['primitive']);
                    $i++;
                }
            }
            $sql .= " WHERE " . $this->pkField . " = " . $this->pkid;

        } else {
            $sql = "INSERT INTO " . $this->tableName . " (";
            $sqlValues = " VALUES (";
            $sqlData = null;

            foreach($this->tableStructure as $field => $fieldData) {

                if($fieldData['valueChanged'] == true) {
                    if(is_null($fieldData['fieldValue']) && $fieldData['nullAllowed'] == 'NO' && !$fieldData['ai'] = true ) {
                        debug::fatal('Attempting to insert null value into not null field', array($field,$fieldData));
                    }

                    if($i == 0) {
                        $comma = '';
                    } else {
                        $comma = ', ';
                    }

                    $sql .= $comma . $field;
                    $sqlValues .= $comma . str::sqlUpsertValue($fieldData['fieldValue'], $fieldData['primitive']);
                    $i++;
                }
            }

            $sql .= ") ";
            $sqlValues .= ")";
            $sql .= $sqlValues;
        }

        $affectedRecords = $this->dbCnx->exec($sql);
        // Get the number of affected rows. If 0: problem, if 1: ok, if more than one: WTF.
        if($affectedRecords != 1) {
            debug::fatal('query did not match exactly 1 record', array($sql, $this->dbCnx->affectedRecords));
        } else {
            if($this->operationToExecute == 'INSERT') {
                return $this->dbCnx->lastInsertId();
            } else {
                return true;
            }
        }
    }

    /**
     * Reset the object so it can be used for a new insert without having to instanciate a  new one, when used in a multi-upsert
     * loop as if the class is re-instanciated, each upsert would require a describe and analysis of the table.
     * Calling reset allows the class to clear and reset the initally described table data structures, and allow a fresh upsert
     * operation, without the class and database overhead that a new object would require.
     * @return void. Fails if a non numeric record primary key value provided.
     */
    public function reset($pkid=null) {
        //Overwrite the currently used data structure with the shadow copy
        $this->tableStructure = $this->shadowTableStructure;
        //And reset the operation to execute:
        if(is_null($pkid)) {
            $this->operationToExecute = "INSERT";
        } else {
            if(is_numeric($pkid)) {
                $this->pkid = $pkid;
                $this->operationToExecute = "UPDATE";
            } else {
                debug::fatal("Non numeric PK id for the table was provided.");
            }
        }
    }
}

/**
 * Loads a known record from the database, and makes it available for reading, field by field, but also allows for updating those loaded fields which
 * can then be written back to the database.
 * with the new values.
 * Note that if the record is read from a table with a Primary Key, then all the fields can be updated except the Primary Key as it allows for atomic updating
 * with only one identification key.
 * If the record does not have a Primary Key, then the update will need to be based from the WHERE conditions used to read that unique record,
 * and those fields will not be updatable.
 */
class recordRead extends singleRecordCommon {
    
    public $pkid = null;

    // This class's constructor will call the parent constructor as it's inherited.
    public function __construct($cnx, $tableName, $pkid) {
        parent::__construct($cnx, $tableName);
        //We can target using where or a unique id in the table. Guaranteed 100% is the pkid record read. 
        //If this value is not null, then we don't need to do anything else.
        if(!is_null($pkid) && is_numeric($pkid)) {
            $this->pkid = $pkid;
        } else {
            debug::fatal("No PK id for the table was provided.");
        }

        $this->failIfTableStructureNotLoaded();

        if(is_null($this->pkField)) {
            debug::fatal("There is no PK field found in this table");
        }

        $sqlHead = "select * from " . $this->tableName . " where " . $this->pkField . " = " . $this->pkid;

        $rs = new recordset($this->dbCnx->query($sqlHead));
        if($this->dbCnx->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
            //We have to do a select count(*) on sqlite, as recordCount does not work on selects... :(
            foreach ($this->dbCnx->query('select count(*) as num from ' . $this->tableName . " where " . $this->pkField . " = " . $this->pkid) as $row) {
                $records = $row['num'];
            }
        } else {
            $records = $rs->recordCount();
        }
        
        if($records != 1) {
            debug::fatal("Query returned $records rows. Only 1 row is expected", $sqlHead );
        } else {
            while ($result = $rs->fetchArray()) {
                foreach($result as $field => $value) {
                    $this->tableStructure[$field]['fieldValue'] = $value;
                }
            }
        }
    }
}

class recordDelete extends singleRecordCommon {
    // This class's constructor will call the parent constructor as it's inherited.
    public function __construct($cnx, $tableName, $pkid=null) {
        parent::__construct($cnx, $tableName);
    }

    public function delete($pkid) {
        if((!is_numeric($pkid))) {
            debug::fatal("Non numeric primary key ID provided", $pkid);
        }
        $sql = "delete from " . $this->tableName . " where " . $this->pkField . " = " . $pkid;
        return $this->dbCnx->exec($sql);
    }
}

?>