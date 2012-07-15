<?php

/**
 * Example of using WAMConsole
 */
spl_autoload_register(function ($class) {
            include_once $class . '.php';
        });

WAMConsole::main($argv);
