<?php

/**
 * Get the file from the command line, and generate the md file in the target path.
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
    public $blocksInFile = 0;      //How many blocks overall in the file?


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
                        die("Comment block #{$this->blocksInFile} starts on line {$startLine} and ends on line {$endLine} and is linked to an element on line {$elementLine}\n");
                        $this->mdMaker($this->blockStarts, $this->blockEnds, $this->element, $this->blocksInFile);
                    }
                }
            }
        }
    }

    public function mdMaker($from, $to, $element, $blockNumber) {
        if($blockNumber == 1) {
            //Check if we find "@package" in this block. If so, it's a page level doc block. If not, then raise that error.
        }
    }
}

$doc = new makedoc($argv[1], $argv[2]);