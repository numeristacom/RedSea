# `namespace RedSea`

This file defines multiple HTML tags that can be completely or partially generated by RedSea to be included in an HTML form in a template.

 * **Author:** Daniel Page <daniel@danielpage.com>
 * **Copyright:** Copyright (c) 2021, Daniel Page
 * **License:** Licensed under the EUPL v1.2 - https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * **To-do:** The form processing elements currently only generate code for input, label, select, option, textarea, button.

     <p>

     The code does not generate form, fieldset, legend, optgroup, datalist or output tags. Currently these will need to be hardcoded in your model.

# `class input`

## `use commonHtmlProperties`

Defines a trait that is used by all HTML classes working around inheritance problems

## `protected $tagAttrArray = array( 'accept' => array( 0 => null, 1 => 'str'), 'autocomplete' => array(0 => null, 1 => 'str'), 'autofocus' => array(0 => null, 1 => 'bool'), 'checked' => array(0 => null, 1 => 'bool'), 'disabled' => array(0 => null, 1 => 'bool'), 'dirname' => array(0 => null, 1 => 'str'), 'form' => array(0 => null, 1 => 'str'), 'formaction' => array(0 => null, 1 => 'str'), 'formenct' => array(0 => null, 1 => 'str'), 'formmethod' => array(0 => null, 1 => 'str'), 'formnovalidate' => array(0 => null, 1 => 'str'), 'formtarget' => array(0 => null, 1 => 'str'), 'list' => array(0 => null, 1 => 'str'), 'max' => array(0 => null, 1 => 'int'), 'maxlength' => array(0 => null, 1 => 'int'), 'min' => array(0 => null, 1 => 'int'), 'minength' => array(0 => null, 1 => 'int'), 'multiple' => array(0 => null, 1 => 'bool'), 'name' => array(0 => null, 1 => 'str'), 'pattern' => array(0 => null, 1 => 'str'), 'placeholder' => array(0 => null, 1 => 'str'), 'readonly' => array(0 => null, 1 => 'bool'), 'required' => array(0 => null, 1 => 'bool'), 'size' => array(0 => null, 1 => 'int'), 'step' => array(0 => null, 1 => 'int'), 'type' => array(0 => null, 1 => 'str'), 'value' => array(0 => null, 1 => 'str'), )`

This is the list of tag specific attributes with their corresponding expected data types. This is used internally by the library.

## `protected $inputTypeArray = array( 'button', 'checkbox', 'color', 'date', 'datetime-local', 'email', 'file', 'hidden', 'image', 'month', 'number', 'password', 'radio', 'range', 'reset', 'search', 'submit', 'tel', 'text', 'time', 'url', 'week')`

Valid values for the "type" attributes

## `public function __construct($inputType)`

Set up an input block. One or two variables are required depending on the input type.

 * **Parameters:** `$inputType` — `string` — Type of the input tag. Input type will be converted to lower case internally.

     NOTE: You should set the ID for this tag and link it to an HTML label that references the same ID.

     Valid input types are:

     - button

     - checkbox

     - color

     - date

     - datetime-local

     - email

     - file

     - hidden

     - image

     - month

     - number

     - password

     - radio

     - range

     - reset

     - search

     - submit

     - tel

     - text

     - time

     - url

     - week

     if you generate an input tag belonging to one of the input types below:

     - Text

     - Checkbox

     - Radio

     - File

     - Password
 * **Returns:** `bool` — TRUE if successful, FALSE on error and error.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public function render()`

Return the HTML code for this tag

 * **Returns:** `string` — HTML code for the specified tag.

## `class label`

This class allows the creation of a label tag, to be associated with certain form elements such as radiobuttons.

## `use commonHtmlProperties`

Defines a trait that is used by all HTML classes working around inheritance problems


## `protected $tagAttrArray = array( 'for' => array( 0 => null, 1 => 'str'), 'form' => array(0 => null, 1 => 'str') )`

This is the list of tag specific attributes with their corresponding expected data types. This is used internally by the library.


## `protected $displayValue = null`

Will contain the text label to be displayed in the label, set by the constructor.


## `public function __construct($displayValue=null, $boundControlID=null, $boundFormID=null)`

The constructor can instanciate a full HTML label on it's own using at minimum the label value to be displayed. As a label is designed to be linked to an HTML form or form element tag, and you can optionally set the ID value of both the tag and the form that the label is to be linked to. The label tag can be bound to one of the following form elements: input, select, textarea, button, fieldset, legend, datalist, output, option, optgroup.

 * **Parameters:**
   * `$labelValue` — `string` — Text to display on the label in the rendered HTML page
   * `$boundControlID` — `string|null` — Optional ID of the form element that the label is related to. Default is NULL.
   * `$boundFormID` — `string|null` — Optional ID of the overall form that both the label, and if set the form element, belong to. Default is NULL.
 * **Returns:** `bool` — TRUE if successful, FALSE on error and error.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public function render()`

