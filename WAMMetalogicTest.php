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
        $solve = $wam->runQuery("assert(robot(c3po)).");
        $solve = $wam->runQuery("robot(X).");
        // no backtrack then no ending with failure (a little odd : improvment to do ? don't know)
        $this->checkOneValueSuccess($solve, 'X', 'c3po', false);
    }

    /**
     * @depends testFixtures3
     */
    public function testUnknownCall(WAMService $wam)
    {
        $solve = $wam->runQuery("call(foo).");
        $this->checkFailure($solve);
    }

    /**
     * @depends testFixtures3
     */
    public function testUnknownCallSTR(WAMService $wam)
    {
        $solve = $wam->runQuery("call(foo(bar)).");
        $this->checkFailure($solve);
    }

}
