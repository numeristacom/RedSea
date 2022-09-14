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
    public $inClass = null;       //What was the last class we saw?
    public $inMethod = null;      //What was the last method we saw?
    public $inFunction = null;      //What was the last function we saw?
    public $inProperty = null;      //Did we see a property comment?
    public $inTrait = null;       //What was the last trait we saw?
    public $fileNamespace = null;       //Did we see a namespace?
    public $useArray = array();     //Did we see a use command?
    public $inComment = false;     //Are we in a block or comment?

    public $lastSeenSection = 'file';   //What was the last main part of the file we saw? File, class, method, function or trait?

    public $mdResult = array();     //This is what the documentation element will be loaded into and re-parsed to output markdown (or what ever extended format you want)


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

        //We will go through every line in the source file one by one.
        for($i = 0; $i < $this->lines; $i++) {  //
            //Lets go through each line until we find a /** string:
            $currentLine = trim($this->lineArray[$i]);
            $this->whereAmI($currentLine);
            //don't get any current structure elements if you are in a comment block

            if($currentLine == '/**') {
                //We've got one!
                $this->blockStarts = $i;
                $this->blocksInFile++;
            }
        

            //Keep going. If $blockStarts not null, then we need to log the block end
            if(!is_null($this->blockStarts)) {
                if($currentLine == '*/') {
                    $this->blockEnds = $i;
                }
            }

            //Keep going. If blockstarts and ends are not null then if we have a not null line immediately after, we have all we need for the comment.
            //Process it and then clear all the variables.
            if(!is_null($this->blockStarts) && !is_null($this->blockEnds)) {
                if($i > $this->blockEnds) {
                    if($currentLine != "") {
                        $this->element = $i;
                        $startLine = $this->blockStarts + 1;
                        $endLine = $this->blockEnds + 1;
                        $elementLine = $this->element +1;
                        
                        //Send it to the block converter
                        //die("Comment block #{$this->blocksInFile} starts on line {$startLine} and ends on line {$endLine} and is linked to an element on line {$elementLine}\n");
                        $this->makeBlockArray($this->blockStarts, $this->blockEnds, $this->element, $this->blocksInFile);
                        
                        //Clear the markers before we get the next block.
                        $this->blockStarts = null;
                        $this->blockEnds = null;
                        $this->element = null;
                    }
                }
            }
        }
    }


