<?php

use Trismegiste\WAMBundle\WAMConsole;

/**
 * Example of using WAMConsole
 */
spl_autoload_register(function ($class) {
            preg_match('#([^\\\\]+)$#', $class, $ret);
            require_once $ret[1] . '.php';
        });

WAMConsole::main($argv);
