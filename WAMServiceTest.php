<?php

/**
 * Description of WAMServiceTest
 *
 * @author flo
 */
class WAMServiceTest extends PHPUnit_Framework_TestCase
{

    public function testDebug()
    {
        $wam = new WAMService(new Program());

        $solve = $wam->runQuery("consult('debug.pro').");
        $this->assertCount(1, $solve);
        $this->assertTrue($solve[0]->succeed);
        
        $solve = $wam->runQuery("equal(luke, luke).");
        $this->assertCount(1, $solve);
        $this->assertTrue($solve[0]->succeed);
        
        $solve = $wam->runQuery("grandmere(X, luke).");
        $this->assertCount(3, $solve);
        $this->assertTrue($solve[0]->succeed);
        $this->assertEquals('ruwee', $solve[0]->variable['X']);
        $this->assertTrue($solve[1]->succeed);
        $this->assertEquals('shmi', $solve[1]->variable['X']);
        $this->assertFalse($solve[2]->succeed);

        $solve = $wam->runQuery("grandmere(shmi, X).");
        $this->assertCount(3, $solve);
        $this->assertTrue($solve[0]->succeed);
        $this->assertEquals('luke', $solve[0]->variable['X']);
        $this->assertTrue($solve[1]->succeed);
        $this->assertEquals('leia', $solve[1]->variable['X']);
        $this->assertFalse($solve[2]->succeed);
        
        $solve = $wam->runQuery("frere(luke, leia).");
        $this->assertCount(3, $solve);
        $this->assertTrue($solve[0]->succeed);
        $this->assertTrue($solve[1]->succeed);
        $this->assertFalse($solve[2]->succeed);
    }

}
