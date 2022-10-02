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
    public static function sqlString($stringToEscape) {
        return str_replace("'", "''", str_replace("'", "''", $stringToEscape));
    }

    /**
     * Check that provided value is numerical or boolean for use in an SQL query, and replace by null if not
     * @param numeric $num Number data to be used in an SQL statement
     * @return string Number if numerical, or null if not.
     */
    public static function sqlNumber($num) {
        if(is_numeric($num)) {
            return $num;
        } else {
            return null;
        }
    }

    /**
     * Remove URL specific characters (: / \ @ ) from a string to avoid HTTP string injection
     * @param string $str String to clean
     * @param string optional $replaceWith replace HTTP specific characters from a string with the provided value. By default: null and matching characters will be removed.
     * @return string Cleaned string 
     */
    public static function neutraliseHTTPInjection($str, $replaceWith=null) {
        return str_replace(":", $replaceWith, str_replace("\\", $replaceWith, str_replace("/", $replaceWith, str_replace("@", $replaceWith, $str))));
    }

    /**
     * Get a specific number of characters from the left side of a string.
     * @param string $str Source string
     * @param string $len Number of characters to return from the left of the string
     * @return bool|string On success, returns the specified left part of the string
     * On error, returns false.
     */
    static function left($str, $len = 0) {

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
    static function mid($str, $start, $len) {
        return substr($str, $start, $len);
    }

    /**
     * Check if a GET value exists and optionally if it is a numerical value.
     * @param string $variableName Name of the GET variable to check.
     * @param bool $checkNumeric If true, check if the supplied value is numerical. Default: False
     * @return bool True if the variable is set and optionaly is also numerical if requested, otherwise false.
     */

     static function isGet($variableName, $checkNumeric=false) {
        if(isset($_GET[$variableName])) {
            $varToCheck = $_GET[$variableName];
            if($checkNumeric == true) {
                if(is_numeric($varToCheck)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
     }

     /**
     * Check if a POST value exists and optionally if it is a numerical value.
     * @param string $variableName Name of the GET variable to check.
     * @param bool $checkNumeric If true, check if the supplied value is numerical. Default: False
     * @return bool True if the variable is set and optionaly is also numerical if requested, otherwise false.
     */

    static function isPost($variableName, $checkNumeric=false) {
        if(isset($_POST[$variableName])) {
            $varToCheck = $_POST[$variableName];
            if($checkNumeric == true) {
                if(is_numeric($varToCheck)) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return true;
            }
        } else {
            return false;
        }
     }
}
?>