<?php
/**
 * This file defines internal debug services to the RedSea class library.
 * @author Daniel Page <daniel@danielpage.com>
 * @copyright Copyright (c) 2021, Daniel Page
 * @license Licensed under the EUPL v1.2 - https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 */

namespace RedSea;

/**
* Static class providing debug reporting services to the RedSea library and usable in any custom 
* code that implements this class library
*/
class d {
    /**
     * This public flag is set if an error message has been set and is waiting to be read.
     * If True: There is a pending error.
     * If False: There is no error to read.
     */
    public static $errorFlag = false;

    /**
     * Stop script execution and display error details even if the debug state is 0 (unset);
     * @var false
     */
    public static $dieOnError = false;
    
    /** 
     * Stores the state of the debug class. If set to TRUE, then the dbg function will generate output
     * It is set by the setDebugLevel method.
     * Possible values are:
     * - 0: No debug (off)
     * - 1: Application flow: Display notifications as long as there is no other details to display.
     * This is the equivalent of calling d:dbg();
     * - 2: Display message (if set), method name, file and line but not method arguments
     * - 3: Display message (if set), method name, file, line and method arguments 
     * @see rsDebug::setDebugLevel()
     * @internal
     */
    public static $debugLevel = 0;

    /**
     * Stores the last error message in case of errors.
     * It should only be accessed by dbg or getLastError
     * @internal
     * */
    public static $lastErrorMessage = null;

    /**
     * This is a helper method for the dbg method. It will take the set error message to display
     * and store it before calling the dbg method to display the error as dbg only renders the error and trace to the screen
     * and does not store it for later error processing.
     * @param mixed|null $message 
     * @param mixed|null $optionalData 
     * @return void 
     */
    static function dbgError($message=null, $optionalData=null) {
        self::$lastErrorMessage = $message;
        if(self::$dieOnError) {
            self::$debugLevel = 3;
        }
        d::dbg($message, $optionalData, true);
        if(self::$dieOnError) {
            die("Die On Error flag set. Program halted.");
        }
    }

