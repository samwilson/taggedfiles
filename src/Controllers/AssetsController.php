<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class AssetsController extends \App\Controllers\Base {

    public function css(Request $request, Response $response, array $args) {
        $filename = 'assets/' . $args['file'];
        $response->setContent(file_get_contents($filename));
        $response->headers->set('Content-Type', 'text/css');
        return $response;
    }

}
