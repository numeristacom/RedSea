# RedSea
PHP/HTML content separation and templating library

This PHP library allows the use of external HTML files to design and display a template that includes blocks of code from other HTML files and replace variable placeholders in the HTML code by calculated content (for example, results of a database query) either as text, or into HTML tags such as form elements.

The system defines a series of classes to allow loading of these template and "ressource" files (HTML files that are designed with divs with correct layout, where RedSea can take these divs and place them in the final template for rendering), a series of static and classic methods to assist in processing data and on-the-fly generation of HTML components such as form elements and divs to allow the customisation of tables and list boxes.

# Version History
- RedSea up to and including v1 - 2005  
Initially designed as a simple function library to integrate HTML pages created by a graphics design partner, to avoid "wordpress" style code where HTML and PHP were intimately linked, allowing my partner as a graphics expert to focus on his task, and me as a backend engineer to focus on the code, and only "merging" the result when needed, replacing placeholder variables set in the HTML page with content pulled from a database.  
This code underpinned the initial bonhommedebois.com french toy retailer and sodistri.fr professional photo material distributor.

- 2009 - v2  
Object Oriented update, extending the features to include "template ressources" which are complete HTML files where RedSea could pull pre-defined div's with styles and insert them "à la CMS" into the template "frame" to be displayed, and included database classes mimicing ADO / PDO to run against MySQL, SQLite and SQL Server databases. Used for multiple internal tools for Marie Claire group and a handful of personal web projects.

- 2012 - v3  
The RedSea Monster. A complete refactoring of v2 to be fully object oriented, replacing all non-OO functions, and interfaces to each class method. Ending up with almost 20k lines of code, it was too complex to maintain and debug, despite complete phpDocumentor documentation, written with the least amount of module dependancies. 

- 2021 - v4  
The Re-Write. Taking the original ideas of using placeholder variables and template ressources, fully Object Oriented, but with a simpler internal design, no interfaces as redundant for the current needs, dropping the database abstraction layer, and adding in direct creation of server-side HTML components, especially form components with methods that aid the creation of selection boxes & content. Requires the PHP DOM module.

- 2022 - Lite  
Having everything was too much and needed to be pruned.  
For personal projects, having getters and setters integrated in one overloaded function was not as elegant in the code as it could have been, and added complexity, the template function with the possibility of loading subtemplates to extract headers on the fly has been simplified, and the debug static class was simplfied.

# Included Library Files
The RedSea Lite library itself comprises of the following components:

- *`debug.inc.php`* Debug and execution flow services. All the components use debug services here, either to denote program flow, report on errors or die horribly, but providing you hopefully with enough information to debug your application. A debug level can be progressively increased to show more program details.
- *`form.inc.php`* HTML form management. Allows the definition of form elements, attributes and events in PHP.
- *`html.inc.php`* HTML (non form) tag management, allowing for the generation of any HTML tags with associated events, modifiers and attributes in PHP.
- *`mariadb.inc.php`* MariaDB PDO helper, easing connection and basic query management.
- *`sqlite3.inc.php`* Sqlite3 PDO helper.
- *`string.inc.php`* Static string helper classes, including SQL text and numerical vetting on data to be used in SQL queries to mitigate SQL injection.
- *`template.inc.php`* Template engine, allowing to load a defined HTML file, set values for placeholders, and render the completed page.
- *`timer.inc.php`* Simple static timer class to record a time duration between start and end events.

# Using RedSea

## Setup 
Unzip the code unto a folder. As long as the code is in the unzipped folders, just include the `redsea.inc.php` file, which will set up the required constants and uncomment the library files you require. Do not comment out the `debug.inc.php` class, as all other RedSea libraries depend on it.

## Documentation
Each class and method are documented with PHPDoc, similar to javadoc. In short, a comment block before each method, with a description of the method, the description of the arguments and the return values, if any. 

## Test Suite
Apart from the MariaDB examples that need to connect to a MariaDB database, the other examples can run stand alone. The test suite is in the `tests` directory where each function in the library is tested out and should work on any PHP 8.x capable web server.

## Examples

