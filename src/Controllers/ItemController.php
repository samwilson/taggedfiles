<?php

namespace App\Controllers;

use App\App;
use App\Config;
use App\Item;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ItemController extends Base
{

    public function view(Request $request, Response $response, array $args)
    {
        $item = new Item($args['id'], $this->user);
        $template = new \App\Template('view.twig');
        $template->item = $item;
        $template->title = $item->getTitle();
        $template->tags = $item->getTags();
        $template->mime_type = $item->getMimeType();
        $template->itemReadGroup = $item->getReadGroup();
        $template->itemEditGroup = $item->getEditGroup();
        // Return the template.
        $response->setContent($template->render());
        return $response;
    }

    public function edit(Request $request, Response $response, array $args)
    {
        $template = new \App\Template('form.twig');
        $template->title = 'Create';

        // Date granularities.
        $sql = "SELECT id, title FROM date_granularities ORDER BY id ASC";
        $template->date_granularities = $this->db->query($sql)->fetchAll();

        // Item.
        $item = new Item();
        if (isset($args['id'])) {
            $template->title = 'Editing #' . $args['id'];
            $item = new Item($args['id']);
        }
        $template->item = $item;

        // User.
        $item->setUser($this->user);
        if ($this->user->getId() === false) {
            $msg = 'You have to '
                . ' <a href="' . $this->config->baseUrl() . '/login" class="alert-link">log in</a> '
                . ' before you can add or edit items.';
            $template->alert('info', $msg);
            $response->setContent($template->render());
            return $response;
        }
        $template->groups = $this->user->getGroups();

        // Other template data.
        $template->fileContents = $item->getFileContents();
        $template->itemTags = $item->getTagsString();
        $template->itemReadGroupId = $item->getReadGroup()->id;
        $template->itemEditGroupId = $item->getEditGroup()->id;
        $response->setContent($template->render());
        return $response;
    }

    public function save(Request $request, Response $response, array $args)
    {
        $_POST = array_filter($_POST, 'trim');
        $metadata = array(
            'id' => filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT),
            'title' => filter_input(INPUT_POST, 'title'),
            'description' => filter_input(INPUT_POST, 'description'),
            'date' => filter_input(INPUT_POST, 'date'),
            'date_granularity' => filter_input(INPUT_POST, 'date_granularity', FILTER_SANITIZE_NUMBER_INT),
            'edit_group' => filter_input(INPUT_POST, 'edit_group', FILTER_SANITIZE_NUMBER_INT),
            'read_group' => filter_input(INPUT_POST, 'read_group', FILTER_SANITIZE_NUMBER_INT),
        );
        $tags = filter_input(INPUT_POST, 'tags');
        $item = new Item(null, $this->user);
        $item->save($metadata, $tags, $_FILES['file']['tmp_name'], filter_input(INPUT_POST, 'file_contents'));

        $config = new Config();
        return new RedirectResponse($config->baseUrl() . '/' . $item->getId());
    }
}
