<?php

namespace App\Controllers;

class TagsController {

    public function index() {
        $response = new \Symfony\Component\HttpFoundation\Response('Tags!');
        return $response;
    }

}
