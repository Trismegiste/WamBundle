<?php

use Trismegiste\WamBundle\Prolog\WAMConsole;

/**
 * Example of using WAMConsole
 */
spl_autoload_register(function ($class) {
            if (preg_match('#^Trismegiste\\\\WamBundle\\\\(.+)$#', $class, $ret)) {
                $relPath = str_replace('\\', DIRECTORY_SEPARATOR, $ret[1]);
                require_once __DIR__ . DIRECTORY_SEPARATOR . $relPath . '.php';
            }
        });

WAMConsole::main($argv);
