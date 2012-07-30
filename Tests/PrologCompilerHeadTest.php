<?php

use Trismegiste\WamBundle\Prolog\PrologCompiler;
use Trismegiste\WamBundle\Prolog\CompilerStructure;
use Trismegiste\WamBundle\Prolog\WAMService;
use Trismegiste\WamBundle\Prolog\Program;

/**
 * Test Prolog Compiler for simple clauses
 * Check the resulting CompilerStructure
 *
 * @author flo
 */
class PrologCompilerHeadTest extends PHPUnit_Framework_TestCase
{

    private $compiler = null;

    protected function setUp()
    {
        $this->compiler = new PrologCompiler(new WAMService(new Program()));
    }

    protected function tearDown()
    {
        unset($this->compiler);
    }

    public function testProgram()
    {
        $programList = $this->compiler->stringToList("mother(shmi, anakin).");
        $struc = new CompilerStructure();
        $this->compiler->program($programList, $struc);
        $this->assertEquals(CompilerStructure::PROGRAM, $struc->type);
        return $struc->head;
    }

    /**
     * @depends testProgram
     */
    public function testClause(CompilerStructure $struc)
    {
        $this->assertEquals(CompilerStructure::CLAUSE, $struc->type);
        $this->assertNull($struc->tail);
        return $struc->head;
    }

    /**
     * @depends testClause
     */
    public function testClauseHead(CompilerStructure $struc)
    {
        $this->assertEquals(CompilerStructure::HEAD, $struc->type);
        return $struc;
    }

    /**
     * @depends testClauseHead
     */
    public function testPredicate(CompilerStructure $struc)
    {
        $struc = $struc->head;
        $this->assertEquals(CompilerStructure::PREDICATE, $struc->type);
        $this->assertEquals('mother', $struc->value);
    }

    /**
     * @depends testClauseHead
     */
    public function testList(CompilerStructure $struc)
    {
        $struc = $struc->tail;
        $this->assertEquals(CompilerStructure::LISTX, $struc->type);
        return $struc;
    }

    /**
     * @depends testList
     */
    public function testTerm1(CompilerStructure $struc)
    {
        $struc = $struc->head;
        $this->assertEquals(CompilerStructure::CONSTANT, $struc->type);
        $this->assertEquals('shmi', $struc->value);
    }

    /**
     * @depends testList
     */
    public function testTerm2(CompilerStructure $struc)
    {
        $struc = $struc->tail;
        $this->assertEquals(CompilerStructure::LISTX, $struc->type);
        $struc = $struc->head;
        $this->assertEquals(CompilerStructure::CONSTANT, $struc->type);
        $this->assertEquals('anakin', $struc->value);
    }

}
