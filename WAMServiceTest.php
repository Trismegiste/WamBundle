<?php

/**
 * Description of WAMServiceTest
 *
 * @author flo
 */
class WAMServiceTest extends PHPUnit_Framework_TestCase
{

    protected function checkSuccess($solve)
    {
        $this->assertCount(1, $solve);
        $this->assertTrue($solve[0]->succeed);
    }

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
    public function testFamilyTree(WAMService $wam)
    {
        $solve = $wam->runQuery("equal(luke, luke).");
        $this->checkSuccess($solve);

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

    public function testFixtures2()
    {
        $wam = new WAMService(new Program());

        $solve = $wam->runQuery("consult('fixtures2.pro').");
        $this->checkSuccess($solve);

        return $wam;
    }

    /**
     * @depends testFixtures2
     */
    public function testArithmetics(WAMService $wam)
    {
        $solve = $wam->runQuery("factorial(6, X).");
        $this->assertCount(2, $solve);
        $this->assertTrue($solve[0]->succeed);
        $this->assertEquals(720, $solve[0]->variable['X']);
        $this->assertFalse($solve[1]->succeed);
    }

    /**
     * @depends testFixtures2
     */
    public function testLists(WAMService $wam)
    {
        $tab = range(0, 13);
        $hypothesisX = '[' . implode(', ', $tab) . ']';
        $hypothesisR = '[' . implode(', ', array_reverse($tab)) . ']';
        shuffle($tab);
        $chaos = '[' . implode(', ', $tab) . ']';
        $solve = $wam->runQuery("qsort($chaos, X).");
        $this->assertCount(2, $solve);
        $this->assertTrue($solve[0]->succeed);
        $this->assertEquals($hypothesisX, $solve[0]->variable['X']);
        $this->assertFalse($solve[1]->succeed);

        $solve = $wam->runQuery("qsort($chaos, X), reverse(X, R), length(R, N).");
        $this->assertCount(2, $solve);
        $this->assertTrue($solve[0]->succeed);
        $this->assertEquals($hypothesisX, $solve[0]->variable['X']);
        $this->assertEquals($hypothesisR, $solve[0]->variable['R']);
        $this->assertEquals(count($tab), $solve[0]->variable['N']);
        $this->assertFalse($solve[1]->succeed);
    }

    // 'p(S,le,chat)' -> 'snm(determinant(le), nom(chat), masculin)'
}
