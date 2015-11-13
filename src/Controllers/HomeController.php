<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController {

    /** @var \App\DB */
    protected $db;

    public function __construct() {
        $this->db = new \App\DB();
    }

    public function index(Request $request, Response $response, array $args) {
        $items = $this->db->query("SELECT * FROM items WHERE auth_level = 0 ORDER BY RAND() LIMIT 10");
        $template = new \App\Template('home.twig');
        $template->items = $items;
        $template->title = 'Home';
        $response->setContent($template->render());
        return $response;
    }

    public function view(Request $request, Response $response, array $args) {
        $item = new \App\Item($this->db, $args['id']);
        $template = new \App\Template('view.twig');
        $template->item = $item;
        $template->title = $item->get('title');
        $template->keywords = $item->getKeywords();
        $response->setContent($template->render());
        return $response;
    }

    public function edit(Request $request, Response $response, array $args) {
        $template = new \App\Template('form.twig');
        $template->date_granularities = $this->db->query("SELECT id, title FROM date_granularities ORDER BY id ASC");

        if (isset($args['id'])) {
            $template->title = 'Editing #' . $args['id'];
            $params = ['id' => $args['id']];
            $item = $this->db->query('SELECT * FROM items WHERE id=:id', $params)->fetch();
        } else {
            $template->title = 'Create';
            $item = array();
        }
        $template->item = $item;

        $response->setContent($template->render());
        return $response;
    }

    public function save(Request $request, Response $response, array $args) {
        $item = new \App\Item();

        $_POST = array_filter($_POST, 'trim');
        $params = array(
            'title' => filter_input(INPUT_POST, 'title', FILTER_SANITIZE_STRING),
            'description' => filter_input(INPUT_POST, 'description', FILTER_SANITIZE_STRING),
            'date' => filter_input(INPUT_POST, 'date', FILTER_SANITIZE_STRING),
            'date_granularity' => filter_input(INPUT_POST, 'date_granularity', FILTER_SANITIZE_NUMBER_INT),
            'auth_level' => filter_input(INPUT_POST, 'auth_level', FILTER_SANITIZE_NUMBER_INT),
        );
        if (empty($params['auth_level'])) {
            $params['auth_level'] = 0;
        }
        $setClause = 'SET title=:title, description=:description, date=:date, '
                . ' date_granularity=:date_granularity, auth_level=:auth_level ';
        $this->db->query('BEGIN');
        if (isset($args['id'])) {
            $sql = "UPDATE items $setClause WHERE id=:id";
            $params['id'] = $args['id'];
            $this->db->query($sql, $params);
            $id = $args['id'];
        } else {
            $sql = "INSERT INTO items $setClause";
            $this->db->query($sql, $params);
            $id = $this->db->lastInsertId();
        }

        // Save the file.
        if ($_FILE['file']) {
            $storage = new \Upload\Storage\FileSystem(\App\App::datadir());
            $file = new \Upload\File('file', $storage);
            $file->upload("file");
        }

        $this->db->query('COMMIT');
        return new \Symfony\Component\HttpFoundation\RedirectResponse(\App\App::baseurl() . '/' . $id);
    }

}
