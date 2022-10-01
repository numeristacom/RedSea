<?php

$str = file_get_contents($argv[1]);
$str = str_replace("\r\n", "\n", $str);
$fileArray = explode("\n", $str);
$noLineArray = array();
$docBlocks = array();
$currentTag = null;
$currentDocBlockLine = null;
$docBlockCounter = 0;

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
    $ignoreLine = false;
    $line = trim($line);
    //Set up where we are.
    switch($line) {
        case '/**':
            $ignoreLine = true;
            $inDocBlock = true;
            $whereAreWe=1;
        break;
        case '*/';
            $ignoreLine = true;
            $inDocBlock = false;
            $takeOneMoreLine = true;
            //We need to take one more line.
        break;
    }


    if($inDocBlock && $ignoreLine == false) {
        //We are in a docblock. Clean it up if it starts with "* "
        $line = stripLine($line);
        $atTag = isAtTag($line);
        if($atTag !== false) {
            
            $currentTag = $atTag;
            $line = trim(substr($line, strlen($currentTag), strlen($line)));
            echo __line__ . ":adding $line to $currentTag...\n";
            if($atTag == "@param") {
                //The param element needs tp be it's own array...
                $currentParam = uniqid('param', true);
                $docBlocks[$docBlockCounter][$currentTag][$currentParam] = $line;
            } else {
                $docBlocks[$docBlockCounter][$currentTag] = $line;
            }
            
        } else {
            if($whereAreWe == 1) {
                $currentTag = "@description";
                echo __line__ . ":adding $line to $currentTag...\n";
                $docBlocks[$docBlockCounter][$currentTag] = $line;
            } else {
                //Append the data to the last known tag.
                echo __line__ . ":adding $line to last known $currentTag...\n";

                if($currentTag == "@param") {
                    //The param element needs tp be it's own array...
                    $currentParam = uniqid('param', true);
                    $docBlocks[$docBlockCounter][$currentTag][$currentParam] = $line;
                } else {
                    $docBlocks[$docBlockCounter][$currentTag] = $line;
                }


                $docBlocks[$docBlockCounter][$currentTag] .= ' ' . $line;
            }
        }

    $whereAreWe++;
    }

    if($takeOneMoreLine) {
        if($ignoreLine == false) {
            //Take the next line as this is what the docblock links to then unset the next line flag.
            echo __line__ . ":adding $line to prototype...\n";
            $docBlocks[$docBlockCounter]['prototype'] = $line;
            $takeOneMoreLine = false;
            $inDocBlock = false;
            $docBlockCounter++;
        }
        
    }
}

//Lets see what we have!
var_dump($docBlocks);


function stripLine($lineToClean) {
    echo __function__ . ":" . __line__ . ":" . $lineToClean . "\n";

    if(substr($lineToClean, 0, 2) == "* ") {

        $lineToClean = trim(substr($lineToClean, 2, strlen($lineToClean)-2));
    }
    echo __function__ . ":" . __line__ . ":" . $lineToClean . "\n";

    return $lineToClean;
}

function isAtTag($line) {
    echo __function__ . ":" . $line . "\n";
    if(str_starts_with(trim($line), '@')) {
        $arr = explode(" ", $line);
        return $arr[0];
    } else {
        return false;
    }
}