<?php

define('FIXTURES_DIR', __DIR__ . DIRECTORY_SEPARATOR . 'fixtures' . DIRECTORY_SEPARATOR);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'WAM_TestCase.php';

spl_autoload_register(function ($class) {
            $found = preg_match('#^Trismegiste\\\\WamBundle\\\\(.+)$#', $class, $ret);
            if (!$found) trigger_error ("Class $class not autoloaded.");
            $relPath = str_replace('\\', DIRECTORY_SEPARATOR, $ret[1]);
            require_once __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . $relPath . '.php';
        });