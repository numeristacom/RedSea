<?php

include("../redsea.inc.php");

// PDO helper class - set database type, database, host, user, pass for type = mariadb
$db = new RedSea\rsdb('mariadb', 'test', 'localhost', 'test', 'testdb');
/*
$w = new RedSea\writeNewRecord($db, 'upsert_test2');

$w->setField('one', 1);
$w->setField('two', 2);
$w->setField('three', 'three');
$w->setField('four', 'four');
$w->setField('five', '2022-08-30');

$w->insertNewRecord();
*/

$r = new RedSea\readUpdateSingleRecord($db, 'upsert_test2');
$r->addWhere('ut_id', 2);
$r->loadOneRecord();

echo "ID " . $r->getField('ut_id') . "\n";
echo "one " . $r->getField('one') . "\n";
echo "two " . $r->getField('two') . "\n";
echo "three " . $r->getField('three') . "\n";
echo "four " . $r->getField('four') . "\n";
echo "five " . $r->getField('five') . "\n";

// Now to update the values and push them back into the database.
$r->setField('three', "Something else!");

$r->updateRecord();

?>