# `namespace RedSea`

This file defines some basic DB libraries.

 * **Author:** Daniel Page <daniel@danielpage.com>
 * **Copyright:** Copyright (c) 2021, Daniel Page
 * **License:** Licensed under the EUPL v1.2 - https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12

     <p>

     The MySQL classes don't add much visible differences to stock MySQL PDO php functions, but they do add

     debug services to the objects that can be activated on demand along with error notification in line with

     the other classes in RedSea.

     <p>

# `class rsdb`

RedSea Database helper class. Based on PDO, this class simplifies connections to SQLite, and provides methods to execute direct SQL queries without returning a result, return a result set for parsing, and can return the current PDO connection as a standalone object.

## `protected $DSN = null`

Contains the DSN connection string


## `protected $dbConnection = null`

Contains the database connection object


## `public $insertId = null`

Contains the last inserted id that is updated after a query that adds a record into a table with an auto-number primary key

## `public $affectedRecords = null`

Contains the number of affected records

## `public $selectedDbType = null`

Contains the database type as used when creating the DSN, used by other classes to adapt to database differences between MariaDB and SQLite.

 * **Type:** `string` — 

## `public function __construct($dbtype, $dbname, $host=null, $username=null, $password=null, $port=3306)`

Opens a connection to MariaDB or SQLite

 * **Parameters:**
   * `$dbtype` — `string` — Database type to connect to: mariadb or sqlite
   * `$dbname` — `string` — Name of the data base to open. For an SQLite database, it will

     be the path and name of the SQLite database file.
   * `$host` — `string` — Hostname or IP address of the database server to connect to. Not required for SQLite database.
   * `$username` — `string` — Username credential for the database. Not required for SQLite database.
   * `$password` — `string` — Password credential for the database. Not required for SQLite database.
   * `$port` — `int` — If not specified, the MariaDB default port 3306 will be used, but can be specified to any other number.
 * **Returns:** `void` — In case of error, the method will raise a fatal error to output.

## `public function execute($sql)`

Executes a single query on the database that does not return a result object.

 * **Parameters:** `$sql` — `string` — SQL query to execute.
 * **Returns:** `bool` — TRUE on success, FALSE on failure.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

     On success, the system will set the parameters affected Records and lastInsertID

## `public function query($sql)`

Runs a query on the database that returns a result set.

 * **Parameters:** `$sql` — `string` — 
 * **Returns:** `false|object` — On success, an SQLite result set is returned, otherwise FALSE. 

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public function getDBConnection()`

Return the currently opened DB connection as an object.

 * **Returns:** `PDO` — connection object

# `class recordset`

wrapper for result sets, including consistant error reporting through the debug class, allows counting the number of results returned in a query result and avoids script overloading when working on a large result set by returning data record by record.

## `public $end = false`

If TRUE, the recordset has reached the end.

## `public function __construct($dbResult=null)`

Set a local object for procesing by passing an SQLite result set to the class.

 * **Parameters:** `$sqliteObject` — `object` — SQLIte result object
 * **Returns:** `mixed|bool` — Ifsuccessful, the method will return TRUE, otherwise FALSE.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag property will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public function fetchArray($recordType=PDO::FETCH_ASSOC)`

