    
    <?php

class singleRecordCommon {
    protected $dbCnx = null;
    protected $tableName = null;
    protected $tableStructure = array();      // array: field name => array(primitive type, null, key, auto increment)
    protected $whereFields = array();         // fields used to hold fields to generate a "where" condition for a query.
    protected $pkField = null;                // If the table contains a primary key, we will store it, as this allows us to run an update only on the unique PK field as a key rather than relying on WHERE conditions.
    protected $recordIsLoaded = false;        // Set flag to true when a record is correctly loaded

    protected $InsertOrReadUpdate = null;
    
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
                        'fieldValue' => null                //Store the field value here when a value is loaded, or store values for an insert.
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
    public function setField($fieldName, $fieldValue) {
        if(array_key_exists($fieldName, $this->tableStructure)) {
            //Is this a numerical field but a non numeric value?
            if($this->tableStructure[$fieldName]['primitive'] == "NUM" && !is_numeric($fieldValue)) {
                if(!is_null($fieldValue)) {
                    debug::fatal('Field value does not match field data type', array('Field' => $fieldName, "Value" => $fieldValue, "Type" => $this->tableStructure[$fieldName]['primitive']));
                }
            }
            
            //Is there a primary key in the table? If so, we can ignore the where conditions. 
            //If not, then we must rely on the where conditions to identify the unique record.

            //Are we attempting to update the primary key?
            if(!is_null($this->pkField)) {
                //We have a primary key. We can ignore attempts to update on WHERE fields as the PK is enough to uniquely identify the record we want to update.
                if($fieldName == $this->pkField && $this->InsertOrReadUpdate == 'ReadUpdate') {
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
     * Check if there is a table described and loaded into the class, and fail with a fatal error if not.
     */
    public function failIfTableStructureNotLoaded() {
        if(!$this->recordIsLoaded) {
            debug::fatal('No table structure loaded. Insert not possible');
        }
    }
}


class read extends singleRecordCommon {

    
    // This class's constructor will call the parent constructor as it's inherited.
    public function __construct($cnx, $tableName) {
        parent::__construct($cnx, $tableName);
        $this->InsertOrReadUpdate = 'ReadUpdate';
    }

    /**
     * Generate "field = value" pairs to be used in an SQL "where" clause. The field must exist, and the value must match the field's datatype. 
     * Strings sent will be automatically sql escapaed.
     * @param mixed $field Field to be used as a condition in a where clause 
     * @param mixed $whereValue Value linked to the field in the where clause. Text data will be escaped automatically. 
     * @return void Method will generate a fatal error if you try to set a where clause on a non existing field or use the incorrect data type.
     */
    public function addWhere($field, $whereValue) {
        $this->failIfTableStructureNotLoaded();

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
     * Read a record from the database, taking the filter conditions into account that have been set through the addWhere method, if any.
     * If the query does not return EXACTLY one record from the system, there will be a fatal error: The method does not force a limit 1, as if more than
     * one record is returned, you have no guarantee that you are working on the correct record, and so the method will error out if your where conditions
     * are not sufficiently precise, and if you have zero records, then you have nothing to do and there is a problem with your query or your database table.
     * @return void 
     */
    public function loadOneRecord() {
        $this->failIfTableStructureNotLoaded();

        $sqlHead = "select * from " . $this->tableName;
        $sqlExtra = '';
        // Are there any where conditions? 
        $where = null;
        $i = 0;
        $and = null;
        foreach($this->whereFields as $field => $value) {
            if($i == 0) {
                $sqlExtra .= " WHERE ";
                $i++;
            } else {
                $sqlExtra .= " AND ";
            }
            $sqlExtra .= $field . " = " . $this->escapeQuoteValueByType($field, $value);
        }

        $rs = new recordset($this->dbCnx->query($sqlHead . $sqlExtra));
        if($this->dbCnx->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
            //We have to do a select count(*) on sqlite, as recordCount does not work on selects... :(
            foreach ($this->dbCnx->query('select count(*) as num from ' . $this->tableName . $sqlExtra) as $row) {
                $records = $row['num'];
            }
        } else {
            $records = $rs->recordCount();
        }
        
        if($records != 1) {
            debug::fatal("Query returned $records rows. Only 1 row is expected", 'select count(*) as num from ' . $this->tableName . $sqlExtra);
        } else {
            while ($result = $rs->fetchArray()) {
                foreach($result as $field => $value) {
                    $this->tableStructure[$field]['fieldValue'] = $value;
                }
            }
        }
    }

    /**
     * Take the field values from the tableStructure array and update the values in the database, either from the PK or from the where condition.
     * @return void 
     */
    public function updateRecord() {

        $this->failIfTableStructureNotLoaded();

        $sql = "UPDATE " . $this->tableName . " SET ";
        $and = null;
        $i = 0;
        
        foreach($this->tableStructure as $field => $fieldData) {
            //Reset the skip flag
            $skipField = false;

            
            if(!is_null($this->pkField)) {
                //Update on the loaded PK
                if($field == $this->pkField) {
                    $skipField = true;
                }
            } else {
                //update on the where condition used to select the content.
                if(array_key_exists($field, $this->whereFields)) {
                    $skipField = true;
                }
            }

            if(is_null($fieldData) && $this->tableStructure[$field]['nullAllowed'] == 'NO') {
                debug::fatal('Attempting to insert null value into not null field', array($field, $this->tableStructure));
            }

            if(!$skipField) {
                $sql .= $and . $field . " = " . $this->escapeQuoteValueByType($field, $fieldData['fieldValue']);
            } else {
                echo "skipping $field : " . $fieldData['fieldValue'] . "\n"; 
            }

            if(!$skipField) {
                $i++;
            }
            
            if($i > 0) {
                $and = ", ";
            }
        }

        //Add in the WHERE conditions:
        if(!is_null($this->pkField)) {
            $sql .= " WHERE " . $this->pkField . " = " . $this->tableStructure[$this->pkField]['fieldValue'];
        } else {
            //We will have to use the where array.
            $where = null;
            $i = 0;
            foreach($this->whereFields as $field => $value) {
                
                if($i == 0) {
                    $where .= " WHERE ";
                } else {
                    $where .= " AND ";
                }

                $where .= $field . " = " .  $this->escapeQuoteValueByType($field, $value);
                $i++;
            }
            $sql .= $where;
        }

        //We can run the generated query.
        $affectedRecords = $this->dbCnx->exec($sql);
        // Get the number of affected rows. If 0: problem, if 1: ok, if more than one: WTF.
        if($affectedRecords != 1) {
            debug::fatal('Update query did not match exactly 1 record', array($sql, $this->dbCnx->affectedRecords));
        } else {
            return true;
        }
    }

}

class update extends singleRecordCommon {

    
    // This class's constructor will call the parent constructor as it's inherited.
    public function __construct($cnx, $tableName) {
        parent::__construct($cnx, $tableName);
        $this->InsertOrReadUpdate = 'ReadUpdate';
    }

    /**
     * Generate "field = value" pairs to be used in an SQL "where" clause. The field must exist, and the value must match the field's datatype. 
     * Strings sent will be automatically sql escapaed.
     * @param mixed $field Field to be used as a condition in a where clause 
     * @param mixed $whereValue Value linked to the field in the where clause. Text data will be escaped automatically. 
     * @return void Method will generate a fatal error if you try to set a where clause on a non existing field or use the incorrect data type.
     */
    public function addWhere($field, $whereValue) {
        $this->failIfTableStructureNotLoaded();

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
     * Read a record from the database, taking the filter conditions into account that have been set through the addWhere method, if any.
     * If the query does not return EXACTLY one record from the system, there will be a fatal error: The method does not force a limit 1, as if more than
     * one record is returned, you have no guarantee that you are working on the correct record, and so the method will error out if your where conditions
     * are not sufficiently precise, and if you have zero records, then you have nothing to do and there is a problem with your query or your database table.
     * @return void 
     */
    public function loadOneRecord() {
        $this->failIfTableStructureNotLoaded();

        $sqlHead = "select * from " . $this->tableName;
        $sqlExtra = '';
        // Are there any where conditions? 
        $where = null;
        $i = 0;
        $and = null;
        foreach($this->whereFields as $field => $value) {
            if($i == 0) {
                $sqlExtra .= " WHERE ";
                $i++;
            } else {
                $sqlExtra .= " AND ";
            }
            $sqlExtra .= $field . " = " . $this->escapeQuoteValueByType($field, $value);
        }

        $rs = new recordset($this->dbCnx->query($sqlHead . $sqlExtra));
        if($this->dbCnx->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
            //We have to do a select count(*) on sqlite, as recordCount does not work on selects... :(
            foreach ($this->dbCnx->query('select count(*) as num from ' . $this->tableName . $sqlExtra) as $row) {
                $records = $row['num'];
            }
        } else {
            $records = $rs->recordCount();
        }
        
        if($records != 1) {
            debug::fatal("Query returned $records rows. Only 1 row is expected", 'select count(*) as num from ' . $this->tableName . $sqlExtra);
        } else {
            while ($result = $rs->fetchArray()) {
                foreach($result as $field => $value) {
                    $this->tableStructure[$field]['fieldValue'] = $value;
                }
            }
        }
    }

    /**
     * Take the field values from the tableStructure array and update the values in the database, either from the PK or from the where condition.
     * @return void 
     */
    public function updateRecord() {

        $this->failIfTableStructureNotLoaded();

        $sql = "UPDATE " . $this->tableName . " SET ";
        $and = null;
        $i = 0;
        
        foreach($this->tableStructure as $field => $fieldData) {
            //Reset the skip flag
            $skipField = false;

            
            if(!is_null($this->pkField)) {
                //Update on the loaded PK
                if($field == $this->pkField) {
                    $skipField = true;
                }
            } else {
                //update on the where condition used to select the content.
                if(array_key_exists($field, $this->whereFields)) {
                    $skipField = true;
                }
            }

            if(is_null($fieldData) && $this->tableStructure[$field]['nullAllowed'] == 'NO') {
                debug::fatal('Attempting to insert null value into not null field', array($field, $this->tableStructure));
            }

            if(!$skipField) {
                $sql .= $and . $field . " = " . $this->escapeQuoteValueByType($field, $fieldData['fieldValue']);
            } else {
                echo "skipping $field : " . $fieldData['fieldValue'] . "\n"; 
            }

            if(!$skipField) {
                $i++;
            }
            
            if($i > 0) {
                $and = ", ";
            }
        }

        //Add in the WHERE conditions:
        if(!is_null($this->pkField)) {
            $sql .= " WHERE " . $this->pkField . " = " . $this->tableStructure[$this->pkField]['fieldValue'];
        } else {
            //We will have to use the where array.
            $where = null;
            $i = 0;
            foreach($this->whereFields as $field => $value) {
                
                if($i == 0) {
                    $where .= " WHERE ";
                } else {
                    $where .= " AND ";
                }

                $where .= $field . " = " .  $this->escapeQuoteValueByType($field, $value);
                $i++;
            }
            $sql .= $where;
        }

        //We can run the generated query.
        $affectedRecords = $this->dbCnx->exec($sql);
        // Get the number of affected rows. If 0: problem, if 1: ok, if more than one: WTF.
        if($affectedRecords != 1) {
            debug::fatal('Update query did not match exactly 1 record', array($sql, $this->dbCnx->affectedRecords));
        } else {
            return true;
        }
    }

}


    class foo {

    
    // This class's constructor will call the parent constructor as it's inherited.
    public function __construct($cnx, $tableName) {
        parent::__construct($cnx, $tableName);
        $this->InsertOrReadUpdate = 'ReadUpdate';
    }

    /**
     * Generate "field = value" pairs to be used in an SQL "where" clause. The field must exist, and the value must match the field's datatype. 
     * Strings sent will be automatically sql escapaed.
     * @param mixed $field Field to be used as a condition in a where clause 
     * @param mixed $whereValue Value linked to the field in the where clause. Text data will be escaped automatically. 
     * @return void Method will generate a fatal error if you try to set a where clause on a non existing field or use the incorrect data type.
     */
    public function addWhere($field, $whereValue) {
        $this->failIfTableStructureNotLoaded();

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
     * Read a record from the database, taking the filter conditions into account that have been set through the addWhere method, if any.
     * If the query does not return EXACTLY one record from the system, there will be a fatal error: The method does not force a limit 1, as if more than
     * one record is returned, you have no guarantee that you are working on the correct record, and so the method will error out if your where conditions
     * are not sufficiently precise, and if you have zero records, then you have nothing to do and there is a problem with your query or your database table.
     * @return void 
     */
    public function loadOneRecord() {
        $this->failIfTableStructureNotLoaded();

        $sqlHead = "select * from " . $this->tableName;
        $sqlExtra = '';
        // Are there any where conditions? 
        $where = null;
        $i = 0;
        $and = null;
        foreach($this->whereFields as $field => $value) {
            if($i == 0) {
                $sqlExtra .= " WHERE ";
                $i++;
            } else {
                $sqlExtra .= " AND ";
            }
            $sqlExtra .= $field . " = " . $this->escapeQuoteValueByType($field, $value);
        }

        $rs = new recordset($this->dbCnx->query($sqlHead . $sqlExtra));
        if($this->dbCnx->getAttribute(PDO::ATTR_DRIVER_NAME) == 'sqlite') {
            //We have to do a select count(*) on sqlite, as recordCount does not work on selects... :(
            foreach ($this->dbCnx->query('select count(*) as num from ' . $this->tableName . $sqlExtra) as $row) {
                $records = $row['num'];
            }
        } else {
            $records = $rs->recordCount();
        }
        
        if($records != 1) {
            debug::fatal("Query returned $records rows. Only 1 row is expected", 'select count(*) as num from ' . $this->tableName . $sqlExtra);
        } else {
            while ($result = $rs->fetchArray()) {
                foreach($result as $field => $value) {
                    $this->tableStructure[$field]['fieldValue'] = $value;
                }
            }
        }
    }

    /**
     * Take the field values from the tableStructure array and update the values in the database, either from the PK or from the where condition.
     * @return void 
     */
    public function updateRecord() {

        $this->failIfTableStructureNotLoaded();

        $sql = "UPDATE " . $this->tableName . " SET ";
        $and = null;
        $i = 0;
        
        foreach($this->tableStructure as $field => $fieldData) {
            //Reset the skip flag
            $skipField = false;

            
            if(!is_null($this->pkField)) {
                //Update on the loaded PK
                if($field == $this->pkField) {
                    $skipField = true;
                }
            } else {
                //update on the where condition used to select the content.
                if(array_key_exists($field, $this->whereFields)) {
                    $skipField = true;
                }
            }

            if(is_null($fieldData) && $this->tableStructure[$field]['nullAllowed'] == 'NO') {
                debug::fatal('Attempting to insert null value into not null field', array($field, $this->tableStructure));
            }

            if(!$skipField) {
                $sql .= $and . $field . " = " . $this->escapeQuoteValueByType($field, $fieldData['fieldValue']);
            } else {
                echo "skipping $field : " . $fieldData['fieldValue'] . "\n"; 
            }

            if(!$skipField) {
                $i++;
            }
            
            if($i > 0) {
                $and = ", ";
            }
        }

        //Add in the WHERE conditions:
        if(!is_null($this->pkField)) {
            $sql .= " WHERE " . $this->pkField . " = " . $this->tableStructure[$this->pkField]['fieldValue'];
        } else {
            //We will have to use the where array.
            $where = null;
            $i = 0;
            foreach($this->whereFields as $field => $value) {
                
                if($i == 0) {
                    $where .= " WHERE ";
                } else {
                    $where .= " AND ";
                }

                $where .= $field . " = " .  $this->escapeQuoteValueByType($field, $value);
                $i++;
            }
            $sql .= $where;
        }

        //We can run the generated query.
        $affectedRecords = $this->dbCnx->exec($sql);
        // Get the number of affected rows. If 0: problem, if 1: ok, if more than one: WTF.
        if($affectedRecords != 1) {
            debug::fatal('Update query did not match exactly 1 record', array($sql, $this->dbCnx->affectedRecords));
        } else {
            return true;
        }
    }




}