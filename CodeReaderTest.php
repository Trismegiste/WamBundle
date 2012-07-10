<?php

/**
 * Unit test for solver : does some queries and checking no regression
 *
 * @author flo
 */
require_once('Program.php');
require_once('Statement.php');
require_once('CodeReader.php');
require_once('CompilerStructure.php');
require_once('Compiler.php');
require_once('QueryCompiler.php');
require_once('PrologCompiler.php');
require_once('InnerClass.php');
require_once('PrologContext.php');
require_once('WAM.php');

class CodeReaderTest extends PHPUnit_Framework_TestCase
{

    public function testReading()
    {
        $p = CodeReader::readProgram('fixtures_test.wam');
        echo $p;
        echo "\n";
    }

}

