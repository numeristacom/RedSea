<?php
/**
 * This file defines the core templating management components of RedSea.
 * @author Daniel Page <daniel@danielpage.com>
 * @copyright Copyright (c) 2021, Daniel Page
 * @license Licensed under the EUPL v1.2 - https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace RedSea;

/**
 * The template class is the master object that contains HTML for output.  
 * You can load a template HTML file, and you will can set variables or replace div elements
 * by their HTML DOM ID from external HTML library files.
 * 
 * A library file is another HTML file that you will pick up to extract and customise content to be merged 
 * with the template loaded into the constructor.
 * 
 * You can add variable placeholders to the template, defined by you in enclosed in double square brackets, for example [[MyVar]],
 * and replace these variables with data via class methods.
 * When you render the output, all these values will be compiled and the HTML template will rendered with all values you set.
 *  
 * Note that div ID's must be unique otherwise the lookup will generate errors.
 * 
 * If you include variable placeholders with the same in the template or in any external ressources, when the output is rendered,
 * they will all be replaced with the last value set for that variable placeholder.
 */
class template {

    /**
     * Stores the variables and values in memory until they are rendered into the template.
     * @internal
     */
    private $variableArray = array();

    /** 
     * Stores HTML template data set by the constructor. This is what will end up being rendered.
     * @internal
     * */
    private $TemplateContent = null;

    /**
     * Stores external HTML ressource files in an array element.
     * @internal
     */
    private $externalRessourceContent = array();

    /**
     * Class constructor that is set with an HTML template to load and process.
     * @param string $TemplatePath Path to the HTML template file that will be used by the class for output.
     * Note that this method does not have any debug support.
     * @return bool TRUE on success, FALSE on error.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    function __construct($TemplatePath = null) {
        d::dbg();
        if(!file_exists($TemplatePath)) {
            d::dbgError("No file found at $TemplatePath");
            return false;
        } else {
            // Here we go!
            $TemplateContent = file_get_contents($TemplatePath);
            if($TemplateContent == false) {
                d::dbgError("Could not read contents of file $TemplatePath");
                return false;
            } else {
                $this->TemplateContent = $TemplateContent;
                return true;
            }
        }
    }

    /**
     * Get or set a template's placeholder variable. 
     * @param string $variableName Exact name of the placeholder variable to be get or set.
     * @param string $variableValue If this value is set, it needs to be added or updated.
     * If the value is null, then the method will return the currently set value for the 
     * placeholder variable defined by $variableName.
     * Note: This means you cannot set NULL as a value which makes no sense for HTML output anyway.
     * If you require a placeholder value to be set as blank, you can set with an
     * empty string - this will cause the placeholder variable to be replaced with "nothing".
     * @param bool $appendValue If FALSE, the placeholder will be set to the value of the
     * variable value. If TRUE, the variable value will be appended to the end of the corresponding
     * placeholder. Default: False.
     * @return mixed Return value depends on arguments:
     * - If no placeholder name submitted, return FALSE.
     * - If a placeholder name is set, but the corresponding value is NULL, then the current value
     * of the placeholder variable is returned. If the placeholder is not set, FALSE will be returned.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag property will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    public function var($variableName, $variableValue, $appendValue=false) {
        d::dbg();           
        if(is_null($variableValue)) {
            if(isset($this->variableArray["$variableName"])) {
                return $this->variableArray["$variableName"];
            } else {
                d::dbgError("No value set for defined variable value", $variableValue);
                return false;
            }
        } else {
            if($appendValue) {
                $this->variableArray["$variableName"] .= $variableValue;
            } else {
                $this->variableArray["$variableName"] = $variableValue;
            }
        }
    }

    /**
     * This method unsets a specified placeholder variable.
     * Note that after unsetting a set placeholder variable, the corresponding placeholder in the HTML template
     * will be rendered as defined in the source. If you need to mask a placeholder, use the var()
     * method to set the variable to an empty string ('').
     * This method always returns true, even if the variable does not exist.
     * @param string $variableName Name of placeholder variable to unset.
     * @return true This method always returns true, even if there was no variable to unset.
     * @see template::var()
     */
    public function unsetVar($variableName) {
        if(isset($this->variableArray["$variableName"])) {
            unset($this->variableArray["$variableName"]);
        }
        return true;
    }

