<?php

namespace App\Tests;

use App\App;
use App\Db;
use PHPUnit\Framework\TestCase;

abstract class Base extends TestCase
{

    /** @var \App\Db */
    protected $db;

    /**
     * Set up the database by dropping all of the tables (if any exist) and then installing the
     * application. The test-data directory will be created too, and then removed in tearDown().
     */
    public function setUpDb()
    {
        $this->db = new Db();
        $this->db->query("SET FOREIGN_KEY_CHECKS=0");
        $this->db->query("DROP TABLE IF EXISTS `tags`");
        $this->db->query("DROP TABLE IF EXISTS `items`");
        $this->db->query("DROP TABLE IF EXISTS `users`");
        $this->db->query("DROP TABLE IF EXISTS `groups`");
        $this->db->query("DROP TABLE IF EXISTS `user_groups`");
        $this->db->query("DROP TABLE IF EXISTS `date_granularities`");
        $this->db->query("SET FOREIGN_KEY_CHECKS=1");
        $this->db->install();
        if (!is_dir($this->dataDir())) {
            mkdir($this->dataDir());
        }
    }

    public function tearDown()
    {
        App::deleteDir($this->dataDir());
    }

    /**
     * Get the full local filesystem path to the test-data directory.
     * This directory is created at set-up and deleted at tear-down.
     *
     * @return string
     */
    protected function dataDir()
    {
        return __DIR__.'/data';
    }
}
