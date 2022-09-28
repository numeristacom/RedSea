<?php

//Go and load the setup file that loads the rest.
include("../../redsea.inc.php");

/** Time page execution */
RedSea\timer::startTimer();

/** Load the template that will be used by this code to insert all PHP generated content. */
$template = new RedSea\template('test.html');
if($template === false) {
    die($template->getLastError());
}

/** Disable caching. If you enable it, be sure that the system can write to the defined cache directory */
RedSea\cache::$enableContentCaching = false;

/** Get the div "topmenu" out of the "ressources.html" file and load it into the topMenu variable in the template */
$template->set('topMenu', $template->getElementById("topmenu", "ressources.html"));

/** 
 * Basic use of placeholder variables: The variable name must not collide with any other value that matches, so they
 * are completely encapsulated - in the examples below they are set in the HTML template with asterisks encompassing the name,
 * like *variable name*. This value can be set in the template constructor, but by default it's *
 */
$template->set('title', 'RedSea Technical Demo');

/**
 * This is probably overkill, but this allows some simple text manipulation in a clear way, allowing
 * returning of the left part, right part or middle part of a text string without having to remember
 * the syntax of the php substr function.
 */
$template->set('textManipulationMethods', 'Text Manipulation on the string "RedSea Technical Demo"');
$template->set('leftString', RedSea\str::left('RedSea Technical Demo', 5));
$template->set('rightString', RedSea\str::right('RedSea Technical Demo', 5));
$template->set('midString', RedSea\str::mid('RedSea Technical Demo', 5, 5));

/**
 * You can create HTML form elements in RedSea that can be integrated into an existing HTML form on a template.
 * You can even design some contents to only render their contents rather than their parent tag, so if you have
 * for example an input tag that will display list elements, you can visually design an empty listbox, and style it
 * as you require, then you can get RedSea to generate the elements for that list box and output the value into
 * a variable that is inside the existing tags.
 * You can also just create the whole tag and contents via the HTML form classes as demonstrated below.
 */
$template->set('HTMLFormMethods', 'RedSea HTML Form manipulation');

// Create a new button object
$rsButton = new RedSea\button("Button test");
// Add a new custom event into the object.
// The event name must correpsond to a pre-defined name defined in the form class or it will return false.
// Here we are setting the button's onClick event with a piece of Javascript.
$rsButton->event('onClick', "javacript:alert('Hello World!'); return false;");
// Set the template variable with the object's render method that outputs rendered HTML
$template->set('button', $rsButton->render());

//Create and set up a new textarea
$rsTextArea = new RedSea\textarea('txtarea');
// Set the elements display value (what displays inside the text area as a pre-defined text)
$rsTextArea->setDisplayValue("This is a text area example");
// Set the text area tag's attributes.
// The attribute name must correpsond to a pre-defined name defined in the form class or it will return false.
// The attribute value must match the expected data type of the attribute name so it can be properly rendered.
$rsTextArea->attribute('rows', 5);
$rsTextArea->attribute('cols', 70);
// Set the template variable with the object's render method that outputs rendered HTML
$template->set('textArea', $rsTextArea->render());

// Create and set up a new label
$rsLabel = new RedSea\label("This is a basic unbound label");
$template->set('label', $rsLabel->render());

//The HTML input tag has a lot of different types. 
// Generate one object per variation of the input tag
$rsInputbutton = new RedSea\input('button');
//The button needs a value to display:
$rsInputbutton->attribute("value", "input button");

$rsInputcheckbox = new RedSea\input('checkbox');
$rsInputcolor = new RedSea\input('color');
$rsInputdate = new RedSea\input('date');
$rsInputdatetimelocal = new RedSea\input('datetime-local');
$rsInputemail = new RedSea\input('email');
$rsInputfile = new RedSea\input('file');
$rsInputhidden = new RedSea\input('hidden');
$rsInputimage = new RedSea\input('image');
$rsInputmonth = new RedSea\input('month');
$rsInputnumber = new RedSea\input('number');
$rsInputpassword = new RedSea\input('password');
$rsInputradio = new RedSea\input('radio');
$rsInputrange = new RedSea\input('range');
$rsInputreset = new RedSea\input('reset');
$rsInputsearch = new RedSea\input('search');
$rsInputsubmit = new RedSea\input('submit');
$rsInputtel = new RedSea\input('tel');
$rsInputtext = new RedSea\input('text');
$rsInputtime = new RedSea\input('time');
$rsInputurl = new RedSea\input('url');
$rsInputweek = new RedSea\input('week');


