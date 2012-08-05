<?php

namespace Trismegiste\WamBundle\Form;

use Symfony\Component\Form\FormBuilderInterface;

/**
 * Input for Prolog
 */
class PrologConsole extends \Symfony\Component\Form\AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('program', 'textarea')
                ->add('query', 'text');
    }

    public function getName()
    {
        return 'prolog_gui';
    }

}
