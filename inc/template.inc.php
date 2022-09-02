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
     * Stores the value used to encapsulate template variables.
     * @internal
    */
    private $templateVariableDelimiter = null;

    /**
     * Class constructor that is set with an HTML template to load and process.
     * @param string $TemplatePath Path to the HTML template file that will be used by the class for output.
     * Note that this method does not have any debug support.
     * @param string $variableDelimiter Delimiting character that encapsulates set template variables.
     * Default value is *, but this can be changed to any text string. The value must match the symbol
     * used for encapsulation in the template. If * is used, then the template variable must be *variable*.
     * @return bool TRUE on success, FALSE on error.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    function __construct($TemplatePath = null, $variableDelimiter = '*') {
        debug::flow();
        if(!file_exists($TemplatePath)) {
            debug::err("No file found at $TemplatePath");
            return false;
        } else {
            // Here we go!
            $TemplateContent = file_get_contents($TemplatePath);
            if($TemplateContent == false) {
                debug::err("Could not read contents of file $TemplatePath");
                return false;
            } else {
                $this->TemplateContent = $TemplateContent;
                $this->templateVariableDelimiter = $variableDelimiter;
                return true;
            }
        }
    }


    /**
     * Set a template's placeholder variable. 
     * @param string $variableName Exact name of the placeholder variable to set.
     * @param string $variableValue Value to be set.
     * @param bool $appendValue If FALSE, the placeholder will be set to the value of the
     * variable value. If TRUE, the new value will be appended to the current placeholder value
     * @return void.
     */
    public function set($variableName, $variableValue=null, $appendValue=false) {
        if($appendValue) {
            $this->variableArray["$variableName"] .= $variableValue;
        } else {
            $this->variableArray["$variableName"] = $variableValue;
        }
    }

    /**
     * Get the current value set in a template placeholder variable
     * @param string $variableName Name of the placeholder to return
     * @return mixed current value of the placeholder. If False then the placeholder is not set
     * You can check the debug::getLastError() static method to get more details.
     */
    public function get($variableName) {
        if(isset($this->variableArray["$variableName"])) {
            return $this->variableArray["$variableName"];
        } else {
            debug::err("No value set for defined variable value", $variableName);
            return false;
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
    public function unset($variableName) {
        if(isset($this->variableArray["$variableName"])) {
            unset($this->variableArray["$variableName"]);
        }
        return true;
    }

    /**
     * Get an HTML element identified by it's ID attribute from a ressource content loaded by loadHtmlRessource
     * @param string $elementID The ID value of the attribute that you want to extract.
     * @param mixed $PathToFileContainingElementID Path and name of the HTML file containing the elements to load
     * @param bool $onlyInnerHTML If true, only the contents inside the tag identified by ID will be returned,
     * but not the openign and closing parent tag identified by the ID. If False, the complete tag and it's contents
     * will be returned. Default false.
     * @return string|false On sucess, the HTML code of the requested element will be returned, or FALSE on error.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     */
    public function getElementById($elementID, $PathToFileContainingElementID, $onlyInnerHTML=false) {
        debug::flow();
        $fileContent=null;

        if(!is_file($PathToFileContainingElementID)) {
            debug::err("Invalid path to external ressource file", $PathToFileContainingElementID);
            return false;
        } else {
            // Check if caching is enabled
            if(cache::$enableContentCaching) {
                $content = cache::getCachedElement(cache::makeElementByIdCacheName($PathToFileContainingElementID, $elementID, $onlyInnerHTML));
                if($content !== false) {
                    return $content;
                }
            }
            //If we get here either caching is disabled or there was a cache miss.
            //Get the content through DOM.
            $fileContent = file_get_contents($PathToFileContainingElementID);
            if($fileContent === false) {
                debug::err("Could read from the specified ressource file", $PathToFileContainingElementID);
                return false;
            } else {
                $dom = new \DOMDocument;
                $dom->loadHTML($fileContent);
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
                        $htmlCode = substr($htmlCode, $endFirstTag+1, ($startLastTag - $endFirstTag-1));
                    } 
                    //Cache the output for future use.
                    if(cache::$enableContentCaching) {
                        $cacheReturn = cache::setCachedElement(cache::makeElementByIdCacheName($PathToFileContainingElementID, $elementID, $onlyInnerHTML), $htmlCode);
                    }
                    //and return the data.
                    return $htmlCode;
                } else {
                    debug::err("Could not find specified ID in HTML ressource", $elementID);
                    return false;
                }  
            }
        }        
    }

    /**
     * Render the HTML template, replacing set placeholder values set by the set() method.
     * @param bool $clearUnusedPlaceholders Remove any placeholders from the template at render time that may not have been set. Default: False
     * @return string HTML output 
     * @see template::var()
     */
    public function render($clearUnusedPlaceholders=false) {
        debug::flow();
        $htmlOutput = $this->TemplateContent;
        foreach($this->variableArray as $var => $value) {
            debug::flow("Variable: $var - Value: $value");
            $htmlOutput = str_replace($this->templateVariableDelimiter . $var . $this->templateVariableDelimiter, $value, $htmlOutput);
        }

        if($clearUnusedPlaceholders) {          
            //$htmlOutput = preg_replace('/\*.*[a-z]\*/', '', $htmlOutput);
            $htmlOutput = preg_replace('/\\' . $this->templateVariableDelimiter . '.*[a-z]\\' . $this->templateVariableDelimiter  . '/', '', $htmlOutput);
        }

        return $htmlOutput;
    }
}
