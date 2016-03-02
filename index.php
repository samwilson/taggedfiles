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
set_exception_handler(['App\App', 'exceptionHandler']);
set_error_handler(function ($errno, $errstr, $errfile, $errline ) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});

/**
 * Configuration file. When testing, the tests/config.php file is used.
 */
define('CONFIG_FILE', __DIR__ . '/config.php');
if (!file_exists(CONFIG_FILE)) {
    echo "Please copy <code>config.example.php</code> to <code>config.php</code> and edit the values therein";
    exit(1);
}

/**
 * Routes.
 */
$router = new League\Route\RouteCollection();
// Assets, installation, and home.
$router->addRoute('GET', '/{file:.*\.(?:css|js)}', 'App\Controllers\AssetsController::css');
$router->addRoute('GET', '/install', 'App\Controllers\InstallController::install');
$router->addRoute('POST', '/install', 'App\Controllers\InstallController::run');
$router->addRoute('GET', '/', 'App\Controllers\TagController::index');
// Items.
$router->addRoute('GET', '/create', 'App\Controllers\ItemController::edit');
$router->addRoute('POST', '/save', 'App\Controllers\ItemController::save');
$router->addRoute('GET', '/{id:number}', 'App\Controllers\ItemController::view');
$router->addRoute('GET', '/{id:number}/edit', 'App\Controllers\ItemController::edit');
// Files
$router->addRoute('GET', '/{id:number}.png', 'App\Controllers\FileController::render');
$router->addRoute('GET', '/{id:number}_{size}.png', 'App\Controllers\FileController::render');
$router->addRoute('GET', '/{id:number}_v{version}_{size}.png', 'App\Controllers\FileController::render');
// Users and groups.
$router->addRoute('GET', '/login', 'App\Controllers\UserController::loginForm');
$router->addRoute('POST', '/login', 'App\Controllers\UserController::login');
$router->addRoute('GET', '/register', 'App\Controllers\UserController::registerForm');
$router->addRoute('POST', '/register', 'App\Controllers\UserController::register');
$router->addRoute('GET', '/remind', 'App\Controllers\UserController::remindForm');
$router->addRoute('POST', '/remind', 'App\Controllers\UserController::remind');
$router->addRoute('GET', '/u/{id:number}', 'App\Controllers\UserController::profile');

/**
 * Dispatch.
 */
$dispatcher = $router->getDispatcher();
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
$response->prepare($request);
$response->send();
