<?php

/**
 * Bootstrap. This file, the test suite, and the CLI tool all run the same bootstrap.
 */

require_once 'bootstrap.php';

use App\Config;
use League\Container\Container;
use League\Route\RouteCollection;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\SapiEmitter;
use Zend\Diactoros\ServerRequestFactory;

/**
 * Routes.
 */
$container = new Container();
$container->share('response', Response::class);

$container->share('request', function () {
    $config = new Config();
    $_SERVER['REQUEST_URI'] = mb_substr($_SERVER['REQUEST_URI'], mb_strlen($config->baseUrl()));
    return ServerRequestFactory::fromGlobals($_SERVER, $_GET, $_POST, $_COOKIE, $_FILES);
});
$container->share('emitter', SapiEmitter::class);
$router = new RouteCollection($container);

// Assets, installation, and home.
$router->map('GET', '/{file:.*\.(?:css|js)}', 'App\Controllers\AssetsController::css');
$router->map('GET', '', '\App\Controllers\DateController::index');
// Items.
$router->map('GET', '/create', 'App\Controllers\ItemController::edit');
$router->map('POST', '/save', 'App\Controllers\ItemController::save');
$router->map('GET', '/{id:number}', 'App\Controllers\ItemController::view');
$router->map('GET', '/{id:number}/edit', 'App\Controllers\ItemController::edit');
// Dates
$router->map('GET', '/d/{year}', 'App\Controllers\DateController::index');
$router->map('GET', '/d/{year}/{month}', 'App\Controllers\DateController::index');
//Tags
$router->map('GET', '/tags', 'App\Controllers\TagController::index');
$router->map('GET', '/t/{ids:[0-9,]+}', 'App\Controllers\TagController::index');
$router->map('GET', '/t/{id:[0-9]+}/edit', 'App\Controllers\TagController::edit');
// Files
$router->map('GET', '/{id:number}.png', 'App\Controllers\FileController::render');
$router->map('GET', '/{id:number}_{size}.png', 'App\Controllers\FileController::render');
$router->map('GET', '/{id:number}_v{version}_{size}.png', 'App\Controllers\FileController::render');
// Users and groups.
$router->map('GET', '/login', 'App\Controllers\UserController::loginForm');
$router->map('POST', '/login', 'App\Controllers\UserController::login');
$router->map('GET', '/register', 'App\Controllers\UserController::registerForm');
$router->map('POST', '/register', 'App\Controllers\UserController::register');
$router->map('GET', '/remind/{userid}/{token}', 'App\Controllers\UserController::remindResetForm');
$router->map('POST', '/remind/{userid}/{token}', 'App\Controllers\UserController::remindReset');
$router->map('GET', '/remind', 'App\Controllers\UserController::remindForm');
$router->map('POST', '/remind', 'App\Controllers\UserController::remind');
$router->map('GET', '/u/{id:number}', 'App\Controllers\UserController::profile');
$router->map('GET', '/logout', 'App\Controllers\UserController::logout');
$router->map('GET', '/g/{id:number}', 'App\Controllers\GroupController::view');

/**
 * Dispatch.
 */
session_start();
$response = $router->dispatch($container->get('request'), $container->get('response'));
$container->get('emitter')->emit($response);
