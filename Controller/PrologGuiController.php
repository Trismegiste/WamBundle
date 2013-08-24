<?php

namespace Trismegiste\WamBundle\Controller;

use Trismegiste\WamBundle\Prolog;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * Description of PrologGuiController
 */
class PrologGuiController extends Controller
{

    public function runAction()
    {
        $form = $this->createForm(new \Trismegiste\WamBundle\Form\PrologConsole());
        $request = $this->getRequest();
        $result = '';

        if ($request->getMethod() == 'POST') {
            $form->bind($request);
            if ($form->isValid()) {
                $data = $form->getData();
                $machine = $this->get('prolog.wam');
                $compiler = new Prolog\PrologCompiler($machine);
                $prog = str_replace(array("\r", "\n"), '', $data['program']);
                $prog = $compiler->compile($prog);
                $machine->addProgram($prog);
                $result = $machine->runQuery($data['query']);
            }
        }

        return $this->render('TrismegisteWamBundle:PrologGui:run.html.twig', array('form' => $form->createView(), 'output' => $result));
    }

}