// Set each corresponding template variable with each object's render method
$template->set('Inputbutton', $rsInputbutton->render());
$template->set('checkbox', $rsInputcheckbox->render());
$template->set('color', $rsInputcolor->render());
$template->set('date', $rsInputdate->render());
$template->set('datetime-local', $rsInputdatetimelocal->render());
$template->set('email', $rsInputemail->render());
$template->set('file', $rsInputfile->render());
$template->set('hidden', $rsInputhidden->render());
$template->set('image', $rsInputimage->render());
$template->set('month', $rsInputmonth->render());
$template->set('number', $rsInputnumber->render());
$template->set('password', $rsInputpassword->render());
$template->set('radio', $rsInputradio->render());
$template->set('range', $rsInputrange->render());
$template->set('reset', $rsInputreset->render());
$template->set('search', $rsInputsearch->render());
$template->set('submit', $rsInputsubmit->render());
$template->set('tel', $rsInputtel->render());
$template->set('text', $rsInputtext->render());
$template->set('time', $rsInputtime->render());
$template->set('url', $rsInputurl->render());
$template->set('week', $rsInputweek->render());

/**
 * A HTML select tag can render in 2 ways:
 * - As a drop down list box (one line with a drop down button that displays the values in the list)
 * - As a list area where all the options are displayed in a selectable list.
 * 
 * There are no constructors to be set for a list box but there could be a fair number of attributes
 * as they are a series key=values that define how the select will display.
 */


// Create a simple no-frills drop down list box
 $rsSelectSimple = new RedSea\select();
 // Set the tag's name with unique value - if you are building a form, then you don't want
 //any names to collide with another form element or you will have problems processing the submitted result!
$rsSelectSimple->attribute('name', "simpleselect");
// Generating a simple loop to generate option objects and send them into the select class.
//This example does not require setting any other value than the option's display value and optional internal value
//A more in-detailed example of setting up independant options can be found below.
for($i = 1; $i <= 12; $i++) {
    //Add a new option object to the select object.
    $rsSelectSimple->addOptionObject(new RedSea\option("Month $i", $i));
}

// Set each corresponding template variable with each object's render method 
// The select object will automatically render each option object you added through the addOptionObject method
$template->set('simpleSelect', $rsSelectSimple->render());


// Create a list box - but not a drop down one.
// As we are going to set this select to be multi-select this means that you can send multiple selected items on submit.
// As such you must remember to append [] to the name of the select tag, so that it's selected items will be sent as an 
// array rather than a single value.
$rsSelectBox = new RedSea\select();
//Set the name, with the [] suffix meaning that the tag can submit multiple values. Forget this and you may have
// problems handing your submitted data.
$rsSelectBox->attribute('name', "selectbox[]");
// Set the select tag to multiple select, and 12 rows long.
$rsSelectBox->attribute('multiple', true);
$rsSelectBox->attribute('size', 12);
// Add items to the list box, generated through this loop.
for($i = 1; $i <= 12; $i++) {
    //Add a new option object to the select object.
    $rsSelectBox->addOptionObject(new RedSea\option("Month $i", $i));
}
// Set each corresponding template variable with each object's render method 
// The select object will automatically render each option object you added through the addOptionObject method
$template->set('multiSelect', $rsSelectBox->render());

/**
 * The following examples are very similar to the previous 2 list boxes, with one notable difference: these examples
 * will only generate the option list, but not the the select tag that contains them.
 * This is because the HTML template has already hard coded a "empty" select objects with all necessary
 * attributes pre-defined, but not the actual option values themselves.
 * You can tell the select class to ignore generation of the actual select tags by instanciating the class with a 
 * false argument. From there, add your option objects and render as usual: The corresponding variable in the template
 * is defined inside the hardcoded <select> tag. 
 */

// a new object is instanciated, but explicitly setting the false argument in the constructor.
$rsSelectBodyFill = new RedSea\select(false);
// No other setup of the select tag, as it won't be rendered.
// Set the object's option tags
for($i = 1; $i <= 12; $i++) {
    $rsSelectBodyFill->addOptionObject(new RedSea\option("Month $i", $i));
}

//Now add some extra values using quickAdd
for($i = 100; $i <= 112; $i++) {
    if($i == 105) {
        $rsSelectBodyFill->quickAdd($i, $i, true);
    } else if ($i == 110) {
        $rsSelectBodyFill->quickAdd($i, $i, false, true);
    } else {
        $rsSelectBodyFill->quickAdd($i, $i);
    }
}