Return the HTML code for this tag

 * **Returns:** `string` — HTML code for the specified tag.

# `class select`

This class allows the creation of a select tag and associated option values (option values can be created seperately if they are to be inserted as a template placeholder inside an existing select tag in a form).

## `use commonHtmlProperties`

Defines a trait that is used by all HTML classes working around inheritance problems


## `private $optionArray = array()`

A select box in HTML displays a series of option objects that have been created and stored in it. This array stores those either select objects where full objects are added or simple select options if it does not contain a fully parameterd object..


## `private $outputFullTag = false`

This flag defines if the tag should be completely output. If True, a full select tag will be generated, ie: <select> followed by options </select> If False, only the code for the option tags will be generated, and the select tags will be ignored (generally because the option tag itself is already present in the template model and the option tags generated will be used to replace a placeholder in that template). It is set by the constructor and used later by the render method.


## `protected $tagAttrArray = array( 'autofocus' => array( 0 => null, 1 => 'bool'), 'disabled' => array( 0 => null, 1 => 'bool'), 'form' => array( 0 => null, 1 => 'str'), 'multiple' => array( 0 => null, 1 => 'bool'), 'name' => array( 0 => null, 1 => 'str'), 'required' => array( 0 => null, 1 => 'bool'), 'size' => array( 0 => null, 1 => 'int') )`

This is the list of tag specific attributes with their corresponding expected data types. This is used internally by the library.


## `public function __construct($outputFullTag=true)`

Sets up the class. Requires a flag to indicate if the class needs to generate a full HTML <select> tag and contents, or just the contents of the tag to be added to an existing tag in an HTML page.

 * **Parameters:** `$outputFullTag` — `bool` — if TRUE, the full HTML code from <select> to </select>. If FALSE, only the

     contents of the select tag will be generated, but the <select> and </select> tags will be omitted.

## `public function quickAdd($label=null, $value=null, $isSelected=false, $isDisabled=false)`

Allows adding a simple option tag into the select object. This is a simplified method that allows the creation of simple option tags inside the select object that contain a display label, a corresponding value to be submitted if the option is selected, and allows defining if the tag is selected or disabled. This does not allow CSS styling, events or other attributes other than "selected" or "disabled". It is also more memory friendly as it does not store a complete object but a simple HTML string.

 * **Parameters:**
   * `$label` — `string` — Text label to display in the option tag.
   * `$value` — `string` — Value to be set in the tag's value attribute.
   * `$isSelected` — `bool` — If TRUE, the tag's selected attribute will be set.
   * `$isDisabled` — `bool` — If TRUE, the tag's disabled attribute will be set
 * **Returns:** `TRUE` — on success, FALSE on failure.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public function addOptionObject($optionObject)`

Add an option object to the select tag. This will be rendered as a selectable element inside the select tag

 * **Parameters:** `$optionObject` — `object` — An instanciated RedSea 'option' object that has been set up for use.
 * **Returns:** `bool` — TRUE on success, FALSE on failure.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public function render()`

Return the HTML code for this tag

 * **Returns:** `string` — HTML code for the specified tag.

# `class option`

