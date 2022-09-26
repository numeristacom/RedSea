# `namespace RedSea`

RedSea static string manupulation functions, usable by anyone, not just internally by RedSea.

 * **Author:** Daniel Page <daniel@danielpage.com>
 * **Copyright:** Copyright (c) 2021, Daniel Page
 * **License:** Licensed under the EUPL v1.2 - https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12

# `class str`

Static string helper class & db functions

## `public static function sqlString($stringToEscape)`

Escape text string for use in an SQL query by replacing single quotes by double quotes.

 * **Parameters:** `$stringToEscape` — `string` — String containing data to be used in an SQL statement
 * **Returns:** `string` — SQL quoted string.

## `public static function sqlNumber($num)`

Check that provided value is numerical or boolean for use in an SQL query, and replace by null if not

 * **Parameters:** `$num` — `numeric` — Number data to be used in an SQL statement
 * **Returns:** `string` — Number if numerical, or null if not.

## `public static function neutraliseHTTPInjection($str, $replaceWith=null)`

Remove URL specific characters (: / \ @ ) from a string to avoid HTTP string injection

 * **Parameters:**
   * `$str` — `string` — String to clean
   * `string` — $replaceWith replace HTTP specific characters from a string with the provided value. By default: null and matching characters will be removed.
 * **Returns:** `string` — Cleaned string

## `static function left($str, $len = 0)`

Get a specific number of characters from the left side of a string.

 * **Parameters:**
   * `$str` — `string` — Source string
   * `$len` — `string` — Number of characters to return from the left of the string
 * **Returns:** `bool|string` — On success, returns the specified left part of the string

     On error, returns false.

## `static function right($str="", $len="")`

Get a specific number of characters from the right side of a string (starting from the end).

 * **Parameters:**
   * `$str` — `string` — Source string
   * `$len` — `string` — Number of characters to return from the right of the string
 * **Returns:** `bool|string` — On success, returns the specified right part of the string

     On error, returns false.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `static function mid($str, $start, $len)`

Get a specific number of characters from the center of a string. Is here for coherency. with the left() and right() methods. This is a skeleton for the substr() standard function.

 * **Parameters:**
   * `$str` — `string` — Source string
   * `$start` — `string` — Position in the string to start reading from
   * `$len` — `string` — Number of characters to return from the start position
 * **Returns:** `bool|string` — On success, returns the specified left part of the string

     On error, returns false.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `static function isGet($variableName, $checkNumeric=false)`

Check if a GET value exists and optionally if it is a numerical value.

 * **Parameters:**
   * `$variableName` — `string` — Name of the GET variable to check.
   * `$checkNumeric` — `bool` — If true, check if the supplied value is numerical. Default: False
 * **Returns:** `bool` — True if the variable is set and optionaly is also numerical if requested, otherwise false.

## `static function isPost($variableName, $checkNumeric=false)`

Check if a POST value exists and optionally if it is a numerical value.

 * **Parameters:**
   * `$variableName` — `string` — Name of the GET variable to check.
   * `$checkNumeric` — `bool` — If true, check if the supplied value is numerical. Default: False
 * **Returns:** `bool` — True if the variable is set and optionaly is also numerical if requested, otherwise false.
