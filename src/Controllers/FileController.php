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

class FileController extends Base
{

    public function render(Request $request, Response $response, array $args)
    {
        $item = new Item($args['id']);
        $size = isset($args['size']) ? $args['size'] : 'o';
        $version = isset($args['version']) ? $args['version'] : null;
        $cacheFile = $item->getCachePath($size, $version);

        $fileResponse = new BinaryFileResponse($cacheFile);
        $fileResponse->headers->set('Content-Type', 'image/png');
        $fileResponse->setFile($cacheFile);
        $fileResponse->setContentDisposition(ResponseHeaderBag::DISPOSITION_INLINE, $item->getTitle() . '.png');
        return $fileResponse;
    }
}
