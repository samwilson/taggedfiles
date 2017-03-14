<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class AssetsController extends Base
{

    public function css(ServerRequestInterface $request, ResponseInterface $response, array $args)
    {
        $filename = 'assets/' . $args['file'];
        $response->getBody()->write(file_get_contents($filename));
        $response->withHeader('Content-Type', 'text/css');
        return $response;
    }
}
