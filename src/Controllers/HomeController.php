<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController {

    public function index(Request $request, Response $response, array $args) {
        $db = new \App\DB();
        $items = $db->query("SELECT * FROM items WHERE auth_level = 0 ORDER BY RAND() LIMIT 10");
        $template = new \App\Template('home.twig');
        $template->items = $items;
        $template->title = 'Home';
        $response = new \Symfony\Component\HttpFoundation\Response($template->render());
        return $response;
    }

    public function view(Request $request, Response $response, array $args) {
        
        return $response;
    }

}
