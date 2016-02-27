<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Base {

    public function loginForm(Request $request, Response $response, array $args) {
        $template = new \App\Template('login.twig');
        $template->title = 'Log in';
        $response->setContent($template->render());
        return $response;
    }

    public function login(Request $request, Response $response, array $args) {
        
    }
}
