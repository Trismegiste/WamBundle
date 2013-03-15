<?php

/*
 * WamBundle
 */

namespace Trismegiste\WamBundle\Tests\Command;

use Trismegiste\WamBundle\Command\WAMCommand;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * WAMCommandTest tests the command WAMCommand
 */
class WAMCommandTest extends \PHPUnit_Framework_TestCase
{

    protected $application;

    protected function setUp()
    {
        $this->application = new Application();
        $command = new WAMCommand();

        $this->application->add($command);
    }

    public function testLauch()
    {
        $command = $this->application->find('wam:prolog:console');

        $commandTester = new CommandTester($command);
        $this->assertNotNull($command);
    }

}