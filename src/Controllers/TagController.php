<?php

namespace App\Controllers;

use App\App;
use App\Config;
use App\Item;
use App\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TagController extends Base
{

    public function index(Request $request, Response $response, array $args)
    {
        $template = new Template('home.twig');

        // Get a list of tags to show.
        $tagsSql = "SELECT tags.id, tags.title, count(*) AS item_count "
            . " FROM tags JOIN item_tags ON tags.id=item_tags.tag "
            . " GROUP BY tags.id";
        $params = [];
        if (isset($args['id'])) {
            $tagsSql .= " WHERE id = :id";
            $params['id'] = $args['id'];
        }
        $template->tags = $this->db->query($tagsSql, $params);

        // Get the Items with the currently-selected tags.
        $sql = "SELECT items.id FROM items "
            . " JOIN groups ON groups.id = items.read_group "
            //. " WHERE groups.id = "
            . " LIMIT 20";
        $params = [];
        $items = $this->db->query($sql, $params, '\\App\\Item');
        $template->items = $items;
        $template->title = 'Home';

        // Return.
        $response->setContent($template->render());
        return $response;
    }
}
