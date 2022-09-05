<?php
/**
     * Displays program flow. When no arguments are provided, it can be used to trace through program execution by outputting the 
     * calling function/method's name, file and line number.
     * 
     * If parameters are set then these details will be output too.
     * @param string $message Message to display during program execution flow
     * @param variant $optionalData Extra data of any sort that can be useful to debug program flow.
     * @param integer $backUpTraceLevels = The backtrace index to display. 0 is the function itself. 1 (default) is the
     * function that called this function, and 2 is another level back, if another function is used to call flow
     * 
     * (such as the err method of the debug class) 
     * @return void 
     */

     echo('hi');