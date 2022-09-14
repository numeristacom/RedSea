RedSea static string manupulation functions, usable by anyone, not just internally by RedSea.

- **Namespace**: RedSea

# Class ``str``


## Static Methods

### ``sqlString()``
Check that provided value is numerical or boolean for use in an SQL query, and replace by null if not. Apostrophes will be automatically escaped.

#### Prototype
```
RedSea\str::sqlString(
	string $stringToEscape
)
```
 
#### Parameters 
- String ``$stringToEscape`` Data to be used in an SQL statement that requires escaping.

#### Return values
- SQL quoted string.

---

### ``sqlNumber()``
Check that provided value is numerical or boolean for use in an SQL query, and replace by null if not. This avoids SQL injection of string values into fields that are expecting numerical data.

#### Prototype
```
RedSea::str::sqlNumber(
	numeric $num
)
```
 
#### Parameters 
- Numeric ``$num``: Number to validate. 

#### Return values
- If the value evaluates to numerical, the value will be returned.

- If the value does not evaluate to numerical, it ``null`` will be returned.

---
### ``neutraliseHTTPInjection()``
Remove URL specific characters ( ``:``, ``/``,  ``\``, ``@`` ) from a string to avoid HTTP string injection

#### Prototype
```
RedSea\str::neutraliseHTTPInjection (
	string $str,
	?string $replaceWith = null
)
```

#### Parameters 
- String ``$str``: String to clean.

- Optional string ``$replaceWith``: Replace matching HTTP specific characters from a string with the provided value. By default the value is ``null`` and matching characters will simply be removed from the string.

#### Return values
- String with HTTP specific elements removed.

---
### ``left()``

#### Prototype
```
RedSea\str::left (
	string $str,
	int $len
)	
```
 
#### Parameters 
- String ``$str``: Source string.

- Integer ``$len``: Number of characters to return from the left of the string.

#### Return values
- On success: Returns the specified left part of the string.

- On error: Returns ``false``.

---

### ``right()``
Get a specific number of characters from the right side of a string (starting from the end).

#### Prototype
```
RedSea\str::right (
	string $str,
	int $len
)	
```
 
#### Parameters 
- String ``$str``: Source string.

- Integer ``$len``: Number of characters to return from the right of the string.

#### Return values
- On success: Returns the specified right part of the string.

- On error: Returns ``false``.

---

### ``isGet()``
Check if a value exists in the ``$_GET`` superglobal array, and optionally if it is a numerical value.

#### Prototype
```
RedSea\str::isGet (
	string $variableName,
	?bool $checkNumeric = false
)	
```
 
#### Parameters 
- String ``$variableName``: Name of the GET variable to check.

- Bool ``$checkNumeric``: If true, check if the supplied value is numerical. Default: ``false``.

#### Return values
- If the parameter ``$checkNumeric`` is ``false``:
 - ``true``if the variable is set.
 - ``false``if the variable is not set.
- If the parameter ``$checkNumeric``is ``true``:
 - ``true`` if the variable is set AND contains numeric data.
 - ``false`` otherwise.
---


### ``isPost()``
Check if a value exists in the ``$_POST`` superglobal array, and optionally if it is a numerical value.

#### Prototype
```
RedSea\str::isPost (
	string $variableName,
	?bool $checkNumeric = false
)	
```
 
#### Parameters 
- String ``$variableName``: Name of the POST variable to check.

- Bool ``$checkNumeric``: If true, check if the supplied value is numerical. Default: ``false``.

#### Return values
- If the parameter ``$checkNumeric`` is ``false``:
 - ``true``if the variable is set.
 - ``false``if the variable is not set.
- If the parameter ``$checkNumeric``is ``true``:
 - ``true`` if the variable is set AND contains numeric data.
 - ``false`` otherwise.

---

# About

Author: Daniel Page

Copyright (c) 2022, Daniel Page

[Licensed under the EUPL v1.2](https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12)