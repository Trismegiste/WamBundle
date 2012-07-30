<?php

define('FIXTURES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'WAM_TestCase.php';

spl_autoload_register(function ($class) {
            if (preg_match('#^Trismegiste\\\\WamBundle\\\\(.+)$#', $class, $ret)) {
                $relPath = str_replace('\\', DIRECTORY_SEPARATOR, $ret[1]);
                require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $relPath . '.php';
            }
        });