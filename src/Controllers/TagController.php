<?php

namespace App\Controllers;

use App\TagsIdentifier;
use App\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class TagController extends Base
{

    /**
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return Response
     */
    public function index(Request $request, Response $response, array $args)
    {
        $template = new Template('tags_index.twig');
        $template->title = 'Tags';

        // Validate tag IDs.
        $tags = new TagsIdentifier();
        if (!empty($args['ids'])) {
            $tags->addFromString($args['ids']);
            if ($args['ids'] !== $tags->toString()) {
                // Redirect to the canonical ordering if required.
                return new RedirectResponse($this->config->baseUrl() . "/t/" . $tags->toString());
            }
        }
        $template->tagsIdent = $tags;

        // Get a list of tags to show.
        // Get all items with this tag, and then all tags of that item.
        $tagsSql = "SELECT tags.id, tags.title, count(*) AS item_count "
            . " FROM item_tags selected_tags"
            . " JOIN item_tags related_tags ON selected_tags.item = related_tags.item"
            . " JOIN tags ON related_tags.tag = tags.id"
            . (!$tags->isEmpty() ? " WHERE selected_tags.tag IN (".$tags->toString().") " : '')
            . " GROUP BY tags.id ORDER BY tags.title ASC";
        $params = [];
        $template->tags = $this->db->query($tagsSql, $params);

        // Get the Items with the currently-selected tags.
        if (!$tags->isEmpty()) {
            $sql = "SELECT items.id FROM items JOIN item_tags ON item_tags.item = items.id " .
                " WHERE item_tags.tag IN (" . $tags->toString() . ") GROUP BY items.id LIMIT 20";
            $params = [];
            $items = $this->db->query($sql, $params, '\\App\\Item');
            $template->items = $items;
        }

        // Return.
        $response->setContent($template->render());
        return $response;
    }
}
