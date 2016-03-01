<?php

namespace App\Controllers;

use App\Config;
use App\Db;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class InstallController {

    public function install(Request $request, Response $response, array $args) {
        $template = new \App\Template('install.twig');
        $template->title = 'Install';
        $response->setContent($template->render());
        return $response;
    }

    public function run(Request $request, Response $response, array $args) {
        $db = new Db();
        $db->install();
        $config = new Config();
        return new RedirectResponse($config->baseUrl());
    }

}
