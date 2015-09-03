<?php

namespace App\Controllers;

class InstallController {

    public function install() {
        $response = new \Symfony\Component\HttpFoundation\Response();
        $template = new \App\Template('install.twig');
        return \Symfony\Component\HttpFoundation\Response::create($template->render());
    }

    public function run() {
        $db = new \App\DB();
        $sql = "CREATE TABLE IF NOT EXISTS items ("
                . " id INT(10) AUTO_INCREMENT PRIMARY KEY,"
                . " title VARCHAR(100) NOT NULL UNIQUE,"
                . " description TEXT NULL DEFAULT NULL"
                . ");";
        $db->query($sql);
    }

}
