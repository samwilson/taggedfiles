<?php

namespace App\Controllers;

use App\App;
use App\Item;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Intervention\Image\Facades\Image;
use Intervention\Image\ImageManager;

class FileController extends Base {

    public function render(Request $request, Response $response, array $args) {
        $item = new Item($args['id']);
        $size = isset($args['size']) ? $args['size'] : 'o';
        $version = isset($args['version']) ? $args['version'] : null;
        $cacheFile = $item->getCachePath($size, $version);

        //dump($cacheFile);
        // Serve the image.
//        $manager = new ImageManager();
//        $image = $manager->make($cacheFile);
//        return $image->response('png');

        $response = new BinaryFileResponse($cacheFile);
        $response->headers->set('Content-Type', 'image/png');
        $response->setFile($cacheFile);
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_INLINE,
            $item->getTitle().'.png'
        );
        //dump($response);

        return $response;
//
//        exit();
//
//        $image = $manager->make($item->getFileStream());
//        var_dump($image->basePath());
//        exit();
//        return $image->response();
//
//        $fileResponse = new StreamedResponse();
//        $fileResponse->setCallback(function () use ($item) {
//            echo $item->getFileContents();
//        });
//        $fileResponse->headers->set('Content-Type', 'image/png');
//        return $fileResponse;
    }

}
