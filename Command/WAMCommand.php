<?php

namespace Trismegiste\WamBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Trismegiste\Prolog\WAMConsole;

/**
 * Wrapper for WAMConsole
 */
class WAMCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this->
                setDefinition(array())->
                setDescription('Run the prolog console with a Warren Abstract Machine')->
                setHelp("The <info>wam:prolog:console</info> command launches a console for Prolog.
It embeds a compiler and a Warren Abstract Machine.
By Stefan Büttcher and Florent Genette")->
                setName('wam:prolog:console');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        WAMConsole::main(array());
    }

}
