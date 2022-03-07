<?php
/**
 * RedSea static string manupulation functions, usable by anyone, not just internally by RedSea.
 * @author Daniel Page <daniel@danielpage.com>
 * @copyright Copyright (c) 2021, Daniel Page
 * @license Licensed under the EUPL v1.2 - https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace RedSea;

/**
* Static string helper class & db functions
*/
class str {

    /**
     * Escape text string for use in an SQL query by replacing single quotes by double quotes.
     * @param string $stringToEscape String containing data to be used in an SQL statement
     * @return string SQL quoted string.
     */
    public static function escapeSQL($stringToEscape) {
        return str_replace("'", "''", str_replace("'", "''", $stringToEscape));
    }

    /**
     * Check that text values are actually text before using the value in a query.
     * @param string $value String to check
     * @return boolean True if string, false otherwise.
     * If the value is true, then the string must be escaped before using in a db query.
     * If false, then the value is not of the expected type.
     */
    public static function checkString($value) {
        if(is_string($value)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Check that number values are actually text.
     * It uses the is_numeric function that will check any numerical type (int, float, bool..)
     * @param numerical $value Numerical value to check
     * @return boolean True of numerical, false otherwise.
     * If a numerical value evaluates as false then it must not be used in a query
     */
    public static function checkNumerical($value) {
        if(is_numeric($value)) {
            return true;
        } else {
            return false;
        }
    }


    /**
     * Get a specific number of characters from the left side of a string.
     * @param string $str Source string
     * @param string $len Number of characters to return from the left of the string
     * @return bool|string On success, returns the specified left part of the string
     * On error, returns false.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    static function left($str="", $len="") {

        if(strlen($len) == 0) {
            return false;
        } else if(!is_numeric($len)) {
            return false;
        } else if($len == 0) {
            return false;
        } else {
            //We have work to do.
            return substr($str, 0, $len);
        }
    }

    /**
     * Get a specific number of characters from the right side of a string (starting from the end).
     * @param string $str Source string
     * @param string $len Number of characters to return from the right of the string
     * @return bool|string On success, returns the specified right part of the string
     * On error, returns false.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    static function right($str="", $len="") {

        if(strlen($len) == 0) {
            return null;
        }

        if(!is_numeric($len)) {
            return null;
        } else if($len == 0) {
            return null;
        } else {
            //We have work to do.
            return substr($str, 0-$len);
        }
    }

    /**
     * Get a specific number of characters from the center of a string. Is here for coherency.
     * with the left() and right() methods. This is a skeleton for the substr() standard function.
     * @param string $str Source string
     * @param string $start Position in the string to start reading from
     * @param string $len Number of characters to return from the start position
     * @return bool|string On success, returns the specified left part of the string
     * On error, returns false.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    static function mid($str="", $start=null, $len=null) {
        return substr($str, $start, $len);
    }
}

?>
