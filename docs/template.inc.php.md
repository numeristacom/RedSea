# `namespace RedSea`

This file defines the core templating management components of RedSea.

 * **Author:** Daniel Page <daniel@danielpage.com>
 * **Copyright:** Copyright (c) 2021, Daniel Page
 * **License:** Licensed under the EUPL v1.2 - https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12

# `class template`

The template class is the master object that contains HTML for output. You can load a template HTML file, and you will can set variables or replace div elements by their HTML DOM ID from external HTML library files.

A library file is another HTML file that you will pick up to extract and customise content to be merged with the template loaded into the constructor.

You can add variable placeholders to the template, defined by you in enclosed in double square brackets, for example [[MyVar]], and replace these variables with data via class methods. When you render the output, all these values will be compiled and the HTML template will rendered with all values you set.

Note that div ID's must be unique otherwise the lookup will generate errors.

If you include variable placeholders with the same in the template or in any external ressources, when the output is rendered, they will all be replaced with the last value set for that variable placeholder.

## `private $variableArray = array()`

Stores the variables and values in memory until they are rendered into the template.

## `private $TemplateContent = null`

Stores HTML template data set by the constructor. This is what will end up being rendered.

## `private $templateVariableDelimiter = null`

Stores the value used to encapsulate template variables.

## `function __construct($TemplatePath = null, $variableDelimiter = '*')`

Class constructor that is set with an HTML template to load and process.

 * **Parameters:**
   * `$TemplatePath` — `string` — Path to the HTML template file that will be used by the class for output.

     Note that this method does not have any debug support.
   * `$variableDelimiter` — `string` — Delimiting character that encapsulates set template variables.

     Default value is *, but this can be changed to any text string. The value must match the symbol

     used for encapsulation in the template. If * is used, then the template variable must be *variable*.
 * **Returns:** `bool` — TRUE on success, FALSE on error.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public function set($variableName, $variableValue=null, $appendValue=false)`

Set a template's placeholder variable.

 * **Parameters:**
   * `$variableName` — `string` — Exact name of the placeholder variable to set.
   * `$variableValue` — `string` — Value to be set.
   * `$appendValue` — `bool` — If FALSE, the placeholder will be set to the value of the

     variable value. If TRUE, the new value will be appended to the current placeholder value

## `public function get($variableName)`

Get the current value set for a template placeholder variable

 * **Parameters:** `$variableName` — `string` — Name of the placeholder to return
 * **Returns:** `mixed` — current value of the placeholder. If False then the placeholder is not set

     You can check the debug::getLastError() static method to get more details.

## `public function unset($variableName)`

This method unsets a specified placeholder variable. Note that after unsetting a set placeholder variable, the corresponding placeholder in the HTML template will be rendered as defined in the source. If you need to mask a placeholder, use the var() method to set the variable to an empty string (''). This method always returns true, even if the variable does not exist.

 * **Parameters:** `$variableName` — `string` — Name of placeholder variable to unset.
 * **Returns:** `true` — This method always returns true, even if there was no variable to unset.
 * **See also:** template::var()

## `public function getElementById($elementID, $PathToFileContainingElementID, $onlyInnerHTML=false)`

Get an HTML element identified by it's ID attribute from a ressource content loaded by loadHtmlRessource

 * **Parameters:**
   * `$elementID` — `string` — The ID value of the attribute that you want to extract.
   * `$PathToFileContainingElementID` — `mixed` — Path and name of the HTML file containing the elements to load
   * `$onlyInnerHTML` — `bool` — If true, only the contents inside the tag identified by ID will be returned,

     but not the openign and closing parent tag identified by the ID. If False, the complete tag and it's contents

     will be returned. Default false.
 * **Returns:** `string|false` — On sucess, the HTML code of the requested element will be returned, or FALSE on error.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public function render($clearUnusedPlaceholders=false)`

Render the HTML template, replacing set placeholder values set by the set() method.

 * **Parameters:** `$clearUnusedPlaceholders` — `bool` — Remove any placeholders from the template at render time that may not have been set. Default: False
 * **Returns:** `string` — HTML output
 * **See also:** template::var()