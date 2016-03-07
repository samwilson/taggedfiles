<?php

/**
 * Composer.
 */
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    echo "Please run <code>composer install</code>";
    exit(1);
}
require __DIR__ . '/vendor/autoload.php';

/**
 * Exception and error handling.
 */
//set_exception_handler(['App\App', 'exceptionHandler']);
//set_error_handler(function ($errno, $errstr, $errfile, $errline) {
//    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
//});
\Eloquent\Asplode\Asplode::install();

/**
 * Configuration file. When testing, the tests/config.php file is used.
 */
define('CONFIG_FILE', __DIR__ . '/config.php');
if (!file_exists(CONFIG_FILE)) {
    echo "Please copy <code>config.example.php</code> to <code>config.php</code> and edit the values therein";
    exit(1);
}