    /**
     * Load an external HTML file containing elements to be extracted for a template.
     * A ressource file contains HTML content identified by ID attributes that can be extracted and
     * used in a parent display template.
     * When a new ressource file is loaded, the method will return a unique hash code that references
     * the element to be used by the getHtmlRessource or unsetHtmlRessource methods.
     * @param string $pathToFile Path to the HTML file to load.
     * If file has not been loaded, it will be read into the object and identified by a unique hash
     * generated from the file name.
     * If the file has been loaded (as identified internally by the file path hash), the data in the
     * object will be refreshed. Note that refreshing the existing ressource will not affect any
     * previously extracted elements by the getElementByID method.
     * @return false|string On success, the method returns a unique hash that identifies the loaded
     * content in the object. On error, return FALSE.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    public function loadHtmlRessource($pathToFile) {
        d::dbg();
        if(!is_file($pathToFile)) {
            d::dbgError("Invalid path to file", $pathToFile);
            return false;
        } else {
            // Generate a hash of the file path, this will be used to identify cached content
            $fileHash = sha1($pathToFile);
            $rcContent = file_get_contents($pathToFile);
            if($rcContent !== false) {
                $this->externalRessourceContent[$fileHash] = $rcContent;
                return $fileHash;
            } else {
                d::dbgError("Error loading file", $pathToFile);
                return false;
            }            
        }
    }

    /**
     * Unloads a loaded ressource from the template object referenced by it's unique hash.
     * @param string $hash Hash referencing the ressource returned by loadHtmlRessource.
     * @return bool TRUE if the ressource was successfully removed. False on failure.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    public function unloadHtmlRessource($hash) {
        if(array_key_exists($hash, $this->externalRessourceContent)) {
            //Delete the element from the hash
            unset($this->externalRessourceContent[$hash]);
            return true;
        } else {
            d::dbgError("Specified hash does not exist", $hash);
            return false;
        }
    }

    /**
     * Get an HTML element identified by it's ID attribute from a ressource content loaded by loadHtmlRessource
     * @param string $elementID The ID value of the attribute that you want to extract.
     * @param mixed $ressourceHashValue The hash value of the ressource element returned by loadHtmlRessourceFile
     * @param bool $onlyInnerHTML If true, only the contents inside the tag identified by ID will be returned,
     * but not the openign and closing parent tag identified by the ID. If False, the complete tag and it's contents
     * will be returned. Default false.
     * @return string|false On sucess, the HTML code of the requested element will be returned, or FALSE on error.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    public function getElementById($elementID, $ressourceHashValue, $onlyInnerHTML=false) {
        d::dbg();
        $extractedContent = false;
        // Loading this from the global namespace. Required as otherwise DOMDocument will be searched only in the 
        // current RedSea namespace, and that will cause a runtime error.
        if(array_key_exists($ressourceHashValue, $this->externalRessourceContent)) {
            $dom = new \DOMDocument;
            $dom->loadHTML($this->externalRessourceContent[$ressourceHashValue]);
            //Get the content, if it exists.
            $ressourceHTML = null;
            $domRessource = $dom->getElementById($elementID);
            if(!is_null($domRessource)) {
                $htmlCode = $dom->saveHTML($domRessource);
                if($onlyInnerHTML) {
                    /* From the returned HTML code:
                    A. Identify the position of the first > character starting from beginning of string (end of initial tag)
                    B. Identify the position of the last < character starting from end of string (start of final tag)
                    C. Extract the string
                        - starting from value in A+1 (next character)
                        - reading from position of value B-1 (previous character) and subtracting A to exclude the start of string
                        - ...
                        - SUCCESS!
                    */
                    $endFirstTag = strpos($htmlCode, '>');
                    $startLastTag = strrpos($htmlCode, '<', -1);
                    return substr($htmlCode, $endFirstTag+1, ($startLastTag - $endFirstTag-1));
                } else {
                    return $htmlCode;
                }
            } else {
                d::dbgError("Could not find id in HTML ressource", $elementID);
                return false;
            }
        } else {
            d::dbgError("Specified hash key does not exist in the external ressource array", $ressourceHashValue);
            return false;
        }
    }

    /**
     * Render the HTML template, replacing set placeholder values set by the var() method.
     * @return string HTML output 
     * @see template::var()
     */
    public function render() {
        d::dbg();
        $htmlOutput = $this->TemplateContent;
        foreach($this->variableArray as $var => $value) {
            d::dbg("Variable: $var - Value: $value");
            $htmlOutput = str_replace($var, $value, $htmlOutput);
        }
        return $htmlOutput;
    }
}
