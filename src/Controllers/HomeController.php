<?php

namespace App\Controllers;

use App\App;
use App\Item;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;

class HomeController {

    /** @var \App\Db */
    protected $db;

    public function __construct() {
        $this->db = new \App\Db();
    }

    public function index(Request $request, Response $response, array $args) {
        $items = $this->db->query("SELECT id, title, description FROM items WHERE auth_level = 0 ORDER BY RAND() LIMIT 10");
        $template = new \App\Template('home.twig');
        $template->items = $items;
        $template->title = 'Home';
        $response->setContent($template->render());
        return $response;
    }

    public function view(Request $request, Response $response, array $args) {
        $item = new Item($args['id']);
        $template = new \App\Template('view.twig');
        $template->item = $item;
        $template->title = $item->getTitle();
        $template->keywords = $item->getKeywords();
        $template->mime_type = $item->getMimeType();
        // Return the template.
        $response->setContent($template->render());
        return $response;
    }

    public function edit(Request $request, Response $response, array $args) {
        $template = new \App\Template('form.twig');
        $template->title = 'Create';
        $item = new Item();
        if (isset($args['id'])) {
            $template->title = 'Editing #' . $args['id'];
            //$params = ['id' => $args['id']];
            //$item = $this->db->query('SELECT * FROM items WHERE id=:id', $params)->fetch();
            $item = new Item($args['id']);
        }
        $template->item = $item;
        $template->fileContents = $item->getFileContents();
        $response->setContent($template->render());
        return $response;
    }

    public function save(Request $request, Response $response, array $args) {
        $_POST = array_filter($_POST, 'trim');
        $metadata = array(
            'id' => filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT),
            'title' => filter_input(INPUT_POST, 'title'),
            'description' => filter_input(INPUT_POST, 'description'),
            'date' => filter_input(INPUT_POST, 'date'),
            'date_granularity' => filter_input(INPUT_POST, 'date_granularity', FILTER_SANITIZE_NUMBER_INT),
            'auth_level' => filter_input(INPUT_POST, 'auth_level', FILTER_SANITIZE_NUMBER_INT),
        );
        $keywords = filter_input(INPUT_POST, 'keywords');
        $item = new \App\Item();
        $item->save($metadata, $keywords, $_FILES['file']['tmp_name'], filter_input(INPUT_POST, 'file_contents'));

        return new RedirectResponse(App::baseurl() . '/' . $item->getId());
    }

}
