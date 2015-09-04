<?php

namespace App\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class InstallController {

    public function install(Request $request, Response $response, array $args) {
        $response = new \Symfony\Component\HttpFoundation\Response();
        $template = new \App\Template('install.twig');
        $template->title = 'Install';
        return \Symfony\Component\HttpFoundation\Response::create($template->render());
    }

    public function run(Request $request, Response $response, array $args) {
        $db = new \App\DB();
        $sql = "CREATE TABLE IF NOT EXISTS date_granularities ("
                . " id INT(2) UNSIGNED AUTO_INCREMENT PRIMARY KEY,"
                . " title VARCHAR(20) NOT NULL UNIQUE,"
                . " php_format VARCHAR(50) NOT NULL UNIQUE"
                . ")";
        $db->query($sql);
        $db->query("INSERT IGNORE INTO date_granularities (id,title,php_format) VALUES"
                . " (1, 'Exact', 'Y-m-d H:i:s'),"
                . " (2, 'Day', 'j F Y'),"
                . " (3, 'Month', 'F Y'),"
                . " (4, 'Year', 'Y'),"
                . " (5, 'Circa', '\c. Y')");
        $sql = "CREATE TABLE IF NOT EXISTS items ("
                . " id INT(10) UNSIGNED AUTO_INCREMENT PRIMARY KEY,"
                . " title VARCHAR(100) NOT NULL UNIQUE,"
                . " date DATE NULL DEFAULT NULL,"
                . " date_granularity INT(2) UNSIGNED NOT NULL DEFAULT 1,"
                . " description TEXT NULL DEFAULT NULL,"
                . " auth_level INT(2) NOT NULL DEFAULT 0"
                . ");";
        $db->query($sql);
        return new \Symfony\Component\HttpFoundation\RedirectResponse(\App\App::baseurl());
    }

}
