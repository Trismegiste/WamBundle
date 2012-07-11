<?php

require_once('Program.php');
require_once('Statement.php');
require_once('CodeReader.php');
require_once('CompilerStructure.php');
require_once('Compiler.php');
require_once('QueryCompiler.php');
require_once('PrologCompiler.php');
require_once('InnerClass.php');
require_once('PrologContext.php');
require_once('WAM.php');

/**
 * Description of PrologCompilerTest
 *
 * @author flo
 */
class PrologCompilerTest extends PHPUnit_Framework_TestCase
{

    private $compiler = null;

    protected function setUp()
    {
        $this->compiler = new PrologCompiler(new WAM(new Program()));
    }

    protected function tearDown()
    {
        unset($this->compiler);
    }

    public function testClauseUnif()
    {
        $p = $this->compiler->compile('equal(X, X).');
        $wamCode = <<<WAM
equal:        trust_me
              allocate
              get_variable Y0 A0
              get_value Y0 A1
              deallocate
              proceed
WAM;
        $wamCode = explode("\n", $wamCode);
        for ($k = 0; $k < $p->getStatementCount(); $k++)
            $this->assertEquals(0, strcmp(trim($p->getStatement($k)), trim($wamCode[$k])));
    }

    public function testClauseFacts()
    {
        $p = $this->compiler->compile('mother(shmi, anakin). mother(padme, luke).');
        $wamCode = <<<WAM
mother:       try_me_else mother~2
              get_constant shmi A0
              get_constant anakin A1
              proceed
mother~2:     trust_me
              get_constant padme A0
              get_constant luke A1
              proceed
WAM;
        $wamCode = explode("\n", $wamCode);
        for ($k = 0; $k < $p->getStatementCount(); $k++)
            $this->assertEquals(0, strcmp(trim($p->getStatement($k)), trim($wamCode[$k])));
    }

    public function testClauseHorn()
    {
        $p = $this->compiler->compile('grandmother(X, Y) :- mother(X, Z), mother(Z,Y).');
        $wamCode = <<<WAM
grandmother:  trust_me
              allocate
              get_variable Y0 A0
              get_variable Y1 A1
              put_value Y0 A0
              put_value Y2 A1
              call mother  
              put_value Y2 A0
              put_value Y1 A1
              call mother  
              deallocate
              proceed
WAM;
        $wamCode = explode("\n", $wamCode);
        for ($k = 0; $k < $p->getStatementCount(); $k++)
            $this->assertEquals(0, strcmp(trim($p->getStatement($k)), trim($wamCode[$k])));
    }

    public function testClauseNot()
    {
        $p = $this->compiler->compile('not(Call) :- call(Call), !, fail. not(Call).');
        $wamCode = <<<WAM
not:          try_me_else not~2
              allocate
              get_variable Y0 A0
              get_level Y1
              put_value Y0 A0
              call call
              cut Y1
              call fail
              deallocate
              proceed
not~2:        trust_me
              allocate
              get_variable Y0 A0
              deallocate
              proceed
WAM;
        $wamCode = explode("\n", $wamCode);
        for ($k = 0; $k < $p->getStatementCount(); $k++)
            $this->assertEquals(0, strcmp(trim($p->getStatement($k)), trim($wamCode[$k])));
    }

    public function testClauseListAppend()
    {
        $p = $this->compiler->compile('append([], Z, Z). append([A|B], Z, [A|ZZ]) :- append(B, Z, ZZ).');
        $wamCode = <<<WAM
append:       try_me_else append~2
              allocate
              get_constant [] A0
              get_variable Y0 A1
              get_value Y0 A2
              deallocate
              proceed
append~2:     trust_me
              allocate
              get_variable Y0 A0
              unify_list Y3 Y1 Y2
              unify_variable Y0 Y3
              get_variable Y4 A1
              get_variable Y5 A2
              unify_list Y7 Y1 Y6
              unify_variable Y5 Y7
              put_value Y2 A0
              put_value Y4 A1
              put_value Y6 A2
              call append
              deallocate
              proceed
WAM;
        $wamCode = explode("\n", $wamCode);
        for ($k = 0; $k < $p->getStatementCount(); $k++)
            $this->assertEquals(0, strcmp(trim($p->getStatement($k)), trim($wamCode[$k])));
    }

}

?>
