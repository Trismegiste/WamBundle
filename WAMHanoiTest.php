<?php

/**
 * Test for WAMService : example of classical non deterministic problem
 */
class WAMHanoiTest extends WAM_TestCase
{

    public function testFixtures()
    {
        $wam = new WAMService(new Program());

        $solve = $wam->runQuery("consult('hanoi.pro').");
        $this->checkSuccess($solve);

        return $wam;
    }

    /**
     * @depends testFixtures
     */
    public function testOutput(WAMService $wam)
    {
        $solve = $wam->runQuery("hanoi(4).");
        $this->checkSuccess($solve);
        $this->assertAttributeContains('transport de milieu sur droite', 'output', $solve[0]);
    }

}
