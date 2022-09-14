File ``db.inc.php``

This file defines MariaDB/MySQL and SQLite PDO helper methods. These are based on the PHP PDO library. You can "roll your own" with RedSea and PDO (or whatever other DB you choose) - the aim of this library was to encapsulate some of the verbosity of PDO, integrate the RedSea debug library, and add some features to allow manupulation of full single table records in an object that validates the expected data types against the database table rather than defining your own queries.

- **Namespace**: RedSea

- **Uses**: PDO



# Class ``rdsb``

RedSea Database helper class. Based on PDO, this class simplifies connections to SQLite and MariaDB, and provides methods to execute direct SQL queries without returning a result, return a result set for parsing, and can return the current PDO connection as a standalone object so it can be used outside the class.

## Class Prototype

```
$object = new RedSea\rsdb(
	string $dbtype,
	string $dbname,
	string $host=null,
	string $username=null,
	string $password=null,
	?int $port=3306
);
```

## Properties

### ``public $insertId = null;``
The ID of the last insert id taken from an insert query.

### ```public $selectedDbType = null;```
Contains the selected database type as taken from the PDO Data Source Name. It can be used to identify if the object is working on a MariaDB or an SQLite database.

## Method list

### ``$object->__construct()``
#### Prototype
```
__construct(
	string $dbtype,
	string $dbname,
	?string $host=null,
	?string $username=null,
	?string $password=null,
	?int $port=3306
)
```


#### Parameters

- String ``$dbtype``: Database type to connect to: mariadb or sqlite.

- String ``$dbname``: Name of the data base to open. For an SQLite database, it wil be the path and name of the SQLite database file.

- String ``$host``: Hostname or IP address of the database server to connect to. Not required for SQLite database.

- String ``$username``: Username credential for the database. Not required for SQLite database.

- String ``$password``: Password credential for the database. Not required for SQLite database.

- Int ``$port``: If not specified, the MariaDB default port 3306 will be used, but can be specified to any other number.


#### Return values
The constructor does not return a value. A fatal error will be raised on error.

---

### ``execute()``
Executes a single query on the database that does not return a result object.

#### Prototype
```
execute (
	string $sql
)
```

#### Parameters
- String ``$sql``: SQL query to execute.

#### Return values

- ``true``on success. In addition:
 -  The number of records affected by the query can be read through the public property ``$affectedRecords``.
 -  If the query inserted a record into a table with an autonumber primary key, that, that autonumber can be read through the public property ``$insertId``.

- ``false``on failure. In addition:
 - ``RedSea\debug::$errorFlag`` will be set to ``true``.
 - You can get the stored error message with the ``debug::getLastError()`` static method.

---  

### ``query()``
Runs a query on the database that returns a result set.

#### Prototype
```
query (
	string $sql
)
```

#### Parameters
- String ``$sql``: SQL query to execute.

#### Return values

-  `On success, a PDO result will be returned.

- ``false``on failure. In addition:
 - ``RedSea\debug::$errorFlag`` will be set to ``true``.
 - You can get the stored error message with the ``debug::getLastError()`` static method.

---

# Class ``recordset``

This is wrapper for result sets, notably coming from the ``rsdb`` class ``query()``method, allowing consistant error reporting through the debug class, allows counting the number of results returned in a query result and avoids script overloading when working on a large result set by returning data record by record.

## Class Prototype
```
$object = new RedSea\recordset(
 object $dbResult
)
```

## Properties

### ``public $end = false;``
Defines if the recordset has reached the final returned record.

## Methods 

### ``__construct()``

#### Prototype
```
__construct(
	object $dbResult
)
```

#### Parameters
- object ``$dbResult`` PDO result object from a query.

#### Return value
- ``true``on success.

- ``false``on failure. In addition:
 - ``RedSea\debug::$errorFlag`` will be set to ``true``.
 - You can get the stored error message with the ``debug::getLastError()`` static method.

---

### ``fetchArray()``
Controls how the next row of the result set be returned to the caller.

#### Prototype
```
fetchArray(
	?const $recordType=PDO::FETCH_ASSOC
)
```

#### Parameters
- Optional constant ``$recordType``: Default ``PDO::FETCH_ASSOC``. This value must be one of either ``PDO::FETCH_ASSOC``, ``PDO::FETCH_NUM``, or ``PDO::FETCH_BOTH``.
 - ``PDO::FETCH_ASSOC``: returns an array indexed by column name as returned in the corresponding result set.
 - ``PDO::FETCH_NUM``: returns an array indexed by column number as returned in the corresponding result set, starting at column 0.
 - ``PDO::FETCH_BOTH``: returns an array indexed by both column name and number as returned in the corresponding result set, starting at column 0

#### Return values:
- On success, array containing the contents of the current record

- On error, ``false``. In addition:
 - ``RedSea\debug::$errorFlag`` will be set to ``true``.
 - You can get the stored error message with the ``debug::getLastError()`` static method.

---

### ``recordCount()``
Return the record count from a PDO result set.

#### Prototype
```
recordCount()
```

#### Return values
- On success, the integer number of records queried.

- False if no records are returned.

---

# Class ``singleRecordCommon``
Class that contains the common data and methods for full single record operations.

Single Record Operations are defined as full record inserts, or full record updates to a specific table, generally based of form entered data.

This class requires a pre-opened RS DB object and the name of the table to describe as parameters to the constructor.

Note: Your table may have a unique fields. This class does not manage these. It will only manage data types, primary key auto increment and not null.

## Methods

### ``__construct()``

#### Prototype
```
```

#### Parameters

#### Return values

-- 

### ``describeTable()``

#### Prototype
```
```

#### Parameters

#### Return values

-- 

### ``getField()``

#### Prototype
```
```

#### Parameters

#### Return values

-- 

### ``escapeQuoteValueByType()``

#### Prototype
```
```

#### Parameters

#### Return values

-- 

### ``setField()``

#### Prototype
```
```

#### Parameters

#### Return values

-- 

### ``failIfTableStructureNotLoaded()``

#### Prototype
```
```

#### Parameters

#### Return values

-- 

# Class ``recordReadUpdate extends singleRecordCommon``

Loads a known record from the database, and makes it available for reading, field by field, but also allows for updating those loaded fields which can then be written back to the database with the new values.

Note that if the record is read from a table with a Primary Key, then all the fields can be updated except the Primary Key as it allows for atomic updating with only one identification key.

If the record does not have a Primary Key, then the update will need to be based from the WHERE conditions used to read that unique record, and those fields will not be updatable.

## Methods

### ``__construct()``

#### Prototype
```
```

#### Parameters

#### Return values

-- 

### ``addWhere()``

#### Prototype
```
```

#### Parameters

#### Return values

-- 

### ``loadOneRecord()``

#### Prototype
```
```

#### Parameters

#### Return values

-- 

### ``updateRecord()``

#### Prototype
```
```

#### Parameters

#### Return values

-- 


# Class ``recordNew extends singleRecordCommon``

Inserts a new record into the loaded table, that must respect the table's data format.

### ``__construct()``

#### Prototype
```
```

#### Parameters

#### Return values

-- 

### ``insertNewRecord()``

#### Prototype
```
```

#### Parameters

#### Return values

-- 

# About

Author: Daniel Page

Copyright (c) 2022, Daniel Page

[Licensed under the EUPL v1.2](https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12)