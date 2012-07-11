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
class PrologCompilerUnitTest extends PHPUnit_Framework_TestCase
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

    public function testProgram()
    {
        $programList = $this->compiler->stringToList("mother(shmi, X) :- equal(X, anakin).");
        $struc = new CompilerStructure();
        $this->compiler->program($programList, $struc);
        echo $struc;
    }

}

?>
