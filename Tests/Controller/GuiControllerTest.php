<?php

/*
 * WamBundle
 */

namespace Trismegiste\WamBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * GuiControllerTest tests PrologGuiController example
 */
class GuiControllerTest extends WebTestCase
{

    protected static function createKernel(array $options = array())
    {
        return new Kernel\AppKernel('test', true);
    }

    public function testFormSubmit()
    {
        $client = self::createClient();

        $crawler = $client->request('GET', '/gui');
        $form = $crawler->selectButton('Query')->form();

        $client->submit($form, array(
            'prolog_gui[program]' => 'father(anakin, luke).',
            'prolog_gui[query]' => 'father(X, luke).'
        ));

        $this->assertRegExp('#X = anakin#', $client->getResponse()->getContent());
    }

}