This class allows the creation of individual option tags, that can either be rendered stand-alone or be added into a select box to be rendered as a complete HTML widget.

## `public $tagAttrArray = array( 'label' => array( 0 => null, 1 => 'str'), 'value' => array( 0 => null, 1 => 'str') )`

This is the list of tag specific attributes with their corresponding expected data types. This is used internally by the library.


## `public function __construct($optionDisplayValue, $optionInnerValue=null)`

The constructor allows you to display a basic set up option tag for use inside a select tag. you can still access all the common methods that extends from the trait to customise events and common tags

 * **Parameters:**
   * `$optionDisplayValue` — `string` — Text value to display in the tag. This can be any value (including a blank string), but an error will be raised if this is a null value.
   * `$optionInnerValue` — `string` — Optional value to set on the tag's 'value' attribute that when selected will be sent back to a form handler on submission 

     of the form that contains this element. If the value is null, it will be ignored.
 * **Returns:** `bool` — TRUE if successful, FALSE on error and error can be obtained through getLastError.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public function render()`

Return the HTML code for this tag

 * **Returns:** `string` — HTML code for the specified tag.

# `class textarea`

Generates a text area tag

## `protected $tagAttrArray = array( 'autofocus' => array( 0 => null, 1 => 'str'), 'cols' => array( 0 => null, 1 => 'str'), 'dirname' => array( 0 => null, 1 => 'str'), 'disabled' => array( 0 => null, 1 => 'str'), 'form' => array( 0 => null, 1 => 'str'), 'maxlength' => array( 0 => null, 1 => 'str'), 'name' => array( 0 => null, 1 => 'str'), 'placeholder' => array( 0 => null, 1 => 'str'), 'readonly' => array( 0 => null, 1 => 'str'), 'rows' => array( 0 => null, 1 => 'str'), 'required' => array( 0 => null, 1 => 'str'), 'wrap' => array( 0 => null, 1 => 'str') )`

This is the list of tag specific attributes with their corresponding expected data types. This is used internally by the library.


## `public function __construct($tagName, $tagTextValue=null)`

The constructor allows you to set up a textarea tag you can still access all the common methods that extends from the trait to customise events and common tags

 * **Parameters:**
   * `$tagName` — `string` — name of the tag that will be used on submission to a form handler.
   * `$tagTextValue` — `string` — Optional text value to display in the text area.
 * **Returns:** `bool` — TRUE if successful, FALSE on error.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public function setDisplayValue($textValue)`

Set the display value inside the text area

 * **Returns:** `string` — $txtValue Value to display inside the text area

## `public function getDisplayValue()`

Returns the currently set text value to be displayed inside the textarea tag as default text

 * **Returns:** `string` — Currently set text to display

## `public function render()`

Return the HTML code for this tag

 * **Returns:** `string` — HTML code for the specified tag.

# `class button`

This class allows the creation of a button. Note that even if the only obligatory value in the constructor is the text to display, you may also want to add a type attribute before rendering.

## `protected $tagAttrArray = array( 'autofocus' => array( 0 => null, 1 => 'bool'), 'disabled' => array( 0 => null, 1 => 'bool'), 'form' => array( 0 => null, 1 => 'str'), 'formaction' => array( 0 => null, 1 => 'str'), 'formenctype' => array( 0 => null, 1 => 'str'), 'formmethod' => array( 0 => null, 1 => 'str'), 'formonvalidate' => array( 0 => null, 1 => 'bool'), 'formtarget' => array( 0 => null, 1 => 'str'), 'name' => array( 0 => null, 1 => 'str'), 'type' => array( 0 => null, 1 => 'str') )`

This is the list of tag specific attributes with their corresponding expected data types. This is used internally by the library.


## `public function __construct($buttonName=null)`

