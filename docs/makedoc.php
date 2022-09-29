<?php

$str = file_get_contents($argv[1]);
$str = str_replace("\r\n", "\n", $str);
$fileArray = explode("\n", $str);
$noLineArray = array();
$docBlocks = array();
$currentTag = null;
$currentDocBlockLine = null;

//Clear blank lines
foreach($fileArray as $line) {
    $line = trim($line);
    if($line != "") {
        $noLineArray[] = $line;
    }
}

//Clean up, save RAM.
$fileArray = $noLineArray;
$noLineArray = null;

$inDocBlock = false;
$takeOneMoreLine = false;

foreach($fileArray as $line) {
    $line = trim($line);
    //Set up where we are.
    switch($line) {
        case '/**':
            $inDocBlock = true;
            
        break;
        case '*/';
            $inDocBlock = false;
            $takeOneMoreLine = true;
            //We need to take one more line.
        break;
    }

    if($takeOneMoreLine) {
        //Take the next line as this is what the docblock links to then unset the next line flag.
        $takeOneMoreLine = false;
    }

    /*
    We support the following tags:
    param
    property
    licence
    static
    return
    internal
    deprecated
    copyright
    author
    */

    if($inDocBlock) {
        $currentDocBlockLine = 1;
        //We are in a docblock. Clean it up if it starts with "* "
        $line = stripLine($line);
    }



}


function stripLine($lineToClean) {
    if(substr($lineToClean, 0, 2) == "* ") {
        $lineToClean = substr(2, strlen($lineToClean) - 2);
    }


    return $lineToClean;
}