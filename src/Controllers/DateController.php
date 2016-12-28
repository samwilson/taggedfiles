<?php

namespace App\Controllers;

use App\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DateController extends Base
{
    public function index(Request $request, Response $response, array $args)
    {
        $template = new Template('dates_index.twig');

        $sql = "SELECT items.id FROM items "
               . " JOIN groups ON groups.id = items.read_group "
               //. " WHERE groups.id = "
               . " ORDER BY items.date DESC LIMIT 20";
        $params = [];
        $items = $this->db->query($sql, $params, '\\App\\Item');
        $template->items = $items;
        $template->title = 'D';

        $response->setContent($template->render());
        return $response;
    }
}
