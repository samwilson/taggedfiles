<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallController {

    public function install(Request $request, Response $response, array $args) {
        $response = new \Symfony\Component\HttpFoundation\Response();
        $template = new \App\Template('install.twig');
        $template->title = 'Install';
        return \Symfony\Component\HttpFoundation\Response::create($template->render());
    }

    public function run(Request $request, Response $response, array $args) {
        $db = new \App\Db();
        $db->install();
        return new \Symfony\Component\HttpFoundation\RedirectResponse(\App\App::baseurl());
    }

}
