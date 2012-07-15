<?php

/**
 * This is a prototype for testing WAMService
 *
 * @author flo
 */
class WAM_TestCase extends PHPUnit_Framework_TestCase
{

    protected function checkSuccess($solve)
    {
        $this->assertCount(1, $solve);
        $this->assertTrue($solve[0]->succeed);
    }

    protected function checkOneValueSuccess($solve, $key, $value)
    {
        $this->checkOneSolutionSuccess($solve, array($key => $value));
    }

    protected function checkOneSolutionSuccess($solve, array $expected)
    {
        $this->assertCount(2, $solve);
        $this->assertTrue($solve[0]->succeed);
        $this->assertCount(count($expected), $solve[0]->variable);
        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $solve[0]->variable);
            $this->assertEquals($value, $solve[0]->variable[$key]);
        }
        $this->assertFalse($solve[1]->succeed);
    }

}

?>
