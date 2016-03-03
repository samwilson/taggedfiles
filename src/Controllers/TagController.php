<?php

namespace App\Controllers;

use App\App;
use App\Config;
use App\Item;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TagController extends Base
{

    public function index(Request $request, Response $response, array $args)
    {
        $sql = "SELECT items.* FROM items "
            . " JOIN groups ON groups.id = items.read_group "
            //. " WHERE groups.id = "
            . " ORDER BY RAND() LIMIT 20";
        $items = $this->db->query($sql);
        $template = new \App\Template('home.twig');
        $template->items = $items;
        $template->title = 'Home';
        $response->setContent($template->render());
        return $response;
    }
}
