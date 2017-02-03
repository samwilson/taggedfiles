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
 * Exception handling.
 */
set_error_handler(function ($code, $message, $file, $line, $context) {
    throw new ErrorException($code, $message, $file, $line, $context);
});
set_exception_handler([ \App\App::class, 'exceptionHandler' ]);

/**
 * Configuration file. When testing, the tests/config.php file is used.
 */
if (substr(basename($_SERVER['PHP_SELF']), 0, 7)==='phpunit') {
    define('CONFIG_FILE', __DIR__ . '/tests/config.php');
} else {
    define('CONFIG_FILE', __DIR__ . '/config.php');
}
if (!file_exists(CONFIG_FILE)) {
    echo "Please copy <code>config.example.php</code> to <code>".CONFIG_FILE."</code> and edit the values therein";
    exit(1);
}
