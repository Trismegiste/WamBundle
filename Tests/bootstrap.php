<?php

define('FIXTURES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'WAM_TestCase.php';

spl_autoload_register(function ($class) {
            preg_match('#([^\\\\]+)$#', $class, $ret);
            require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $ret[1] . '.php';
        });