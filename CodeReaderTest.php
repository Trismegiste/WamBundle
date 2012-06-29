<?php

require_once('Program.php');
require_once('Statement.php');
require_once('CodeReader.php');
require_once('CompilerStructure.php');
require_once('Compiler.php');
require_once('QueryCompiler.php');
require_once('PrologCompiler.php');
require_once('WAM.php');
/*
$cr = new CodeReader();
$p = $cr->readProgram('fixtures_test.wim');
echo $p;
echo "\n";
 */

$c = new QueryCompiler(new WAM());
$p = $c->compile("pere(X, luke).");

echo $p;

$c = new PrologCompiler(new WAM());
$p = $c->compile("pere(anakin, luke).");

echo $p;