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
 * Exception handler.
 */
set_exception_handler(['App\App', 'exceptionHandler']);

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
$router = new League\Route\RouteCollection;
$router->addRoute('GET', '/{file:.*\.(?:css|js)}', 'App\Controllers\AssetsController::css');
$router->addRoute('GET', '/install', 'App\Controllers\InstallController::install');
$router->addRoute('POST', '/install', 'App\Controllers\InstallController::run');
$router->addRoute('GET', '/', 'App\Controllers\HomeController::index');
$router->addRoute('GET', '/create', 'App\Controllers\HomeController::edit');
$router->addRoute('POST', '/save', 'App\Controllers\HomeController::save');
$router->addRoute('GET', '/{id:number}', 'App\Controllers\HomeController::view');
$router->addRoute('GET', '/{id:number}.png', 'App\Controllers\FileController::render');
$router->addRoute('GET', '/{id:number}_{size}.png', 'App\Controllers\FileController::render');
$router->addRoute('GET', '/{id:number}/edit', 'App\Controllers\HomeController::edit');
$router->addRoute('GET', '/login', 'App\Controllers\UserController::loginForm');
$router->addRoute('POST', '/login', 'App\Controllers\UserController::login');

/**
 * Dispatch.
 */
$dispatcher = $router->getDispatcher();
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
$response->send();
