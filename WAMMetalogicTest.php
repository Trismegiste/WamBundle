<?php

/**
 * Test for WAMService : metalogic : call, cut, assert, retract
 */
class WAMMetalogicTest extends WAM_TestCase
{

    public function testFixtures1()
    {
        $wam = new WAMService(new Program());

        $solve = $wam->runQuery("consult('fixtures1.pro').");
        $this->checkSuccess($solve);

        return $wam;
    }

    /**
     * @depends testFixtures1
     */
    public function testMetalogic(WAMService $wam)
    {
        $this->fail('not yet implemented');
    }

}
