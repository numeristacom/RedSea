<?php

include("../redsea.inc.php");
function myFunction() {
$arr = array('key1' => 1, 'key2' => 'value 2');
    RedSea\debug::$debugLevel = 2;
RedSea\debug::fatal('Something went wrong', $arr);
}

myFunction();
?>
