<?php

namespace App\Controllers;

use App\App;
use App\Item;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;

class FileController extends Base {

    public function render(Request $request, Response $response, array $args) {
        $item = new Item($args['id']);
        $size = isset($args['size']) ? $args['size'] : 'o';

        $manager = new ImageManager();
        $image = $manager->make($item->getFileStream());
        var_dump($image->basePath());
        exit();
        return $image->response();

        $fileResponse = new StreamedResponse();
        $fileResponse->setCallback(function () use ($item) {
            echo $item->getFileContents();
        });
        $fileResponse->headers->set('Content-Type', 'image/png');
        return $fileResponse;
    }

}
