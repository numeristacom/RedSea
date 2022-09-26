timer.inc.php

# `class timer`


## `static function startTimer()`

Start a timer, storing the current time in the $dbgTimer property in the class.

 * **Returns:** `void` — 

## `public static function getElapsedTime()`

Get current runtime in microseconds, without resetting the timer

 * **Returns:** `bool|int` — Timer value in microseconds or FALSE if timer was not started.

## `public static function stopTimer()`

Reset the timer and return elapsed time based on a start time set in the $dbgTimer property.

 * **Returns:** `number` — Number of microseconds since the startTimer method was called, or FALSE if the timer was not set.