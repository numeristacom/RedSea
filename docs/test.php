<?php

/**
 * @class debug
 * Static class providing debug reporting services to the RedSea library and usable in any custom code
 * that implements this class library.
 * 
 * @properties
 * - ``public static $errorFlag``: Flag is set if an error message has been set and is waiting to be read.
 *  - If True: There is a pending error.
 *  - If False: There is no error to read.
 * - ``public static $debugLevel``: Stores the state of the debug class. If set to TRUE, then the dbg function will generate output
 * It is set by the setDebugLevel method.
 * Possible values are:
 *  - 0: No debug (off)
 *  - 1: Application flow: Display notifications as long as there is no other details to display. This is the equivalent of calling d:dbg();
 *  - 2: Display message (if set), method name, file and line but not method argument
 *  - 3 Same as 2 but adding calling function arguments.
 * - ``public static $lastErrorMessage``: Stores the last error message in case of errors. Despite being a public property,
 * it should only be accessed by the ``debug::getLastError()`` method.
 * 
*/
class debug {

     public static $errorFlag = false;   //Error flag property    
     public static $debugLevel = 0;  //debug level property
     public static $lastErrorMessage = null; //last error message property

/**
 * @method flow()
     * Displays program flow. When no arguments are provided, it can be used to trace through program execution by outputting the 
     * calling function/method's name, file and line number.
     * If parameters are set then these details will be output too.
     * @prototype flow(?string $message=null, ?string $optionalData=null, ?int $backUpTraceLevels = 0, ?bool $returnFlow=false)
     * @parameters
     * @param string $message Message to display during program execution flow
     * @param variant $optionalData Extra data of any sort that can be useful to debug program flow.
     * @param integer $backUpTraceLevels = The backtrace index to display. 0 is the function itself. 1 (default) is the
     * function that called this function, and 2 is another level back, if another function is used to call flow (such as the err method of the debug class) 
     * @return void 
     */

     static function flow($message=null, $optionalData=null, $backUpTraceLevels = 0, $returnFlow=false) {
 
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
             $backTraceMessage = $backTrace[$traceIndex]['function'] . '() - ' . $backTrace[$traceIndex]['file'] . ':' . $backTrace[$traceIndex]['line'] . RS_EOL;
             if(self::$debugLevel > 1) {
                 // App flow and flow state
                 if(!is_null($message)) {
                     $backTraceMessage .= $message . RS_EOL;
                     //print($message . RS_HR);
                 }
                 if(!is_null($optionalData)) {
                     $backTraceMessage = var_export($optionalData, true) . RS_EOL;
                     //print(var_dump($optionalData) . RS_HR);
                 }
             }

/**
 * thie
 * is a
 * blocko.
 */

             if($returnFlow) {
                 return $backTraceMessage;
             } else {
                 echo $backTraceMessage;
             }
         }
     }
    }