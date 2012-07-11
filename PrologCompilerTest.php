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

    public function testClause1()
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

    public function testClause2()
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

}

?>
