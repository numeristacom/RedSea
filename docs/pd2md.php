<?php

/**
 * Generates a basic outline of PHP file documentation converting phpdoc to markdown.
 * The file will make a markdown version of the file using the following hierarchy:
 * - File (from the document header block - if it contains a @package tag)
 * - Class
 *   - For each class:
 *      - Tagged class properties
 *      - Methods (taking the defined "function" declaration but stripping any { from the list.
 *          tagged method properties
 * 
 * @package pd2md.php
 * @author Daniel Page <daniel@danielpage.com>
 * @copyright Copyright (c) 2021, Daniel Page
 * @license Licensed under the EUPL v1.2 - https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

class makedoc {

    public $source = null;      //Contains the file source to analyse
    public $sourceFileName = null;  //The name of the source file that will be the title of the markdown file we generate
    public $sourceFilePath = null;  //Path to the source file
    public $targetFileName = null;  //Name of the target file;
    public $lineArray = array();    //Array of lines from the source file
    public $lines = null;           //Lines in the array
    public $blockStarts = null;    //On what line does a block start?
    public $blockEnds = null;      //On what line does a block end?
    public $element = null;        //On what line is the element that matches the block?
    public $blocksInFile = 0;       //How many blocks overall in the file?
    public $inClass = false;        //Have we entered a class?
    public $inClassMethod = false;  //Have we entered a method in a class?
    public $lastClass = null;       //What was the last class we saw?
    public $lastMethod = null;      //What was the last method we saw?
    public $nameSpace = null;       //Did we see a namespace?
    public $useArray = array();     //Did we see a use command?


    public function __construct($pathToInputFile, $pathToOutputFile) {
        $this->sourceFileName = basename($pathToInputFile);
        echo "Attempting to parse {$this->sourceFileName}...\n";
        $this->sourceFilePath = $pathToInputFile;
        $this->targetFileName = $pathToOutputFile;
        $source = file_get_contents($pathToInputFile);
        if($source === null) {
            die("Cannot load source file $pathToInputFile\n");
        }
        
        //Remove any character returns, newlines are considered to be \n only.
        $this->source = str_replace("\r", "", $source);
        //explode the file into an array, one element per line.
        $this->lineArray = explode("\n", $this->source);
        $this->lines = count($this->lineArray); //Remember to -1 on the count as the array starts at 0, but count starts at 1 :)
        echo $this->lines . " lines loaded.\n";
        $this->parseArray();
    }


    public function parseArray() {

        for($i = 0; $i < $this->lines; $i++) {
            //Lets go through each line until we find a /** string:
            if(trim($this->lineArray[$i]) == '/**') {
                //We've got one!
                $this->blockStarts = $i;
                $this->blocksInFile++;
            }

            //Keep going. If $blockStarts not null, then we need to log the block end
            if(!is_null($this->blockStarts)) {
                if(trim($this->lineArray[$i]) == '*/') {
                    $this->blockEnds = $i;
                }
            }

            //Keep going. If blockstarts and ends are not null then if we have a not null line immediately after, we have all we need for the comment.
            //Process it and then clear all the variables.
            if(!is_null($this->blockStarts) && !is_null($this->blockEnds)) {
                if($i > $this->blockEnds) {
                    if(trim($this->lineArray[$i]) != "") {
                        $this->element = $i;
                        $startLine = $this->blockStarts + 1;
                        $endLine = $this->blockEnds + 1;
                        $elementLine = $this->element +1;
                        //Send it to the block converter
                        //die("Comment block #{$this->blocksInFile} starts on line {$startLine} and ends on line {$endLine} and is linked to an element on line {$elementLine}\n");
                        $this->mdMaker($this->blockStarts, $this->blockEnds, $this->element, $this->blocksInFile);
                    }
                }
            }
        }
    }

    public function mdMaker($from, $to, $element, $blockNumber) {

        $subset = array();
        $output = array();
        $tmpArray = array();

        //Load the identified block into a smaller array.
        $lastElement = null;
        $lastParam = null;
        for($i = $from; $i<= $to; $i++) {
            if(trim($this->lineArray[$i]) != '/**' && trim($this->lineArray[$i]) != '*/') { //ignore starting and ending of the block.
                
                $workingstring = substr(trim($this->lineArray[$i]), 2, strlen(trim($this->lineArray[$i])));    //Get the trimmed string...
                if(substr($workingstring, 0,1) == "@") {    //We have a tag
                    $tmpArray = explode(' ', $workingstring, 2);
                    $lastElement = str_replace('@', '', $tmpArray[0]);    //We have the tag
                    if($lastElement == 'param') {
                        //We need the next element for a unique key
                        $tmpArray2 = explode(' ', $tmpArray[1], 3);
                        $paramName = $tmpArray2[0] . ' ' . $tmpArray2[1]; 
                        $lastParam = $paramName;
                        $output['param'][$paramName] = $tmpArray2[2] . ' ';
                    } else {
                        $lastParam = null;
                        $output[$lastElement] = $tmpArray[1] . ' ';
                    }
                } else {
                    if(is_null($lastElement)) {
                        //We are in the description element.
                        if(trim($workingstring) == "") {
                            //Newline
                            $output['description'] .= "\n\n";
                        } else {
                            //append.
                            if(!array_key_exists('description', $output)) {
                                $output['description'] = $workingstring . ' ';
                            } else {
                                $output['description'] .= $workingstring . ' ';
                            }
                        }
                    } else {
                        if(trim($workingstring) == "") {
                            $workingstring = "\n\n";
                        }
                        //We are still in the previously defined element.
                        if($lastElement == 'param') {
                            $output['param'][$lastParam] .= $workingstring;
                        } else {
                            $output[$lastElement] .= $workingstring;
                        }
                    }
                }
            }
        }

        die(var_dump($output));
    
    }
}

$doc = new makedoc($argv[1], $argv[2]);