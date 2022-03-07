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
     * Render the HTML template, replacing set placeholder values set by the var() method.
     * @return string HTML output 
     * @see template::var()
     */
    public function render() {
        debug::flow();
        $htmlOutput = $this->TemplateContent;
        foreach($this->variableArray as $var => $value) {
            debug::flow("Variable: $var - Value: $value");
            $htmlOutput = str_replace($var, $value, $htmlOutput);
        }
        return $htmlOutput;
    }
}
