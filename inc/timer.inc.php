<?php 

namespace RedSea;
    /**
     * @class timer
     * A simple class that starts and stops a timer
     * Stores a float value generated by startTimer
     */
class timer {

    private static $dbgTimer = null;
    
    /**
     * Start a timer, storing the current time in the $dbgTimer property in the class.
     * @return void
     */
    static function startTimer() {
        self::$dbgTimer = microtime(true);
    }

    /**
     * Get current runtime in microseconds, without resetting the timer
     * @return bool|int
     * Timer value in microseconds or FALSE if timer was not started.
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
     * @return number
     * Number of microseconds since the startTimer method was called, or FALSE if the timer was not set.
     */
    public static function stopTimer() {
        $ret = self::getElapsedTime();
        self::$dbgTimer = null;
        return $ret;
    }
}
?>