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
    }

    /**
     * @depends testFixtures3
     */
    public function testUnknownCallSTR(WAMService $wam)
    {
        $solve = $wam->runQuery("call(foo(bar)).");
    }

}
