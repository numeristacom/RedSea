<?php

// To Do : What happens when a comment is on multiple lines? The new line if no tag must inherit the last known tag. 

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
            echo __line__ . ":adding $line ==> to $currentTag...\n";
            if($atTag == "@param") {
                //The param element needs tp be it's own array...
                $currentParam = uniqid('param', true);
                echo "counter / current tag / current param / line : $docBlockCounter / $currentTag / $currentParam / $line";
                $docBlocks[$docBlockCounter][$currentTag][$currentParam] = $line;
            } else {
                $docBlocks[$docBlockCounter][$currentTag] = $line;
            }
            
        } else {
            if($whereAreWe == 1) {
                $currentTag = "@description";
                echo __line__ . ":adding $line to $currentTag...\n";
                $docBlocks[$docBlockCounter][$currentTag] .= $line;
            } else {
                //Append the data to the last known tag.
                echo __line__ . ":adding $line ==> to last known $currentTag...\n";
                if($currentTag == "@param") {
                    //The param element needs tp be it's own array... or adding a comment into the last known parameter block!
                    //$currentParam = uniqid('param', true);
                    $docBlocks[$docBlockCounter][$currentTag][$currentParam] .= $line;
                } else {
                    $docBlocks[$docBlockCounter][$currentTag] .= $line;
                }

                //echo __line__ . ": current tag: $currentTag\n"; 
                //$docBlocks[$docBlockCounter][$currentTag] .= ' ' . $line;
            }
        }

    $whereAreWe++;
    }

    if($takeOneMoreLine) {
        if($ignoreLine == false) {
            //Take the next line as this is what the docblock links to then unset the next line flag.
            echo __line__ . ":adding $line to prototype...\n";
            $docBlocks[$docBlockCounter]['@prototype'] = $line;
            $takeOneMoreLine = false;
            $inDocBlock = false;
            $docBlockCounter++;
        }
        
    }
}

//Now lets look at each element in the block array, and attempt to identify the type of the block:
// function, class, trait, property, and their visibility.

foreach($docBlocks as $id=>$block) {
   $docBlocks[$id]['type'] = getBlockType($block['@prototype'], $id);
}

//Lets see what we have!
die(var_dump($docBlocks));

function stripLine($lineToClean) {
    echo __function__ . ":" . __line__ . ":" . $lineToClean . "\n";

    if(substr($lineToClean, 0, 2) == "* ") {
        $lineToClean = trim(substr($lineToClean, 2, strlen($lineToClean)-2));
    }

    if(substr($lineToClean, 0, 2) == "- ") {
        $lineToClean = "\n" . $lineToClean;
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

function getBlockType($prototype, $id) {
    $arr = explode("(", $prototype);
    $textPart = strtolower(str_replace('{', '', $arr[0]));
    /*
    So we can have:
    1 element: $var
    2 element: class/trait/function/$var/static plus name
    3 elements: lines public/private/protected/static followed by line 1 plus name
    */
    
    $whatAreWe = 'nothing';
    $visibility = 'nothing';

    //Does the string part contain a $ ? If so it's a variable / property.
    if(strstr($textPart, '$') !== false) {
        //We have to be a variable or a property.
        //If we can find a class starting anywhere above, then we are a property,
        if(lookFor('class', $id)) {
            $whatAreWe = 'property';
        } elseif(lookFor('trait', $id)) {
            $whatAreWe = 'property';
        } else {
            $whatAreWe = 'variable';
        }
        //otherwise we are a just a variable.
    } elseif(strstr($textPart, 'class ') !== false) {
        $whatAreWe = 'class';
    } elseif(strstr($textPart, 'function ') !== false) {
        //We are a function.... or a class method! 
        //If we can find a class starting anywhere above, then we are a method,
        //otherwise we are a just a function.
        if(lookFor('class', $id)) {
            $whatAreWe = 'method';
        } elseif(lookFor('trait', $id)) {
            $whatAreWe = 'method';
        } else {
            $whatAreWe = 'function';
        }
    } elseif(strstr($textPart, 'trait ') !== false) {
        $whatAreWe = 'trait';
    }
    
    if(strstr($textPart, 'public ') !== false) {
        $visibility = 'public';
    }elseif(strstr($textPart, 'private ') !== false) {
        $visibility = 'private';
    }elseif(strstr($textPart, 'protected ') !== false) {
        $visibility = 'protected';
    }elseif(strstr($textPart, 'static ') !== false) {
        $visibility = 'static';
    }

    return array('type' => $whatAreWe, 'visibility' => $visibility);
}

function lookFor($for, $to) {
    global $docBlocks;
    for($i = 0; $i<= $to; $i++) {
        if($docBlocks[$i]['type']['type'] == 'class');
        return true;
    }
    return false; 
}