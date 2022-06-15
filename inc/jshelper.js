
function makeShortPseudoRandom() {
    rc = "AaBbCcDdEeFfGgHhIiJjKkLlMmNnOoPpQqRrSsTtUuVvWwXxYyZz0123456789";
    dt = new Date()
    mo = dt.getMonth()
    dy = dt.getDay()
    hr = dt.getHours(); 
    mi = dt.getMinutes();
    se = dt.getSeconds();
    
    pr = rc.substring(mo, mo+1) + rc.substring(dy, dy+1) + rc.substring(hr, hr+1) + rc.substring(mi, mi+1) + rc.substring(se, se+1)

    for(let i = 1; i <= 5; i++) {
        r = Math.floor(Math.random() * 61);
        pr +=  rc.substring(r, r+1);
    }
    return pr;
}

function getSelectedValue(elementId) {
    return document.getElementById(elementId).options[document.getElementById(elementId).selectedIndex].value;
}

function getSelectedText(elementId) {
    return document.getElementById(elementId).options[document.getElementById(elementId).selectedIndex].text;
}

function removeSelectedOption(elementId) {
    idx = getSelectedIndex(elementId);

    if(idx != null) {
        document.getElementById(elementId).remove(idx);
    }
}

function quickAddOption(elementId, elementValue, elementDisplayText, elementDisabled=false) {
    newOption = document.createElement('option');
    newOption.value = elementValue.toString();
    newOption.text = elementDisplayText.toString();
    if(elementDisabled) {
        newOption.disabled = true;
    }
    document.getElementById(elementId).add(newOption);
}

function setDivText(elementId, txtString) {
    document.getElementById(elementId).innerHTML = txtString;
}

function getText(elementId) {
    return document.getElementById(elementId).value;
}

function getSelectedIndex(elementId) {
    return document.getElementById(elementId).selectedIndex;
}

function hideElement(elementId) {
    document.getElementById(elementId).style.display = "none";
}

function showElement(elementId) {
    document.getElementById(elementId).style.display = "block";
}

function getTimestamp() {
    currentDate = new Date();
    return currentDate.getTime();
}

function isChecked(elementId) {
    if(document.getElementById(elementId).checked==true) {
        return true;
    } else {
        return false;
    }
}

function setRBCB(elementId) {
    document.getElementById(elementId).checked=true;
}

function unsetRBCB(elementId) {
    document.getElementById(elementId).checked=false;
}

function selectedRB(radiobuttonGroupName) {
    var getSelectedValue = document.querySelector( 'input[name="' + radiobuttonGroupName + '"]:checked');
    if(getSelectedValue != null) {
        return getSelectedValue.value;
    } else {
        return null;
    }
}

function selectAllListBoxElements(elementId) {
    var listbox = document.getElementById(elementId);
    for (var count = 0; count < listbox.options.length; count++) {
      listbox.options[count].selected = true;
    }
  }