    /**
     * Output debug information to the screen, depending on the state of the self:$debugLevel property of the class.
     * @param string $message Message or string to output. If null, no output will be generated.
     * @param $optionalData Variable data to be displayed. Can by any PHP data type. If null, no output for this data will be generated.
     * @param bool $functionArguments If TRUE, pull the arguments supplied to the parent function or method that called this method
     * @param bool $backtraceOneMore Will be set if the call comes from the dbgError method and the backtrace needs to ignore
     * the extra intermediate entry in the call stack.
     * @return void There is no return value, output is generated direct to screen/browser.
     */
    static function dbg($message=null, $optionalData=null, $backtraceOneMore=false) {
        
        /*
         * Format of debug message:
         * 0: Nothing - debugging deactivated.
         * 1: Only display "message free" calls to illustrate program flow. No rendering of function call arguments
         * 2: All calls and messages, but no rendering of function/method call arguments
         * 3: Everything.
         */

        // Handles debug levels 1, 2 and 3
        $dbgMessage = null;

        if(self::$debugLevel > 0) {

            if(!empty($message) && self::$debugLevel == 1) {
                //Don't do anything in this case as we are in level 1 flow mode and we
                //need to ignore anything with a message.
                return false;
            } 
            
            // All other cases and debug levels, we need the backtrace data.
            $dbgBacktrace = debug_backtrace();

            if($backtraceOneMore == false) {
                $backTraceIndexModifier = 0;
            } else {
                $backTraceIndexModifier = 1;
            }
        
            $dbgQuickStackTrace = null;
            if(isset($dbgBacktrace[1 + $backTraceIndexModifier]['class'])) {
                $dbgQuickStackTrace .= 'Class: ' . $dbgBacktrace[1 + $backTraceIndexModifier]['class'] . RS_EOL;
            }

            if(isset($dbgBacktrace[1 + $backTraceIndexModifier]['function'])) {
                $dbgQuickStackTrace .= 'Function: ' . $dbgBacktrace[1 + $backTraceIndexModifier]['function'] . RS_EOL;
            }

            $dbgQuickStackTrace .= 'Source: ' . $dbgBacktrace[0 + $backTraceIndexModifier]['file'] . ":" . $dbgBacktrace[0 + $backTraceIndexModifier]['line'] . RS_EOL;

            if(isset($dbgBacktrace[2 + $backTraceIndexModifier]['file'])) {
                $dbgQuickStackTrace .= 'Called from: ' . $dbgBacktrace[2 + $backTraceIndexModifier]['file'] . ':'. $dbgBacktrace[2 + $backTraceIndexModifier]['line'] . RS_EOL;
            }

            $dbgMessage = $dbgQuickStackTrace;

            if(RS_CLI) {
                $startLine = '';
                $endLine = '';
                $newLine = "\r\n";
            } else {
                $startLine = '<small><i>';
                $endLine = '</i></small>';
                $newLine = "<br>";
            }
            

            if(!is_null($optionalData)) {
                $dbgMessage = $startLine .  $optionalData . $endLine . $newLine . $startLine .  $dbgQuickStackTrace . $endLine;
            } else {
                $dbgMessage = $startLine .  $dbgQuickStackTrace . $endLine;
            }
            


            if(self::$debugLevel > 1) {
                //Handles debug levels 2 and 3
                if(!empty($message)) {
                    $dbgMessage  = $message . RS_EOL . RS_EOL . $dbgMessage;
                } else {
                    $dbgMessage = "Flow Control" . $message . RS_EOL . RS_EOL . $dbgMessage;
                }
                
            }

            //Prepare debug 2
            if(self::$debugLevel > 2) {
                //Handles debug level 3
                $dbgMessage .= RS_EOL;
                if(isset($dbgBacktrace[1 + $backTraceIndexModifier]['function'])) {
                    if(count($dbgBacktrace[1 + $backTraceIndexModifier]['args']) == 0) {
                        $dbgMessage .= $startLine . "No parameters supplied" . $endLine;
                    } else {
                        $dbgMessage .= $startLine . "Parameters:" . RS_EOL . str_replace(PHP_EOL, RS_EOL, var_export($dbgBacktrace[1 + $backTraceIndexModifier]['args'], true)) . $endLine;
                    }
                }   
            }
            

            //Final output
            if(RS_CLI) {
                echo RS_HR . $dbgMessage . RS_HR;
            } else {
                echo '<div style="background:#fc8403;">' .  $dbgMessage . '</div>'. RS_HR;
            }
        }
    }

    /**
     * Set an error message.
     * @param string $err Error to store in the class.
     * @return void
     * @internal
     */
    protected function setLastError($err=null) {
        self::$errorFlag = true;
        self::$lastErrorMessage = $err;
    }

    /**
     * Return the last set error message. If no error message is set, NULL will be returned.
     * @param bool $preserveErrorMessage Optional. If TRUE, the error message will not be wiped after returning and will stay in the object.
     * If FALSE the value will be reset to NULL after returning. Default FALSE.
     * @return string containing the last known error. 
     */
    public function getLastError($preserveErrorMessage=false) {
        if($preserveErrorMessage) {
            return self::$lastErrorMessage;
        } else {
            $err = self::$lastErrorMessage;
            self::$lastErrorMessage = null;
            self::$errorFlag = false;
            return $err;
        }
    }    
}

/**
* A simple class that starts and stops a timer
*/
class timer {
    /**
     * Stores a float value generated by startTimer
     * @internal
     */
    private $dbgTimer = null;
    /**
    
     * Start a timer, storing the current time in the $dbgTimer property in the class.
     * @return void
     */
    static function startTimer() {
        self::$dbgTimer = microtime(true);
    }

    /**
     * Get current runtime in microseconds, without resetting the timer
     * @return bool|int Timer value in microseconds or FALSE if timer was not started.
     */
    public static function getElapsedTime() {
        if(self::$dbgTimer == null) {
            return false;
        } else {
            return microtime(true) - self::$dbgTimer;
        }
    }

    /**
     * Reset the timer and return elapsed time based on a start time set in the $dbgTimer property.
     * @return number of microseconds since the startTimer method was called, or FALSE if the timer was not set.
     */
    public static function stopTimer() {
        $ret = self::getElapsedTime();
        self::$dbgTimer = null;
        return $ret;
    }
}

?>
