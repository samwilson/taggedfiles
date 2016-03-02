<?php

namespace App\Controllers;

use App\Config;

abstract class Base {

    /**
     * The site configuration.
     *
     * @var \App\Config
     */
    protected $config;

    /**
     * The database.
     *
     * @var \App\Db
     */
    protected $db;

    public function __construct() {
        $this->config = new Config();
        $this->db = new \App\Db();
    }
}
