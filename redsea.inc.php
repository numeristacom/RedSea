<?php
/**
 * RedSea code and content separation and templating library - Version 4
 * This is the main startup file, and is required to set up line endings in a 
 * cross-platform way (*nix/macOS vs Windows), and defines the line breaks for command line or web use,
 * then includes the other RedSea class library files.
 * @author Daniel Page <daniel@danielpage.com>
 * @copyright Copyright (c) 2021, Daniel Page
 * @license Licensed under the EUPL v1.2 - https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * 
 * Note that RedSea will set 2 constants used in it's other classes:
 * RS_EOL defines the End Of Line symbol:
 * - On a command line script it will use PHP's defined End Of Line symbol (\r\n or \n depending on your OS)
 * - In a web/non-CLI environment, it will use the HTML line break <br>
 * RS_HR for a horizonal rule:
 * - On a command line script it will use 70 hyphens
 * - In a web/non-CLI environment, it will use the HTML Horizontal Rule <hr> 
*/

// Setup:

/**
 * Define end of line and horizontal rule constants depending on execution context (CLI or web)
 */

if(php_sapi_name() === 'cli') {
    /**
     * Set End of line constant to PHP_EOL when running on the command line
     * @internal
     */
    define("RS_EOL", PHP_EOL);
    
    /**
     * Set Horizonal Rule constant to 70 hypens when running on the command line
     * @internal
     */
    define("RS_HR", RS_EOL . "----------------------------------------------------------------------" . RS_EOL);
    
    /**
     * Set a constant to identify execution context as Command Line Interface.
     * @internal
     */
    define("RS_CLI", true);

} else {
    /**
     * Set End Of Line constant to HTML <br>
     * @internal
     */
    define("RS_EOL", "<br>");

    /**
     * Set Horizonal Rule constant to HTML <hr>
     * @internal
     */ 
    define("RS_HR", "<hr>");

/**
     * Set a constant to identify execution context as not CLI.
     * @internal
     */
    define("RS_CLI", false);
}

/**
 * Define a cache directory, but don't do anything with it right now.
 * This will be used for including rpe-processed external components from the template getElementByID method 
 */
define("RS_CACHE", __DIR__ . DIRECTORY_SEPARATOR);

//Library autoloader
// debug.inc.php must be set first as all other objects depend on it for debug and execution flow services
include(__DIR__ . DIRECTORY_SEPARATOR . "inc" . DIRECTORY_SEPARATOR . "debug.inc.php");

//cache.inc.php is a dependancy of template.inc.php. 
include(__DIR__ . DIRECTORY_SEPARATOR . "inc" . DIRECTORY_SEPARATOR . "cache.inc.php");
include(__DIR__ . DIRECTORY_SEPARATOR . "inc" . DIRECTORY_SEPARATOR . "template.inc.php");
include(__DIR__ . DIRECTORY_SEPARATOR . "inc" . DIRECTORY_SEPARATOR . "form.inc.php");
include(__DIR__ . DIRECTORY_SEPARATOR . "inc" . DIRECTORY_SEPARATOR . "pdohelper.inc.php");
include(__DIR__ . DIRECTORY_SEPARATOR . "inc" . DIRECTORY_SEPARATOR . "string.inc.php");
include(__DIR__ . DIRECTORY_SEPARATOR . "inc" . DIRECTORY_SEPARATOR . "timer.inc.php");

?> 