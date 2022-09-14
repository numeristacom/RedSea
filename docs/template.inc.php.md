File ``template.inc.php``

This file defines the core templating management components of the RedSea Class Library

- **Namespace**: RedSea

# Class ``template``

The template class is the master object that contains HTML for output.

You can load a template HTML file, and you will can set variables or replace div elements by their HTML DOM ID from external HTML library files.

A library file is another HTML file that you will pick up to extract and customise content to be merged with the template loaded into the constructor.

You can add variable placeholders to the template, defined by you in enclosed in double square brackets, for example [[MyVar]], and replace these variables with data via class methods.

When you render the output, all these values will be compiled and the HTML template will rendered with all values you set.

Note that div ID's must be unique otherwise the lookup will generate errors.
 
If you include variable placeholders with the same in the template or in any external ressources, when the output is rendered, they will all be replaced with the last value set for that variable placeholder.

## Class Prototype

```
$object = new RedSea\template($templatePath);
```

## Method list

### ``$object->__construct()``
#### Prototype
```
__construct(
	string $templatePath = null,
	?string $variableDelimiter = '*'
)
```


#### Parameters

- String ``$templatePath``: Path to the HTML template to load into the class. Default: ``null``.
- String ``$variableDelimiter``: Delimiter for the variable placeholders. Default: ``*``.

#### Return values
- ``true``on success
- ``false``on failure. In addition, error details can be obtained by calling the static debug and error handling method ``debug::getLastError()``

---

### ``$object->set()``
#### Prototype
```
set(
	string $variableName, 
	?mixed $variableValue=null, 
	?bool $appendValue=false)
```

Set a template's placeholder variable.

#### Parameters
- String ``$variableName``: Exact name of the placeholder variable to set.
- String ``$variableValue``: Value to be set.
- Boolean ``$appendValue``:
 - If ``false`` the placeholder will set or overwrite any previous value
 - If ``true`` the placeholder will be appended to any previous value.

#### Examples
Set or overwrite a placeholder value:

``$templ->set('name', $firstName);``

Append a value to an existing placeholder:

``$templ->set('name', ' ' . $lastName, true);``

---
### ``$object->get()``
#### Prototype

```
get(
	string $variableName
)
```

Get the current value set for a template placeholder.

#### Paramters
- String ``$variableName``: Name of the placeholder value to return

#### Return values
- On success, the value of the specified variable name is returned
- ``false`` on failure. In addition, error details can be obtained by calling the static debug and error handling method ``debug::getLastError()``

#### Examples
Getting a placeholder:

``$p = $tmpl->get('name');``

Checking for an error:

```
$p = $tmpl->get('NotHere');
if($p == false && debug::$errorFlag) {
   echo debug::getLastError();
} else {
   echo $p;
}
```

---
### ``$object->unset()``
#### Prototype
```
unset(
	string $variableName
)
```

This method unsets a specified placeholder variable.

Note that after unsetting a set placeholder variable, the corresponding placeholder in the HTML template will be rendered as defined in the source. If you need to mask a placeholder, use the ``set()`` method to set the variable to an empty string or render using the option to render the template, blanking out unused variables.

#### Parameters
- String ``$variableName`` Name of placeholder variable to unset.

#### Return ``bool``
This method always returns true, even if there was no variable to unset.

#### Examples
Removing a placeholder called ``name``:

``$tmpl->unset('name');``

---
### ``$object->getElementById()``
#### Prototype
```
getElementById(
	string $elementID, 
	string $PathToFileContainingElementID,
	?bool $onlyInnerHTML=false);
```

Get an HTML div element from a specified HTML file, and return the outer or inner HTML of the corresponding element, for example, extracting a header or footer from a standalone file to be integrated into a loaded template.

If caching is enabled, the method will store the rendered element in the RedSea cache as part of the pre-rendered cached template.


#### Parameters

- String ``$elementId`` The unique HTML ID of the ``div``tag you want to extract from a specified file.
- string ``$pathToFileContainingElementID`` Path to the HTML file containing a specific ``div`` element to extract.
- optional boolean ``$onlyInnerHTML`` Defines if the inner HTML (the code actually in between the specified div tags) is to be extracted, or if the whole tag including the ``div`` is to be extracted. By default, the complete tag and the code it contains will be returned.

#### Return values

- On success, a string containing the matching ``div`` element will be returned.
- ``false`` on failure. In addition, error details can be obtained by calling the static debug and error handling method ``debug::getLastError()``

#### Examples
Extracting a ``div`` and inserting the result into a placeholder to be used in later template rendering:

```
$div = $tmpl->getElementById('header', '/foo/bar/header.html', true);
if($div == false && debug::$errorFlag) {
   echo debug::getLastError();
} else {
   $tmpl->set('headerblock', $div);
}
```

---
### ``$object->render()``
#### Prototype

```
render(
	?bool $clearUnusedPlaceholders=false
);
```

Render the HTML template, replacing set placeholder values set by the ``set()`` method and returning the rendered template as a string of HTML.


#### Parameters
- boolean ``$clearUnusedPlaceholders``:
 - Default ``false`` and unused placeholders will be left as-is in the rendered source.
 - If ``true`` then any unused placeholders, as identified by the placeholder delimiting system will be replaced by a blank string in the template.


#### Return values

Returns a string containing the rendered HTML source with all transformations and specified placeholders set.
 
 
# About

Author: Daniel Page

Copyright (c) 2022, Daniel Page

[Licensed under the EUPL v1.2](https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12)****