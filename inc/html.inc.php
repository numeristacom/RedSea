<?php

namespace RedSea;


class tagFactory {
    /**
     * Array of common standard HTML attributes with their expected data types.
     * This is used internally by the library.
     * @internal
     */
    public $lastError = null;
    protected $setAttrArray = array();
    protected $setEventArray = array();
    protected $tagType = null;              //The actual name of the tag. Must be set if you want it to render something!

    protected $globalAttrArray = array(
        'accesskey' => array(0 => null, 1 => 'str'),
        'alt' => array(0 => null, 1 => 'str'),
        'class' => array(0 => null, 1 => 'str'),
        'contenteditable' => array(0 => null, 1 => 'str'),
        'dir' => array(0 => null, 1 => 'str'),
        'draggable' => array(0 => null, 1 => 'str'),
        'height' => array(0 => null, 1 => 'int'),
        'hidden' => array(0 => null, 1 => 'bool'),
        'id' => array(0 => null, 1 => 'str'),
        'lang' => array(0 => null, 1 => 'str'),
        'spellcheck' => array(0 => null, 1 => 'str'),
        'src' => array(0 => null, 1 => ''),
        'style' => array(0 => null, 1 => 'str'),
        'tabindex' => array(0 => null, 1 => 'str'),
        'title' => array(0 => null, 1 => 'str'),
        'translate' => array(0 => null, 1 => 'str'),
        'width' => array(0 => null, 1 => 'int')
    );

    /**
     * Array of all standard HTML events.
     * This is used internally by the library.
     * @internal
     */
    protected $globalEventArray = array(
        //window events
        'onafterprint' =>null,
        'onbeforeprint' =>null,
        'onbeforeunload' =>null,
        'onerror' =>null,
        'onhashchange' =>null,
        'onload' =>null,
        'onmessage' =>null,
        'onoffline' =>null,
        'ononline' =>null,
        'onpagehide' =>null,
        'onpageshow' =>null,
        'onpopstate' =>null,
        'onresize' =>null,
        'onstorage' =>null,
        'onunload' =>null,
        //Form events
        'onblur' =>null,
        'onchange' =>null,
        'oncontextmenu' =>null,
        'onfocus' =>null,
        'oninput' =>null,
        'oninvalid' =>null,
        'onreset' =>null,
        'onsearch' =>null,
        'onselect' =>null,
        'onsubmit' =>null,
        //Keyboard events
        'onkeydown' =>null,
        'onkeypress' =>null,
        'onkeyup' =>null,
        //Mouse events
        'onclick' =>null,
        'ondblclick' =>null,
        'onmousedown' =>null,
        'onmousemove' =>null,
        'onmouseout' =>null,
        'onmouseover' =>null,
        'onmouseup' =>null,
        'onmousewheel' =>null,
        'onwheel' =>null,
        'ondrag' =>null,
        'ondragend' =>null,
        'ondragenter' =>null,
        'ondragleave' =>null,
        'ondragover' =>null,
        'ondragstart' =>null,
        'ondrop' =>null,
        'onscroll' =>null,
        'oncopy' =>null,
        'oncut' =>null,
        'onpaste' =>null,
        //media related
        'onabort' =>null,
        'oncanplay' =>null,
        'oncanplaythrough' =>null,
        'oncuechange' =>null,
        'ondurationchange' =>null,
        'onemptied' =>null,
        'onended' =>null,
        'onerror' =>null,
        'onloadeddata' =>null,
        'onloadedmetadata' =>null,
        'onloadstart' =>null,
        'onpause' =>null,
        'onplay' =>null,
        'onplaying' =>null,
        'onprogress' =>null,
        'onratechange' =>null,
        'onseeked' =>null,
        'onseeking' =>null,
        'onstalled' =>null,
        'onsuspend' =>null,
        'ontimeupdate' =>null,
        'onvolumechange' =>null,
        'onwaiting' =>null,
        // Details tag event
        'ontoggle' =>null
    );

    /**
     * Add tag specific attributes from the parent object to the inherited factory, if any.
     * The array format must be as follow: 
     * 'attributename' => array(0 => null, 1 => <datatype>)
     * attributename is the unique tag specific attribute name.
     * The array it contains has 2 values:
     * - Index 0: Always null (meaning unset).
     * - Index 1: A string containing the data type. Possible value: 'str', 'bool', 'int', 'null'
     * str must be a string value. Bool must be set true or false, int must be integer number, and null will unset a set attribute.
     * @param mixed $tagAttrArray 
     * @return void 
     */
    protected function loadExtraAttributes($tagAttrArray) {
        if(is_array($tagAttrArray)) {
            foreach($tagAttrArray as $key => $value) {
                $this->globalAttrArray[$key] = $value;
            }
            return true;
        } else {
            $this->setLastError("Not an array");
            return false; 
        }

        /*
        protected $tagAttrArray = array(
        'for' => array( 0 => null, 1 => 'str'),
        'form' => array(0 => null, 1 => 'str')
    );
        */
    }