Controls how the next row of the result set be returned to the caller

 * **Parameters:** `$recordType` — `mixed|null` — Default MYSQLI_ASSOC.

     This value must be one of either PDO::FETCH_ASSOC, PDO::FETCH_NUM, or PDO::FETCH_BOTH.

     - SQLITE3_ASSOC: returns an array indexed by column name as returned in the corresponding result set

     - SQLITE3_NUM: returns an array indexed by column number as returned in the corresponding result set, starting at column 0

     - SQLITE3_BOTH: returns an array indexed by both column name and number as returned in the corresponding result set, starting at column 0
 * **Returns:** `array` — Contents of the current record or false if there is nothing to return.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag property will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public function recordCount()`

Return the record count from a PDO result set

 * **Returns:** `mixed` — False if no records, otherwise the integer number of records queried.

# `class singleRecordCommon`

Class that contains the common data and methods for full single record operations. Single Record Operations are defined as full record inserts, or full record updates to a specific table, generally based of form entered data. This class requires a pre-opened RS DB object and the name of the table to describe as parameters to the constructor. Note: Your table may have a unique fields. This class does not manage these. It will only manage data types, pk ai and not null

## `public function __construct($cnx, $tableName)`

Class constructor.

 * **Parameters:**
   * `$cnx` — `mixed` — RS DB database connection
   * `$tableName` — `string` — Name of the table accessible through the connection to describe and work on.
 * **Returns:** `void` — 

## `protected function describeTable($table)`

Describe the structure of a database table, and load it into the tableStructure array, including field names, values, auto increment, primary key and not null flags.

 * **Parameters:** `$table` — `mixed` — Table to describe
 * **Returns:** `bool|void` — 

## `private function sqliteNotnull($result)`

Takes the notnull field from the pragma table_info command from sqlite and returns the expected not null value

 * **Parameters:** `$result` — `mixed` — notnull value (0 or 1)
 * **Returns:** `string` — YES if null is allowed, NO if not.

## `private function sqlitePk($result)`

Takes the PK field and checks if it's set to 1 (PK) or not.

## `private function returnPrimitiveDataType($typeRecord)`

Return a MariaDB field's primitive data type (string or number) for a given field's set data type. These data types are taken from https://mariadb.com/kb/en/data-types/ valid as of 10.3 Known numerical values will be returned as numerical, all others will be returned as text. Dates will be considered text types.

 * **Parameters:** `$fieldDataType` — `mixed` — Database data type to check
 * **Returns:** `int` — 1 for numerical type, 0 for everything else (text, dates, spatial)

## `private function returnSqlitePrimitiveDataType($typeRecord)`

Return an SQLite field's primitive data type (string or number) for a given field's set data type. This library expects STRICT tables, where the value actually does match the column's data type. - or at least where the table users respect these types. These data types are taken from https://www.sqlite.org/datatype3.html valid as of 3 Known numerical values will be returned as numerical, all others will be returned as text. Dates will be considered text types. BLOB's are undefined... but will best be considered text, and insert hex strings into them rather than raw binary!

 * **Parameters:** `$fieldDataType` — `mixed` — Database data type to check
 * **Returns:** `int` — 1 for numerical type, 0 for everything else (text, dates, spatial)

## `private function isAutoIncrement($extraData)`

Check if the Extra column contains an auto_increment modifier

 * **Parameters:** `$extraData` — `mixed` — Value from the Extra column from a show columns from table statement
 * **Returns:** `bool` — True if the field contains an auto_increment modifier, otherwise False.

## `public function getField($fieldName)`

Get the value from a loaded record if the field exists. Note 1) MySQL/MariaDB could have case sensitive field names, so you MUST match the case of the loaded field names or the method will raise a fatal error. Note 2) This method will return the data as it is stored in the database, unescaped.

 * **Parameters:** `$fieldName` — `string` — Case sensitive name of the loaded field containing the value you want to return

## `protected function escapeQuoteValueByType($field, $value)`

Escape and quote a value for a specific field according to it's type

 * **Parameters:**
   * `$field` — `mixed` — Name of the field to check
   * `$value` — `mixed` — Value of the field that may need escaping
 * **Returns:** `mixed` — If the expected type is numerical, then the value will be returned as is. If it is a string, it will be quoted and escaped.

## `public function setField($fieldName, $fieldValue)`

Sets the value of the field of a loaded record for UPDATE, as long as it does not conflict with the loaded where list or auto_increment primary key. Text values set here will not be escaped at this level (this is done when the update method is called.) Note 1) MySQL/MariaDB could have case sensitive field names, so you MUST match the case of the loaded field names or the method will return a fatal error. Note 2) This method will set the data as it is provided, but escaping will only happen on update or insert for text values Note 3) The method will raise a fatal error if you attempt to: - Update the primary key value (if a primary key is present) - Update values that are set in the WHERE clause, as these are needed to identify the correct record to update - Set a value that does not match the field's type (numerical or text)

 * **Parameters:**
   * `$fieldName` — `mixed` — 
   * `$fieldValue` — `mixed` — 
 * **Returns:** `void` — 

## `public function failIfTableStructureNotLoaded()`

Check if there is a table described and loaded into the class, and fail with a fatal error if not.

# `class recordReadUpdate extends singleRecordCommon`

Loads a known record from the database, and makes it available for reading, field by field, but also allows for updating those loaded fields which can then be written back to the database. with the new values. Note that if the record is read from a table with a Primary Key, then all the fields can be updated except the Primary Key as it allows for atomic updating with only one identification key. If the record does not have a Primary Key, then the update will need to be based from the WHERE conditions used to read that unique record, and those fields will not be updatable.

## `public function addWhere($field, $whereValue)`

Generate "field = value" pairs to be used in an SQL "where" clause. The field must exist, and the value must match the field's datatype. Strings sent will be automatically sql escapaed.

 * **Parameters:**
   * `$field` — `mixed` — Field to be used as a condition in a where clause
   * `$whereValue` — `mixed` — Value linked to the field in the where clause. Text data will be escaped automatically.
 * **Returns:** `void` — Method will generate a fatal error if you try to set a where clause on a non existing field or use the incorrect data type.

## `public function loadOneRecord()`

Read a record from the database, taking the filter conditions into account that have been set through the addWhere method, if any. If the query does not return EXACTLY one record from the system, there will be a fatal error: The method does not force a limit 1, as if more than one record is returned, you have no guarantee that you are working on the correct record, and so the method will error out if your where conditions are not sufficiently precise, and if you have zero records, then you have nothing to do and there is a problem with your query or your database table.

 * **Returns:** `void` — 

## `public function updateRecord()`

Take the field values from the tableStructure array and update the values in the database, either from the PK or from the where condition.

 * **Returns:** `void` — 

# `class recordNew extends singleRecordCommon`

Inserts a new record into the loaded table, that must respect the table's data format.

## `public function insertNewRecord()`

Insert a new record into the loaded table with data added via the setField method. Data will be checked against the expected data type from the table, and returned escaped and quoted to build the insert query. If the class has identified a Primary Key Auto Increment, then this field will be automatically ignored as the DB will auto-fill it

 * **Returns:** `mixed` — ID of the inserted auto-increment record if available.
