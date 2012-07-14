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
        $this->checkOneValueSuccess($solve, 'X', 720);
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
        $this->checkOneValueSuccess($solve, 'X', $hypothesisX);

        $solve = $wam->runQuery("qsort($chaos, X), reverse(X, R), length(R, N).");
        $this->checkOneSolutionSuccess($solve, array('X' => $hypothesisX, 'R' => $hypothesisR, 'N' => count($tab)));
    }

    /**
     * @depends testFixtures2
     */
    public function testStructure(WAMService $wam)
    {
        $solve = $wam->runQuery('p(S,le,chat).');
        $this->checkOneValueSuccess($solve, 'S', 'snm(determinant(le), nom(chat), masculin)');
    }

}