    /**
     * Checks an attribute for coherency against the global attribute list and a class's custom attribute list.
     * If the attribute matches, returns an array to be loaded into the class's processable attribute list.
     * @param string $name Attribute name to set. This attribute must be in the global attribute list or in
     * the tag specific array if set.
     * @param mixed $value Value to set. The value must match the type set in the tag global or specific array list
     * @param array|null Tag specific array if the tag has specific attributes in addition to the global ones.
     * @return array|bool Array containing the set value or false if the array element is not found or not valid.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     * @internal
     */
    public function attribute($name, $value) {
        debug::flow();
        if(empty($name)) {
            debug::err("No attribute name specified");
            return false;
        } else {
            //Make sure the tag name is lower case as this is how all the tags are named internally.
            $name = strtolower($name);

            //Find out where the attribute exists, and extract the corresponding data array element.
            $keyArrayElement = null;
            if(array_key_exists($name, $this->globalAttrArray)) {
                //Validate that the value's value matches the expected value for that name
                $isExpectedValue = false;
                if(is_null($value)) {   //Has a null value been sent? If so, no need to validate the data type
                    //Set the flag and continue.
                    $isExpectedValue = true;
                } else {
                    //We need to validate the actual value matches the specified type for the attribute
                    switch($this->globalAttrArray[$name][1]) {
                        case "int":
                            if(is_integer($value)) {
                                $isExpectedValue = true;
                            }
                            break;
                        case "bool":
                            if(is_bool($value)) {
                                $isExpectedValue = true;
                            }
                            break;
                        case "str":
                            if(is_string("$value")) {
                                $value = str_replace('"', "&quot;", $value);
                                $isExpectedValue = true;
                            }
                            break;
                        case "null":
                            if(is_null($value)) {
                                $isExpectedValue = true;
                            }
                            break;
                    }
                }

                //Do we have a value that matches the expected type?
                if($isExpectedValue) {
                    debug::err('Appending attribute', $name . " / " . $value);
                    $this->setAttrArray[$name] = $value;    //Set or update a value on the attribute array.
                    return true;
                } else {
                    $this->setLastError('Value does not match the expected data type');
                    return false;
                }
            } else {
                $this->setLastError('Non-exising attribute');
                return false;
            }
        }
    }

    /**
     * Checks and returns an event value array to be used in a tag against the list.
     * @param string $name Event name to set. If the event name does not match a known event in the list,
     * the method will return false
     * @param string $value Event value to set.
     * @return array|bool Array containing the set event value or false if the array element is not found.
     * In case of error, in addition to the above:
     * - The method itself will return FALSE
     * - The object's $errorFlag flag will be set to TRUE
     * - Error details can be obtained by calling the object's getLastError() method.
     * @internal
     */
    public function event($name, $value) {
        debug::flow();
        if(empty($name)) {
            debug::err("Event name not sent");
            return false;
        } else {
            $name = strtolower($name);
            $value = str_replace('"', "&quot;", $value);
            debug::err('Appending event', $name . " / " . $value);
            if(array_key_exists($name, $this->globalEventArray)) {
                $this->setEventArray[$name] = $value;
                return true;
            } else {
                //Non existing tag
                debug::err("Event type not recognised", $name);
                return false;
            }
        }
    }

    /** sets errors from the object class */
    private function setLastError($str) {
        $this->lastError = $str;
    }

    /** Returns the last recorded error before clearing it. */
    public function getLastError() {
        $err = $this->lastError;
        $this->lastError = null;
        return $err;
    }

    /**
     * Common method to generate single line HTML tags.
     *  It is called from each class's render() method and will generate the full HTML of the tag, including attributes, events
     * and any content that needs to be set between the opening and closing parts of the tag.
     * @param string $tagName Name of the HTML tag to render
     * @param string|null $tagHasValue If the tag is not self closing and has a value to display (such as a label, a...) then
     * the data to display in the tag will be specified here.
     * @return string rendered single line HTML tag.
     * @internal
     */
    public function render($tagValue=null) {
        debug::flow();
        $commonProperties = null;
        debug::err("Rendering tag", $this->tagType);
        $renderData = "<" . $this->tagType;
        // Generate the tag attributes.
        foreach($this->setAttrArray as $key => $value) {
            if(!is_null($value)) {  //Don't render null values.
                debug::flow('Attribute set loop', $key . " / " . $value);
                if($this->globalAttrArray[$key][1] == 'bool') { //Check the expected value from the original attribute definition.
                    //only display the key name if expected data type is BOOL and the value set for the attribute is TRUE
                    if($value === true) {
                        //Boolean attribute set to TRUE. Just display the attribute name in the rendering.
                        $renderData .= " $key";
                    }
                } else {
                    //Set attribute name and corresponding value in the rendering
                    $renderData .= " $key=\"" . $value . "\"";
                }
            }
        }

        debug::err('Tag and attribute interim result', $renderData);

        //Generate the tag events.
        foreach($this->setEventArray as $key => $value) {
            debug::flow('Event set loop', $key . " / " . $value);
            if(!is_null($value)) {
                $renderData .= " $key=\"$value\"";
            }
        }
        $renderData .= ">";
        debug::flow('Tag event and attribute interim result', $renderData);

        if(!empty($tagValue)) {
            $renderData .= $tagValue  . "</" . $this->tagType .">\n";
        }

        debug::flow('Final tag output', $renderData);

        return $renderData;
    }
}   