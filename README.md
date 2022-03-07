# RedSea
PHP/HTML content separation and templating library

This PHP library allows the use of external HTML files to design and display a template that includes blocks of code from other HTML files and replace variable placeholders in the HTML code by calculated content (for example, results of a database query) either as text, or into HTML tags such as form elements.

The system defines a series of classes to allow loading of these template and "ressource" files (HTML files that are designed with divs with correct layout, where RedSea can take these divs and place them in the final template for rendering), a series of static and classic methods to assist in processing data and on-the-fly generation of HTML components such as form elements and divs to allow the customisation of tables and list boxes.

#Version History

RedSea up to and including v1 - 2005
Initially designed as a simple function library to integrate HTML pages created by a graphics design partner, to avoid "wordpress" style code where HTML and PHP were intimately linked, allowing my partner as a graphics expert to focus on his task, and me as a backend engineer to focus on the code, and only "merging" the result when needed, replacing placeholder variables set in the HTML page with content pulled from a database.
This code underpinned the initial bonhommedebois.com french toy retailer and sodistri.fr professional photo material distributor.

2009 - v2
Object Oriented update, extending the features to include "template ressources" which are complete HTML files where RedSea could pull pre-defined div's with styles and insert them "à la CMS" into the template "frame" to be displayed, and included database classes mimicing ADO / PDO to run against MySQL, SQLite and SQL Server databases. Used for multiple internal tools for Marie Claire group and a handful of personal web projects.

2012 - v3
The RedSea Monster. A complete refactoring of v2 to be fully object oriented, replacing all non-OO functions, and interfaces to each class method. Ending up with almost 20k lines of code, it was too complex to maintain and debug, despite complete phpDocumentor documentation, written with the least amount of module dependancies. 

2021 - v4
The Re-Write. Taking the original ideas of using placeholder variables and template ressources, fully Object Oriented, but with a simpler internal design, no interfaces as redundant for the current needs, dropping the database abstraction layer, and adding in direct creation of server-side HTML components, especially form components with methods that aid the creation of selection boxes & content. Requires the PHP DOM module.

