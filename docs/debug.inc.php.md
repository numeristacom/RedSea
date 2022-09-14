File ``debug.inc.php``

This file defines internal debug services to the RedSea class library.

- **Namespace**: RedSea

# Static class ``debug``


The debug library contains processes to display program flow, debug information and fatal error management.

It contains static class providing debug reporting services to the RedSea library and is also usable in any custom code that implements the RedSea class library.

As a class containing exclusively static methods, the class does not require instanciation.

### Public properties

#### ``public static $errorFlag = false``

Contains the class error flag. Default value is ``false``. If an error message is stored in the ``$lastErrorMessage`` property by the ``setErrorMessage()`` method, this property will be set to ``true``.

#### ``RedSea\debug::$debugLevel = 0;``

Contains the class debug level:
- 0: All debug features deactivated
- 1: Program flow and message output of ``debug::err()`` methods are displayed to screen
- 2: Debug level 1 + dump of all optional extra mixed values (strings or arrays) displayed to screen.

#### ``RedSea\debug::$lastErrorMessage = null``

Contains the last error message that was stored in the class. When set via the ``debug::setErrorMessage()`` method, the ``$errorFlag`` property will be reset to ``true``. When returned and cleared by the ``debug::getLastError()`` method, the value is cleared and the ``$errorFlag``property will be reset to ``false``.

## Method list



### ``debug::flow()``
#### Prototype
```
RedSea\debug::flow(
	?optional string $message,
	?$optional mixed $extraData,
	?optional int $backupTraceLevels = 0,
	?optional bool $returnFlow = false
)
```

#### Parameters

If none of the optioanl parameters are set, and debug mode is non-zero, then the static class will just return the name and line of the function that called ``debug::flow()``.

- optional string ``$message``: Defines a string to display as an error message along with program flow.
- optional mixed ``$extraData``: Extra information to be displayed as a response. This information may be scalar (strings, integers) or superscalar (arrays, objects...) that would.
- optional int ``$backupTraceLevels``: This value sets how far back flow needs to check in the stack trace to get to obtain the source calling function or method, as some of the calls may be indirect throughout the library. By default: ``0``
- optional bool ``$returnFlow``: Indicates to the method if the program flow data is to be directly output to screen/browser or returned as a string to a calling function: This is useful when you want to capture debug flow information, but want to log the details to a logfile or system log, rather than display directly to screen.

#### Return values

The method depends on both the supplied arguments and the value of the public class property ``$debugLevel``. 

If ``$debugLevel`` is zero, the class does nothing, and returns nothing. 

If ``$debugLevel``is non-zero, then the return value depends on the value of the ``$returnFlow`` parameter:

- If ``true`` then debug data will be returned to the calling function or method
- If  ``false`` then debug data will be directly output to screen.

#### Examples

**Viewing program flow**

Calling ``flow()`` from a function called ``myFunction();`` from a file called ``test.php`` that includes the debug library set to a debug level other than 0:

```
RedSea\debug::$debugLevel = 1;
RedSea\debug::flow();
```

This xill output to screen the indication that the function ``myFunction()`` called ``flow()``from line 9 of the file ``test.php``

```
myFunction() - /path/to/test/test.php:9
```

If the debug level was set to 0, then no program flow will be displayed.

---

### ``debug::err()``

Method used to store the latest error message generated, and if debug mode is enabled, output the error and the current location of the error message in the calling code to screen.

#### Prototype
```
RedSea\debug::err(
	string $message,
	?mixed  $extraDetails
)
```
#### Parameters
- String ``$message``: Error message to store in the class, and to be returned with the ``RedSea\debug::getLastError()`` method, and sets the class error flag ``$errorFlag`` to ``true``.
- Optional Mixed ``$extraDetails``: Any extra details that need to be stored, and potentially output depending on the current debug mode value. These will be output by the PHP ``var_dump()`` function, and can be any data type (string, integer, array, object...).

#### Return values

This method does not return any data. Program execution is immediately stopped after displaying and/or logging the error details.

#### Example

**Log a simple error:**

This will store an error message in the static class, to be accessed with the ``RedSea\debug::getLastError()`` method. If extended debugging is enabled (setting the debug level higher than 0), then this will also be displayed on screen including the file, line number and name of the calling function or method.

```
RedSea\debug::err("You have not entered a value");
```
	
**Log an error with extra details**

In addition to the example above, if extended debugging is enabled (setting the debug level higher than 0), then the class will display this error along with a dump of the values in the second argument.

```
RedSea\debug::err("You have not entered a value", $array('Field', $value));
```

---

### ``debug::fatal()``

This static method handles fatal errors that appear in the code and stops program execution. If extended debug mode is enabled, then the full fatal error message will be displayed on screen. 

If extended debug is disabled (set to 0) then only an error code, based on the current server time, will be displayed, to avoid providing errors that could expose the inner working of the code to a user, but details will be logged, depending on the current execution context:

- In CLI mode, fatal errors are logged to a file called ``debug.log``, that will be created or appended to in the current directory of the running script.
- In Web Server mode, the errors will be logged to the webserver's default log.

The logged errors will contain the same error code based on the server time, so you can match a logged error against a reported issue.

#### Prototype

```
debug::fatal(
	string $message,
	?mixed $extraDetails
)
```

#### Parameters

- String ``$message``: Message corresponding to the fatal error.
- Optional mixed ``$extraDetails``: Extra details that will be dumped.

#### Return values

This method does not return any data. Program execution is immediately stopped after displaying and/or logging the error details.

#### Examples

**Fatal error when debug mode is set to 0**

```
RedSea\debug::fatal('Missing information in field');
```

This will return the following output:

```
FATAL-1662067057.6844
```

The full error details are available either in the server log or in the ``debug.log`` file in the current script directory. The error will be referenced with the same code ``FATAL-1662067057.6844``, where you can find the calling script, line and calling function or method that contained the fatal error call:

```
----------------------------------------------------------------------
FATAL-1662067057.6844 - Fatal error: Something went wrong
myFunction() - /foo/bar/tests/test.php:16
Extra details:
NULL
```

**Fatal error with extra details contained within an array:**

In the previous example, extra details were displayed as ``NULL`` as none were provided. If you provide a value for extra details these will be dumped.

In this example, we will set the debug level for direct screen output rather than sending the details to a logfile:

```
RedSea\debug::$debugLevel = 1;
$arr = array('key1' => 1, 'key2' => 'value 2');
RedSea\debug::fatal('Something went wrong', $arr);
```

The output of this snippet would be:

```
----------------------------------------------------------------------
FATAL-1662067404.0983 - Fatal error: Something went wrong
myFunction() - /foo/bar/tests/test.php:10
Extra details:
array (
  'key1' => 1,
  'key2' => 'value 2',
)
Program halted
----------------------------------------------------------------------
```

---

### debug::getLastError()

Gets the last error message set in the static class, and returns it, clearing the ``$errorFlag`` flag.

#### Prototype

```
debug::getLastError(
	bool $preserveErrorMessage
);
```

#### Parameters

- Optional boolean ``$preserveErrorMessage``: If ``true``the error message and error flag will be preserved in the class. If ``false``, the error message and flag will be reset. Default: ``false``.

#### Return values

If an error message is set, it will be returned. If no error message is set, it will return null.

#### Examples

**Checking an error value if present**

```
if(RedSea\debug::$debugLevel) {
	//Only get the last error if it's true
	echo RedSea\debug::getLastError();
}
```
 
# About

Author: Daniel Page

Copyright (c) 2022, Daniel Page

[Licensed under the EUPL v1.2](https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12)