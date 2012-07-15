<?php

/**
 * Test for CodeReader and writer
 *
 * @author flo
 */
class CodeReaderTest extends PHPUnit_Framework_TestCase
{

    public function testCompile()
    {
        $tempFile = tempnam('.', 'wam');
        CodeReader::prologToWamCode('basket.pro', $tempFile);
        return $tempFile;
    }

    /**
     * @depends testCompile
     */
    public function testReading($wamFile)
    {
        $p = CodeReader::readProgram($wamFile);
        $tempFile = tempnam('.', 'wam');
        CodeReader::writeProgram($p, $tempFile);
        $this->assertFileEquals($wamFile, $tempFile);
        unlink($wamFile);
        unlink($tempFile);
    }

}