// Set each corresponding template variable with each object's render method 
// The select object will automatically render each option object added to the select object
//without rendering the parent <select> tag.
$template->set('bodyFill', $rsSelectBodyFill->render());

/** This is the same exercise as above, with a new select and option object, except the output will be
 * added into a different template variable.
 * Even though the code is almost identical apart from the variable names, as the underlying select tag
 * in the HTML template defines a multi-select list and a submit array, the visual display will be different,
 */


RedSea\debug::flow("Test with debug helper services");
$rsSelectlistBoxBodyFill = new RedSea\select(false);
for($i = 1; $i <= 12; $i++) {
    RedSea\debug::flow("Loop", $i);
    $rsSelectlistBoxBodyFill->addOptionObject(new RedSea\option("Month $i", $i));
}

//Now add some extra values using quickAdd
for($i = 100; $i <= 112; $i++) {
    if($i == 105) {
        $rsSelectlistBoxBodyFill->quickAdd($i, $i, true);
    } else if ($i == 110) {
        $rsSelectlistBoxBodyFill->quickAdd($i, $i, false, true);
    } else {
        $rsSelectlistBoxBodyFill->quickAdd($i, $i);
    }
}


RedSea\debug::$debugLevel = 0;
$template->set('listBoxBodyFill', $rsSelectlistBoxBodyFill->render());

/**
 * Creating a list of linked radio buttons
 * Even though radio buttons are a discrete HTML tag, any radiobutton sharing the same name will work as a
 * collection, allowing only one button in the collection to be selected at a given time, and on submission,
 * only the selected radiobutton's value will be submitted to the form handler.
 * 
 * You can either create your radiobutton collection directly or use the radiobutton helper class.
 */

// Creating your list directly
$rbRender = null;
// Loop.
for($i = 1; $i<= 10; $i++) {
    //Create & reuse 2 objects. 
    //Create a label object
    $lbl = new RedSea\label("Number $i", $i);
    // Set the for attribute to the ID value you will use on the radio
    $lbl->attribute('for', 'handMadeRadioButtonID');
    $rb = new RedSea\input('radio');
    // Set rb value
    $rb->attribute('value', "$i$i$i$i$i");
    $rb->attribute('id', 'handMadeRadioButtonID');
    $rb->attribute('name', "handmadeRBList1");

    // Append the outputs of the label and the radiobuttons to a working variable. Add a line break at the end.
    $rbRender .= $lbl->render();
    $rbRender .= $rb->render() . '<br>';
}
$template->set('handRadioButton', $rbRender);
// Claw back some memory as we don't need data in the working variable anymore.
$rbRender = null;


/**
 * Creating a series of checkboxes
 * Checkboxes allow an on/off display of a value. If the box is selected, then the corresponding
 * value will be submitted. If not, it won't.
 * They do not work in a collection in the same way as radiobuttons, as they are supposed to be independant,
 * but you can create a list of checkboxes in a similar way as we created the radiobuttons
 */


$cbRender = null;

for($i = 1; $i<= 10; $i++) {
    //Create & reuse 2 objects. 
    $cblbl = new RedSea\label("Number $i", $i);
    $cbcb = new RedSea\input('checkbox');
    // Set checkbox value
    $cbcb->attribute('value', "$i$i$i$i$i");
    $cbcb->attribute('name', "handmadeCBList$i");

    //Lets check one in 3
    if($i % 3 == 0) {
        $cbcb->attribute('checked', true);
    }

    //We can add some styling to the label if we want too via inline CSS.
    // You can also set an ID or class attribute and drive the styling through a CSS in your template.
    $cblbl->attribute('style', 'color: red; font-family: arial; font-weight: bold');

    // Append the outputs of the label and the radiobuttons to a working variable. Add a line break at the end.
    $cbRender .= $cblbl->render();
    $cbRender .= $cbcb->render() . '<br>';
}
// Render the checkbox string in the template.
$template->set('handCheckBox', $cbRender);
// Claw back some memory as we don't need data in the working variable anymore.
$cbRender = null;

/** Get the div "bottommenu" out of the "ressources.html" file and load it into the bottomMenu variable in the template */
$template->set('bottomMenu', $template->getElementById("bottommenu", "ressources.html"));

/**
 * Now that the template has been set with the rendered output of all the objects used above,
 * calling the template's render method will take the loaded HTML template, replace the placeholder
 * variables with their corresponding set values, and return that data to echo for output.
 */
echo $template->render();

echo "<hr>";
/** Time page execution */
echo "Time to generate HTML output " . RedSea\timer::getElapsedTime() . "<hr>";

/*
Work on SQLite db manipulation
*/

