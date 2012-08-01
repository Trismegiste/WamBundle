<?php

namespace Trismegiste\WamBundle\Controller;

use Trismegiste\WamBundle\Prolog;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Description of PrologGuiController
 */
class PrologGuiController extends Controller
{

    /**
     * @Template()
     */
    public function runAction()
    {
        $prog = "father(anakin, luke).";
        $query = "father(X, luke).";
        $machine = $this->get('prolog.wam');

        $compiler = new Prolog\PrologCompiler($machine);
        $compiler->compile($prog);
        $result = $machine->runQuery($query);

        return array('prog' => $prog, 'query' => $query, 'output' => $result);
    }

}
