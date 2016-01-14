<?php

namespace App\Controllers;

use App\App;
use App\Item;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

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
        $response->setContent($template->render());
        return $response;
    }

    public function save(Request $request, Response $response, array $args) {
        $_POST = array_filter($_POST, 'trim');
        $params = array(
            'id' => filter_input(INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT),
            'title' => filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
            'description' => filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING),
            'date' => filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING),
            'date_granularity' => filter_input(INPUT_POST, 'date_granularity', FILTER_SANITIZE_NUMBER_INT),
            'auth_level' => filter_input(INPUT_POST, 'auth_level', FILTER_SANITIZE_NUMBER_INT),
        );
        $item = new \App\Item();
        $item->save($params);

        // Save the file.
//        if ($_FILE['file']) {
//            $storage = new \Upload\Storage\FileSystem(\App\App::datadir());
//            $file = new \Upload\File('file', $storage);
//            $file->upload("file");
//        }
        //$this->db->query('COMMIT');
        return new \Symfony\Component\HttpFoundation\RedirectResponse(App::baseurl() . '/' . $item->getId());
    }

}
