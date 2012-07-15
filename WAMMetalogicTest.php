<?php

/**
 * Test for WAMService : metalogic : call, cut, assert, retract
 */
class WAMMetalogicTest extends WAM_TestCase
{

    public function testFixtures3()
    {
        $wam = new WAMService(new Program());

        $solve = $wam->runQuery("consult('fixtures3.pro').");
        $this->checkSuccess($solve);

        return $wam;
    }

    /**
     * @depends testFixtures3
     */
    public function testAssert(WAMService $wam)
    {
        $this->fail('not yet implemented');
    }

    /**
     * @depends testFixtures3
     */
    public function testUnknownCall(WAMService $wam)
    {
        $solve = $wam->runQuery("call(foo).");
        $this->assertCount(1, $solve);
        $this->assertFalse($solve[0]->succeed);
    }

    /**
     * @depends testFixtures3
     */
    public function testUnknownCallSTR(WAMService $wam)
    {
        $solve = $wam->runQuery("call(foo(bar)).");
        $this->assertCount(1, $solve);
        $this->assertFalse($solve[0]->succeed);
    }

}
