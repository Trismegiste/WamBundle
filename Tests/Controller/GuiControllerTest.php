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
    }

}