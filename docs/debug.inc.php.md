# `namespace RedSea`

This file defines internal debug services to the RedSea class library.

 * **Author:** Daniel Page <daniel@danielpage.com>
 * **Copyright:** Copyright (c) 2021, Daniel Page
 * **License:** Licensed under the EUPL v1.2 - https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12

# `class debug`

# Class ``debug`` Static class providing debug reporting services to the RedSea library and usable in any custom code that implements this class library.

## `public static $errorFlag = false`

Flag is set if an error message has been set and is waiting to be read. - If True: There is a pending error. - If False: There is no error to read.

## `public static $debugLevel = 0`

Stores the state of the debug class. If set to TRUE, then the dbg function will generate output It is set by the setDebugLevel method. Possible values are: - 0: No debug (off) - 1: Application flow: Display notifications as long as there is no other details to display. This is the equivalent of calling d:dbg(); - 2: Display message (if set), method name, file and line but not method argument - 3 Same as 2 but adding calling function arguments.

## `static function flow($message=null, $optionalData=null, $backUpTraceLevels = 0, $returnFlow=false)`

Displays program flow. When no arguments are provided, it can be used to trace through program execution by outputting the calling function/method's name, file and line number. If parameters are set then these details will be output too.

 * **Parameters:**
   * `$message` — `string` — Message to display during program execution flow
   * `$optionalData` — `variant` — Extra data of any sort that can be useful to debug program flow.
   * `$backUpTraceLevels` — `integer` — = The backtrace index to display. 0 is the function itself. 1 (default) is the

     function that called this function, and 2 is another level back, if another function is used to call flow (such as the err method of the debug class)
 * **Returns:** `void` — 

## `static function err($message, $optionalData=null)`

This is a wrapper to the flow method, but will set the class error flag and store it for future processing.

 * **Parameters:**
   * `$message` — `mixed|null` — 
   * `$optionalData` — `mixed|null` — 
 * **Returns:** `void` — 

## `static function fatal($message, $optionalData=null)`

 * **Parameters:**
   * `$message` — `string` — Message to display during program execution flow
   * `$optionalData` — `variant` — Extra data of any sort that can be useful to debug program flow.
 * **Returns:** `void` — 

## `static function setLastError($err=null)`

Set an error message.

 * **Parameters:** `$err` — `string` — Error to store in the class.
 * **Returns:** `void` — 

## `public function getLastError($preserveErrorMessage=false)`

Return the last set error message. If no error message is set, NULL will be returned.

 * **Parameters:** `$preserveErrorMessage` — `bool` — Optional. If TRUE, the error message will not be wiped after returning and will stay in the object.

     If FALSE the value will be reset to NULL after returning. Default FALSE.
 * **Returns:** `string` — String containing the last known error.