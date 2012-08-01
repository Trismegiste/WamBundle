<?php

use Trismegiste\WamBundle\Prolog\PrologCompiler;
use Trismegiste\WamBundle\Prolog\CompilerStructure;
use Trismegiste\WamBundle\Prolog\WAMService;
use Trismegiste\WamBundle\Prolog\Program;

/**
 * Test for WAMService : example of business rules
 * for e-commerce
 */
class WAMBasketTest extends WAM_TestCase
{

    public function testFixtures()
    {
        $wam = new WAMService();

        $solve = $wam->runQuery("consult('" . FIXTURES_DIR . "basket.pro').");
        $this->checkSuccess($solve);

        return $wam;
    }

    /**
     * @depends testFixtures
     */
    public function testGift1(WAMService $wam)
    {
        $solve = $wam->runQuery("gift([bike], X).");
        $this->assertCount(3, $solve);
        foreach (array('tyre', 'keychain') as $k => $name) {
            $this->assertTrue($solve[$k]->succeed);
            $this->assertEquals($name, $solve[$k]->variable['X']);
        }
        $this->assertFalse($solve[2]->succeed);
    }

    /**
     * @depends testFixtures
     */
    public function testGift2(WAMService $wam)
    {
        $solve = $wam->runQuery("gift([starwars_box, wow], X).");
        $this->checkOneValueSuccess($solve, 'X', 'lightsaber');
    }

    /**
     * @depends testFixtures
     */
    public function testGift3(WAMService $wam)
    {
        $solve = $wam->runQuery("gift([starwars_box, wow, diablo3], X).");
        $this->assertCount(3, $solve);
        foreach (array('lightsaber', 'life') as $k => $name) {
            $this->assertTrue($solve[$k]->succeed);
            $this->assertEquals($name, $solve[$k]->variable['X']);
        }
        $this->assertFalse($solve[2]->succeed);
    }

    /**
     * @depends testFixtures
     */
    public function testDiscount1(WAMService $wam)
    {
        $solve = $wam->runQuery("discount([geforce, wow], X).");
        $this->checkOneValueSuccess($solve, 'X', 20, false);
    }

    /**
     * @depends testFixtures
     */
    public function testDiscount2(WAMService $wam)
    {
        $solve = $wam->runQuery("discount([geforce, bike], X).");
        $this->checkOneValueSuccess($solve, 'X', 50, false);
    }

    /**
     * @depends testFixtures
     */
    public function testDiscount3(WAMService $wam)
    {
        $solve = $wam->runQuery("discount([ultrabook, geforce], X).");
        $this->checkOneValueSuccess($solve, 'X', 1750 / 30, false);
    }

}
