// JS form helper functions


//Get the value of a selected element in a list.
function getSelectedValue(elementId) {
    return document.getElementById(elementId).options[document.getElementById(elementId).selectedIndex].value;
}

//Get the text of a selected element in a list
function getSelectedText(elementId) {
    return document.getElementById(elementId).options[document.getElementById(elementId).selectedIndex].text;
}

//Add a new row to list
function addElement(elementId, elementValue, elementDisplayText, elementDisabled=false) {
    newOption = document.createElement('option');
    newOption.value = elementValue.toString();
    newOption.text = elementDisplayText.toString();
    if(elementDisabled) {
        newOption.disabled = true;
    }
    document.getElementById(elementId).add(newOption);
}

//Remove the selected element in a list
function removeSelectedElement(elementId) {
    idx = getSelectedIndex(elementId);
    if(idx != null) {
        document.getElementById(elementId).remove(idx);
    }
}

//Get the text from an element containing a value like a textbox
function getText(elementId) {
    return document.getElementById(elementId).value;
}

//Get the index of a selected element in a listbox
function getSelectedIndex(elementId) {
    return document.getElementById(elementId).selectedIndex;
}
