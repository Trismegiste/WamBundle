<?php

require_once('Program.php');
require_once('Statement.php');
require_once('CodeReader.php');

$cr = new CodeReader();
$p = $cr->readProgram('fixtures_test.wim');
echo $p;
?>
