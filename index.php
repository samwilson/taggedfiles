<?php

/**
 * Bootstrap. This file, the test suite, and the CLI tool all run the same bootstrap.
 */
require_once 'bootstrap.php';

/**
 * Routes.
 */
$router = new League\Route\RouteCollection();
// Assets, installation, and home.
$router->addRoute('GET', '/{file:.*\.(?:css|js)}', 'App\Controllers\AssetsController::css');
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
$router->addRoute('GET', '/remind/{userid}/{token}', 'App\Controllers\UserController::remindResetForm');
$router->addRoute('POST', '/remind/{userid}/{token}', 'App\Controllers\UserController::remindReset');
$router->addRoute('GET', '/remind', 'App\Controllers\UserController::remindForm');
$router->addRoute('POST', '/remind', 'App\Controllers\UserController::remind');
$router->addRoute('GET', '/u/{id:number}', 'App\Controllers\UserController::profile');
$router->addRoute('GET', '/logout', 'App\Controllers\UserController::logout');
$router->addRoute('GET', '/g/{id:number}', 'App\Controllers\GroupController::view');

/**
 * Dispatch.
 */
session_start();
$dispatcher = $router->getDispatcher();
$request = \Symfony\Component\HttpFoundation\Request::createFromGlobals();
$response = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());
$response->prepare($request);
$response->send();
