## API

## Classes

* button - This class allows the creation of a button.
	* cache - 
	* debug - Static class providing debug reporting services to the RedSea library and usable in any custom
code that implements this class library
	* GlobalPropertiesAndAttributes - Static class to be used internally by the form (or any) HTML tag generator.
	* input - The HTML form linked tags can be quite complex, and as such this library contains multiple classes,
and one common PHP trait to enable non-inheritance based inclusion of common code into each class.
	* label - This class allows the creation of a label tag, to be associated with certain form elements such as radiobuttons.
	* option - This class allows the creation of individual option tags, that can either be rendered stand-alone or be added into a select box to be rendered as a complete HTML widget.
	* recordNew - Inserts a new record into the loaded table, that must respect the table's data format.
	* recordReadUpdate - Loads a known record from the database, and makes it available for reading, field by field, but also allows for updating those loaded fields which
can then be written back to the database.
	* recordset - wrapper for result sets, including consistant error reporting through the debug class, allows counting the number of results returned in a query result
and avoids script overloading when working on a large result set by returning data record by record.
	* rsdb - RedSea Database helper class. Based on PDO, this class simplifies connections to SQLite,
and provides methods to execute direct SQL queries without returning a result, return a result set for parsing, and
can return the current PDO connection as a standalone object.
	* select - This class allows the creation of a select tag and associated option values (option values can be created seperately if they are
to be inserted as a template placeholder inside an existing select tag in a form).
	* singleRecordCommon - Class that contains the common data and methods for full single record operations.
	* str - Static string helper class & db functions
	* template - The template class is the master object that contains HTML for output.
	* textarea - Generates a text area tag
	* timer - A simple class that starts and stops a timer
	
## button

This class allows the creation of a button.

Note that even if the only obligatory value in the constructor is the text to display, you may also want to add a type attribute before rendering.

## cache





## debug

Static class providing debug reporting services to the RedSea library and usable in any custom
code that implements this class library



## GlobalPropertiesAndAttributes

Static class to be used internally by the form (or any) HTML tag generator.

This will allow attributes and events to be added into the tag classes when needed, but avoid having a full
array list of all global attributes and events copied into every object if they are not needed. This will
allow an "on-demand" creation of attribute and event data avoiding un-necessary procesisng for each attribute
and event that a tag class may use - but probably does not!
As a class defining static methods, this can be called without instanciation, optimising memory use
The downside is that, opposed to a trait, the class cannot know anything implicit about the parent class unless it has
been explicitly sent as an argument - probably a good thing as this enables less coupled code.

## input

The HTML form linked tags can be quite complex, and as such this library contains multiple classes,
and one common PHP trait to enable non-inheritance based inclusion of common code into each class.



## label

This class allows the creation of a label tag, to be associated with certain form elements such as radiobuttons.



## option

This class allows the creation of individual option tags, that can either be rendered stand-alone or be added into a select box to be rendered as a complete HTML widget.



## recordNew

Inserts a new record into the loaded table, that must respect the table's data format.



## recordReadUpdate

Loads a known record from the database, and makes it available for reading, field by field, but also allows for updating those loaded fields which
can then be written back to the database.

with the new values.
Note that if the record is read from a table with a Primary Key, then all the fields can be updated except the Primary Key as it allows for atomic updating
with only one identification key.
If the record does not have a Primary Key, then the update will need to be based from the WHERE conditions used to read that unique record,
and those fields will not be updatable.

## recordset

wrapper for result sets, including consistant error reporting through the debug class, allows counting the number of results returned in a query result
and avoids script overloading when working on a large result set by returning data record by record.



## rsdb

RedSea Database helper class. Based on PDO, this class simplifies connections to SQLite,
and provides methods to execute direct SQL queries without returning a result, return a result set for parsing, and
can return the current PDO connection as a standalone object.



## select

This class allows the creation of a select tag and associated option values (option values can be created seperately if they are
to be inserted as a template placeholder inside an existing select tag in a form).



## singleRecordCommon

Class that contains the common data and methods for full single record operations.

Single Record Operations are defined as full record inserts, or full record updates to a specific table, generally based of form entered data.
This class requires a pre-opened RS DB object and the name of the table to describe as parameters to the constructor.
Note: Your table may have a unique fields. This class does not manage these. It will only manage data types, pk ai and not null

## str

Static string helper class & db functions



## template

The template class is the master object that contains HTML for output.

You can load a template HTML file, and you will can set variables or replace div elements
by their HTML DOM ID from external HTML library files.

A library file is another HTML file that you will pick up to extract and customise content to be merged
with the template loaded into the constructor.

You can add variable placeholders to the template, defined by you in enclosed in double square brackets, for example [[MyVar]],
and replace these variables with data via class methods.
When you render the output, all these values will be compiled and the HTML template will rendered with all values you set.

Note that div ID's must be unique otherwise the lookup will generate errors.

If you include variable placeholders with the same in the template or in any external ressources, when the output is rendered,
they will all be replaced with the last value set for that variable placeholder.

## textarea

Generates a text area tag



## timer

A simple class that starts and stops a timer





--------
> This document was automatically generated from source code comments on 2022-09-03 using [phpDocumentor](http://www.phpdoc.org/) and [cvuorinen/phpdoc-markdown-public](https://github.com/cvuorinen/phpdoc-markdown-public)