echo __line__ . " Generating SQLite output from classic query<br>";

// PDO helper class - set database type, and path to db file = mariadb
$db = new RedSea\rsdb('sqlite', "helloworld.sqlite");

//Create a table and add some data.
$db->execute("drop table if exists contacts");
$db->execute("CREATE TABLE IF NOT EXISTS contacts (
    contact_id INTEGER PRIMARY KEY,
    first_name TEXT NOT NULL,
    last_name TEXT NOT NULL)");
$db->execute("insert into contacts values (1, 'Joe', 'Biden')");
echo "Affected records: {$db->affectedRecords} - Last inserted ID: {$db->insertId}<hr>";
$db->execute("insert into contacts values (2, 'Emmanuel', 'Macaron')");
echo "Affected records: {$db->affectedRecords} - Last inserted ID: {$db->insertId}<hr>";
$db->execute("insert into contacts values (3, 'Vladimir', 'Putin')");
echo "Affected records: {$db->affectedRecords} - Last inserted ID: {$db->insertId}<hr>";
$db->execute("insert into contacts values (4, 'Xi', 'Jinping')");
echo "Affected records: {$db->affectedRecords} - Last inserted ID: {$db->insertId}<hr>";
$db->execute("insert into contacts values (5, 'Imran', 'Khan')");
echo "Affected records: {$db->affectedRecords} - Last inserted ID: {$db->insertId}<hr>";

$sql = "SELECT * FROM contacts";

$rs = new RedSea\recordset($db->query($sql));

while ($ret = $rs->fetchArray()) {
    echo($ret['first_name'] . ' ' . $ret['last_name'] . '<br>');
}

echo "Time taken to generate SQLite output from standard queries: " . RedSea\timer::getElapsedTime() . "<hr>";


//Using static debug reporting:
RedSea\debug::$debugLevel = 0;
RedSea\debug::flow("Hello World");

/** Time page execution */
echo "<hr>Page execution time: " . RedSea\timer::stopTimer() . "µsec";

echo "<hr>Single Table database operations on SQLite<br>";
$db = new RedSea\rsdb('sqlite', 'helloworld.sqlite');

echo __line__ . ' Creating read/update object<br>';
$srReadUpdate = new RedSea\recordReadUpdate($db->getDBConnection(), 'contacts');

echo __line__ . ' Loading a known record<br>';

$srReadUpdate->addWhere('first_name', 'Emmanuel');
$srReadUpdate->loadOneRecord();

// We should have the record loaded:
echo __line__ . ' Display the results from the query<br>';
echo 'Contact ID:' . $srReadUpdate->getField('contact_id') . '<br>';
echo 'Firstname:' . $srReadUpdate->getField('first_name') . '<br>';
echo 'Lastname:' . $srReadUpdate->getField('last_name') . '<hr>';

// Add a new record into the table on the same connection and table we just read from:
echo __line__ . ' Update the current record<br>';
echo "We read the following details:<br>";
$srReadUpdate->setField('first_name', 'Jacques');
$srReadUpdate->setField('last_name', 'Chirac');
$srReadUpdate->updateRecord();

echo __line__ . ' Read the record back<br>';
// Close the connection and read the new connection back:
$srReadUpdate = new RedSea\recordReadUpdate($db->getDBConnection(), 'contacts');
$srReadUpdate->addWhere('first_name', 'Jacques');
$srReadUpdate->loadOneRecord();

// We should have the record loaded:
echo __line__ . ' Display the results of the read back<br>';
echo "We read the following details that we just updated:<br>";
echo 'Contact ID:' . $srReadUpdate->getField('contact_id') . '<br>';
echo 'Firstname:' . $srReadUpdate->getField('first_name') . '<br>';
echo 'Lastname:' . $srReadUpdate->getField('last_name') . '<hr>';

echo __line__ . ' Insert a new record<br>';
//Now go insert another record:
$srInsert = new RedSea\recordNew($db->getDBConnection(), 'contacts');
$srInsert->setField('contact_id', 100);
$srInsert->setField('first_name', "Emmanuel");
$srInsert->setField('last_name', 'Macron');
echo $srInsert->insertNewRecord() . " - last insert id<br>";

echo __line__ . ' Now read back the full table<br>';
$sql = "SELECT * FROM contacts";

$rs = new RedSea\recordset($db->query($sql));

while ($ret = $rs->fetchArray()) {
    echo('ID ' . $ret['contact_id'] . ': ' . $ret['first_name'] . ' ' . $ret['last_name'] . '<br>');
}

echo '<hr><b>End of process</b>';

?>