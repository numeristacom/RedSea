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
        if(!is_null($stringToEscape)) {
            return str_replace("'", "''", str_replace("'", "''", $stringToEscape));
        } else {
            return null;
        }
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

    public static function sqlUpsertValue($value, $primitive) {
        if($primitive == RS_NUM) {
            return str::sqlNumber($value);
        } else {
            return "'" . str::sqlString($value) . "'";
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
     * @param string $varName Name of the variable to get from the superglobal
     * @param const $GetOrPost Define the superglobal to check: RS_GET or RS_POST
     * @param const $expectedDataType What is the expected data type: RS_NUM or RS_STR. If the data type is numerical but does not match
     * the method will generate a fatal error.
     * @param bool $FailIfMissing If true, fatal error if the variable is not present in the defined data type. If false, then the 
     * @return mixed Corresponding value 
     */
    static function getSuperGlobal($varName, $GetOrPost, $expectedDataType, $FailIfMissing=true) {
        $superGlobalValue = null;
        $varFound = false;
        if($GetOrPost == RS_GET) {
            if(isset($_GET[$varName])) {
                $superGlobalValue = $_GET[$varName];
                $varFound = true;
            }
        } else if($GetOrPost == RS_POST) {
            if(isset($_POST[$varName])) {
                $superGlobalValue = $_POST[$varName];
                $varFound = true;
            }
        } else {
            debug::fatal("Unexpected primitive data type provided. Should Be RS_STR or RS_NUM", $expectedDataType);
        }
        
        
        if(!$varFound && $FailIfMissing) { //Die if we didn't find the key in the specified array and FailIfMissing enabled
            debug::fatal("Value not found in specified array matching the specified data type", array($varName, $GetOrPost, $expectedDataType));
        } else if(!$varFound) { // FailIfMissing disabled, but no value. Return null.
            return null;
        } else if($varFound) {    //We have a value
            return $superGlobalValue;
        } else {
            debug::fatal("Something bad happened detecting the value to return or error to handle");
        }
        
    }

    /**
     * @param string $varName Name of the variable to get from the superglobal
     * @param const $GetOrPost Define the superglobal to check: RS_GET or RS_POST
     * @param const $expectedDataType What is the expected data type: RS_NUM or RS_STR. If the data type is numerical but does not match
     * the method will generate a fatal error.
     * @return bool True if the value is present in the superglobal and is of the correct data type, otherwise false.
     */
    static function isSuperGlobal($varName, $GetOrPost, $expectedDataType) {
        $superGlobalValue = null;
        if($GetOrPost == RS_GET) {
            if(isset($_GET[$varName])) {
                $superGlobalValue = $_GET[$varName];
            }
        } else if($GetOrPost == RS_POST) {
            if(isset($_POST[$varName])) {
                $superGlobalValue = $_POST[$varName];
            }
        }
        
        if(($expectedDataType == RS_NUM && is_numeric($superGlobalValue)) || ($expectedDataType == RS_STR && is_string($superGlobalValue))) {
            return true;
        } else {
            return false;
        }
    }

    //Create a quick hidden form element
    static function formHiddenInput($name, $value) {
        return "<input type=\"hidden\" name=\"$name\" value=\"$value\">\n";
    }
}

/**
 * Simple class to help build a basic table.
 */
class qTable {

    private $tableCode = null;
    private $cellList = null;

    public function __construct($id=null, $class=null) {
        $this->tableCode = '<table';
        $this->tableCode .= $this->makeId($id);
        $this->tableCode .= $this->makeClass($class);
        $this->tableCode .= ">\n";
    }

    public function addCell($cellData, $id=null, $class=null, $isTableHeader=false) {
        if($isTableHeader) {
            $tagType = 'th';
        } else {
            $tagType = 'td';
        }
        $this->cellList .= "\t\t<$tagType";
        $this->cellList .= $this->makeId($id);
        $this->cellList .= $this->makeClass($class);
        $this->cellList .= '>' . $cellData . "</$tagType>\n";
    }

    public function appendToRow($id=null, $class=null) {
        $this->tableCode .= "\t<tr";
        $this->tableCode .= $this->makeId($id);
        $this->tableCode .= $this->makeClass($class);
        $this->tableCode .= ">\n" . $this->cellList . "\t</tr>\n";
        $this->cellList = null;
    }

    public function render() {
        return $this->tableCode . "</table>\n";
    }

    private function makeId($id) {
        if(!is_null($id)) {
            return ' id="' . $id . '"';
        } else {
            return null;
        }
    }

    private function makeClass($class) {
        if(!is_null($class)) {
            return ' class="' . $class . '"';
        } else {
            return null;
        }
    }
}
?>