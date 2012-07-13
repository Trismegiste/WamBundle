<?php

/**
 * Unit test for solver : does some queries and checking no regression
 *
 * @author flo
 */
class CodeReaderTest extends PHPUnit_Framework_TestCase
{

    public function testReading()
    {
        $p = CodeReader::readProgram('fixtures_test.wam');
     /*   echo $p;
        echo "\n";*/
    }

}