Class constructor. Requires at minimum one value - the text to display on the button.

 * **Parameters:** `$buttonName` — `string|null` — Text to display on the rendered button
 * **Returns:** `bool` — TRUE if successful, FALSE on error/

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public function render()`

Return the HTML code for this tag

 * **Returns:** `string` — HTML code for the specified tag.

# `class GlobalPropertiesAndAttributes`

Static class to be used internally by the form (or any) HTML tag generator. This will allow attributes and events to be added into the tag classes when needed, but avoid having a full array list of all global attributes and events copied into every object if they are not needed. This will allow an "on-demand" creation of attribute and event data avoiding un-necessary procesisng for each attribute and event that a tag class may use - but probably does not! As a class defining static methods, this can be called without instanciation, optimising memory use The downside is that, opposed to a trait, the class cannot know anything implicit about the parent class unless it has been explicitly sent as an argument - probably a good thing as this enables less coupled code.

## `private static $globalAttrArray = array( 'accesskey' => array(0 => null, 1 => 'str'), 'alt' => array(0 => null, 1 => 'str'), 'class' => array(0 => null, 1 => 'str'), 'contenteditable' => array(0 => null, 1 => 'str'), 'dir' => array(0 => null, 1 => 'str'), 'draggable' => array(0 => null, 1 => 'str'), 'height' => array(0 => null, 1 => 'int'), 'hidden' => array(0 => null, 1 => 'bool'), 'id' => array(0 => null, 1 => 'str'), 'lang' => array(0 => null, 1 => 'str'), 'spellcheck' => array(0 => null, 1 => 'str'), 'src' => array(0 => null, 1 => ''), 'style' => array(0 => null, 1 => 'str'), 'tabindex' => array(0 => null, 1 => 'str'), 'title' => array(0 => null, 1 => 'str'), 'translate' => array(0 => null, 1 => 'str'), 'width' => array(0 => null, 1 => 'int') )`

Array of common standard HTML attributes with their expected data types. This is used internally by the library.


## `private static $globalEventArray = array(`

Array of all standard HTML events. This is used internally by the library.


## `public static function validateAndSetAttribute($name, $value, $tagSpecificArray = null)`

Checks an attribute for coherency against the global attribute list and a class's custom attribute list. If the attribute matches, returns an array to be loaded into the class's processable attribute list.

 * **Parameters:**
   * `$name` — `string` — Attribute name to set. This attribute must be in the global attribute list or in

     the tag specific array if set.
   * `$value` — `mixed` — Value to set. The value must match the type set in the tag global or specific array list
   * `$tagSpecificArray` — `array|null` — Tag specific array if the tag has specific attributes in addition to the global ones.
 * **Returns:** `array|bool` — Array containing the set value or false if the array element is not found or not valid.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public static function validateAndSetEvent($name, $value)`

Checks and returns an event value array to be used in a tag against the list.

 * **Parameters:**
   * `$name` — `string` — Event name to set. If the event name does not match a known event in the list,

     the method will return false
   * `$value` — `string` — Event value to set.
 * **Returns:** `array|bool` — Array containing the set event value or false if the array element is not found.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `trait commonHtmlProperties`

This class will be inherited by RedSea managed HTML tag classes. For attributes and values see https://developer.mozilla.org/en-US/docs/Web/HTML/Global_attributes and the associated help per HTML tag to see specific attributes. For events, see https://www.w3schools.com/tags/ref_eventattributes.asp.

 * **To-do:** : Add helper methods to the HTML library (or an extra HTML helper library) that can explicity set the valid

     attributes and / or events per tag - this could be referenced as a stand-alone trait to be added to the

     RedSea tag classes if required, rather than letting the dev look up the elements they need to set themselves.

## `protected $tagType = null`

This is part of the common internal data structure defining the class type. It will be set by the corresponding constructor via the type method.


## `protected $tagDisplayValue = null`

Some tags can display text. This will be generally set by the class constructor.

## `private $RenderAttributeArray = array()`

Array containing all set attributes to render.

## `private $RenderEventArray = array()`

Array containing all set Events to render

## `public function type($tagType = null)`

Get or set a tag type for identification purposes. This is a getter/setter method.

 * **Parameters:** `$tagType` — `string` — Type of tag to set:

     - If Null, the current type of the tag is returned.

     - If string, the value will be set as the current tag type if no other type is already set otherwise
 * **Returns:** `string|bool` — - If $tagType is empty, then the method will return the current set value of of the tag

     - If $tagType is set correctly the method will return TRUE

     - If $tagType is not set correctly, the method will return FALSE.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.

## `public function attribute($attributeName, $attributeValue=null)`

Get or set a tag object's attributes. This is a getter/setter method. If an attribute name is set but not the event value, the method will return the current attribute value, if set. If there is no attribute set, the method will return FALSE. This is set as a trait as it requires direct access to the object's own internal structure to avoid code duplication.

 * **To-do:** We have boolean values set as values: This is going to be a problem with the getters as you will not know if a value 

     is supposed to be boolean or if it's not set at all!

     To unset an attribute, send an empty string (not a null string) as the event value. Given that empty attributes will not do

     anything on a tag, they will be removed from the list and will not be rendered.
 * **Parameters:**
   * `$attributeName` — `string` — Name of the attribute to get, set or unset. The event name must already exist as a valid event name as 

     defined by the static method GlobalPropertiesAndAttributes::validateAndSetAttribute
   * `$attributeValue` — `string` — Value to set in the attribute.

     - If the string is set to NULL, the method acts as a getter: it will return the value for the attribute if the name is valid

     - If the string is set to empty string ("") the method acts as an "un"-setter, removing the event from the list of events

     and the attribute will no longer be rendered in the object.

     - If the string is set to any other value, the method acts as a setter, adding the event value to the object as long as 

     the event exists as defined by GlobalPropertiesAndAttributes::validateAndSetAttribute
 * **Returns:** `string|bool` — Depending on accessing this method as a getter or setter:

     - Getter: Will return the value that corresponds to the attribute name, or FALSE if the value is not set or the name is invalid.

     - Setter: Will return TRUE if the value was set, or FALSE if not. 

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.
 * **See also:**
   * GlobalPropertiesAndAttributes::validateAndSetEvent()
   * rsDebug::getLastError()

## `public function event($eventName, $eventValue)`

Get or set a tag object's events. This is a getter/setter method. If an event name is set but not the event value, the method will return the current event value, if set. If there is no event set, the method will return FALSE: As events are only actually strings internally, boolean false is never a value that will actually be rendered. To unset an event, send an empty string (not a null string) as the event value.

 * **Parameters:**
   * `$eventName` — `string` — Name of the event to get, set or unset. The event name must already exist as a valid event name as 

     defined by the static method GlobalPropertiesAndAttributes::validateAndSetEvent
   * `$eventValue` — `string` — Value to set in the event.

     - If the string is set to NULL, the method acts as a getter: it will return the value for the event if the name is valid

     - If the string is set to empty string ("") the method acts as an "un"-setter, removing the event from the list of events

     and the event will no longer be rendered in the object.

     - If the string is set to any other value, the method acts as a setter, adding the event value to the object as long as 

     the event exists as defined by GlobalPropertiesAndAttributes::validateAndSetEvent
 * **Returns:** `string|bool` — Depending on accessing this method as a getter or setter:

     - Getter: Will return the value that corresponds to the event name, or FALSE if the value is not set or the name is invalid.

     - Setter: Will return TRUE if the value was set, or FALSE if not. 

     If the method returns an error, the error.

     In case of error, in addition to the above:

     - The method itself will return FALSE

     - The object's $errorFlag flag will be set to TRUE

     - Error details can be obtained by calling the object's getLastError() method.
 * **See also:**
   * GlobalPropertiesAndAttributes::validateAndSetEvent()
   * rsDebug::getLastError()

## `private function singleLineTagRender($tagName, $tagHasValue=null)`

Common method to generate single line HTML tags. It is called from each class's render() method and will generate the full HTML of the tag, including attributes, events and any content that needs to be set between the opening and closing parts of the tag.

 * **Parameters:**
   * `$tagName` — `string` — Name of the HTML tag to render
   * `$tagHasValue` — `string|null` — If the tag is not self closing and has a value to display (such as a label, a...) then

     the data to display in the tag will be specified here.
 * **Returns:** `string` — rendered single line HTML tag.