/**
 * Make an array containing the document block.
 * @param mixed $from From where in the line array
 * @param mixed $to To where in the line array
 * @param mixed $element Where is the element that the phpdoc block is associated with in the line array
 * @param mixed $blockNumber What is the number of the block?
 * @return never 
 */
    public function makeBlockArray($from, $to, $element, $blockNumber) {

        $subset = array();
        $output = array();
        $tmpArray = array();

        //Try to get what element we are working on...

        //Load the identified block into a smaller array.
        $lastElement = null;
        $lastParam = null;
        for($i = $from; $i<= $to; $i++) {
            $workingLine = trim($this->lineArray[$i]);

            if($workingLine != '/**' && $workingLine != '*/') { //ignore starting and ending of the block.
                
                $workingstring = substr(trim($this->lineArray[$i]), 2, strlen(trim($this->lineArray[$i])));    //Get the trimmed string...
                if(substr($workingstring, 0,1) == "@") {    //We have a tag
                    $tmpArray = explode(' ', $workingstring, 2);
                    $lastElement = str_replace('@', '', $tmpArray[0]);    //We have the tag
                    if($lastElement == 'property') {
                        $this->inProperty = true;
                    }
                    if($lastElement == 'param') {
                        //We need the next element for a unique key
                        $tmpArray2 = explode(' ', $tmpArray[1], 3); //Split param into 3
                        $paramName = $tmpArray2[0] . ' ' . $tmpArray2[1]; //Get the resulting type and variable name as the parameter name array key
                        $lastParam = $paramName;
                        $output['param'][$paramName] = $tmpArray2[2] . ' '; //And append the rest of the description to that key.
                    } else {
                        $lastParam = null;  //Clear the last param
                        $output[$lastElement] = $tmpArray[1] . ' '; //and store the key and value in the output array.
                    }
                } else {
                    if(is_null($lastElement)) {
                        //We are in the description element.
                        if(trim($workingstring) == "") {
                            //Newline
                            $output['description'] .= "\n\n"; //Blank line = newline.
                        } else {
                            if(!array_key_exists('description', $output)) {
                                $output['description'] = $workingstring . ' '; //add description
                            } else {
                                $output['description'] .= $workingstring . ' '; //Append
                            }
                        }
                    } else {
                        if(trim($workingstring) == "") {
                            $workingstring = "\n\n";    //Blank line = newline
                        }
                        //We are still in the previously defined element.
                        if($lastElement == 'param') {
                            $output['param'][$lastParam] .= $workingstring; //Append to the last known parameter/variable key
                        } else {
                            $output[$lastElement] .= $workingstring;    //or just append to the last known element key
                        }
                    }
                }
            }
        }

        //Where do we put the result in the final working array?

        //Are we in a file?
        //Are we in a file property?
        //Are we in a class?
            //Are we in a class property?
            //Are we in a class's method?
                //Are we in a class's method's property?
        //Are we in a trait?
        //Are we in a function?
            //Are we in a function's property?
        
        if(!is_null($this->inClass)) {

            if(!is_null($this->inProperty)) {

            } else 

            if(!is_null($this->inMethod)) {
                // In a class method
                $this->mdResult[$this->inMethod] = $output;
            } else {
                //Just in a class.
                $this->mdResult[$this->inClass] = $output;
            }
        } else {
            $this->mdResult[$this->lastSeenSection] = $output;
        }
        
        $this->inProperty = null;   //Reset this or it will bite us later in another loop...

        die(var_dump($output));
    }

    /**
     * Check the current line and identify if we are at the file level, inside a class, inside a class method, or just working on a plain function?
     * @param mixed $currentLine 
     * @return void 
     */
    public function whereAmI($currentLine) {
        /*
        We are looking for the following patterns outside a phpdoc block:
        class something {
        public/private/protected function something() {
        function somthing {}
        trait somthing {}
        */
        if(strlen($currentLine) > 2) {
            if(substr($currentLine, 0, 2) == "/*") {    //We have a comment
                $this->inComment = true;
            }
            
            if(substr($currentLine, -2) == '*/') {  //The comment could also end on the same line, so we can't do an elseif here.
                $this->inComment = false;
            }
        }
        if(!$this->inComment) { //only if we are not in a comment somehow.
            if($currentLine != "") {

                /*
                PHP keywords are not case sensitive. I tested. The cursed code below runs perfectly.
                <?php
                FuNcTiOn sOmThIng() {
                    eCho "Hello World";
                }
                SoMtHiNg();
                ?>
                //So we need to waste some cycles on people who may write cursed code by matching on lower case,
                then processing the original string for the final output
                */

                $check = strtolower($currentLine);  
                $lineData = preg_split("/[\s,]+/", $check);
                $real = preg_split("/[\s,]+/", str_replace('{', '', $currentLine));
                switch($lineData[0]) {
                    case "class":
                        $this->inClass = $real[0] . ' ' . $real[1];
                        $this->lastSeenSection = $this->inClass;
                        break;
                    case "public":
                    case "private":
                    case "protected":
                        $this->inMethod = $real[0] . ' ' . $real[1] . ' ' . $real[2];
                        // No need to set this as a last seen section. We are part of a class - if we picked that up properly...
                        break;
                    case "function":
                        $this->inFunction = $real[1] . ' ' . $real[2];
                        $this->lastSeenSection = $this->inFunction;
                        break;
                    case "trait":
                        $this->inFunction = null;
                        $this->inClass = null;
                        $this->inTrait = $real[0] . ' ' . $real[1];
                        $this->lastSeenSection = $this->inTrait;
                        break;
                    case "use":
                        //We got a namespace to use:
                        $this->useArray[] = $real[0] . ' ' . $real[1];
                        break;
                    case "namespace":
                        $this->fileNamespace = $real[0] . ' ' . $real[1];
                        break;
                }
            }
        }
    }
}


$doc = new makedoc($argv[1], $argv[2]);