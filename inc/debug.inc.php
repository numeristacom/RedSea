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
class debug {
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
     * - 2: Display message (if set), method name, file and line but not method argument
     * - 3 Same as 2 but adding calling function arguments.
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
     * Displays program flow. When no arguments are provided, it can be used to trace through program execution by outputting the 
     * calling function/method's name, file and line number.
     * If parameters are set then these details will be output too.
     * @param string $message Message to display during program execution flow
     * @param variant $optionalData Extra data of any sort that can be useful to debug program flow.
     * @param integer $backUpTraceLevels = The backtrace index to display. 0 is the function itself. 1 (default) is the
     * function that called this function, and 2 is another level back, if another function is used to call flow (such as the err method of the debug class) 
     * @return void 
     */
    static function flow($message=null, $optionalData=null, $backUpTraceLevels = 0) {

        if(self::$debugLevel > 0) {
            //App flow
            $backTrace = debug_backtrace();
            if(count($backTrace) == 1) {
                //Call directly from a php script outside of a function
                $traceIndex = 0 + $backUpTraceLevels;
            } else {
                //Call from some nested function
                $traceIndex = 1 + $backUpTraceLevels;
            }
            print($backTrace[$traceIndex]['function'] . '() - ' . $backTrace[$traceIndex]['file'] . ':' . $backTrace[$traceIndex]['line'] . RS_EOL);
            if(self::$debugLevel > 1) {
                // App flow and flow state
                if(!is_null($message)) {
                    print($message . RS_HR);
                }
                if(!is_null($optionalData)) {
                    print(var_dump($optionalData) . RS_HR);
                }
            }
        }
    }


    /**
     * This is a wrapper to the flow method, but will set the class error flag and store it for future processing.
     * @param mixed|null $message 
     * @param mixed|null $optionalData 
     * @return void 
     */
    static function err($message, $optionalData=null) {
        
        self::$lastErrorMessage = $message;
        debug::flow($message, $optionalData, 1);

        if(self::$dieOnError) {
            //Output a stacktrace and die.
            die(RS_HR . var_dump(debug_backtrace()) . RS_HR . "Die On Error flag set. Program halted.");
        } else {
            self::$lastErrorMessage = $message;
        }
    }

    /**
     * Output a fatal error and stop program execution.
     * @param string $message Message to display during program execution flow
     * @param variant $optionalData Extra data of any sort that can be useful to debug program flow.
     * @return void 
     */
    static function fatal($message, $optionalData) {
        self::$dieOnError = true;
        self::$debugLevel = 2;
        self::err($message, $optionalData);
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

?>
