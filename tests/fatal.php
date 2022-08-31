<?php

include("../redsea.inc.php");
function f() {
    RedSea\debug::$debugLevel = 0;
RedSea\debug::fatal('Fatal error test', array(1, 2));
}

f();
?>
