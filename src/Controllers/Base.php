<?php

namespace App\Controllers;

use App\Config;
use App\User;

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

    /**
     * The current user.
     *
     * @var \App\User
     */
    protected $user;

   public function __construct() {
        $this->config = new Config();
        $this->db = new \App\Db();

        // User.
        if (isset($_SESSION['userid'])) {
            $this->user = new User($this->db);
            $this->user->load($_SESSION['userid']);
        }
    }

    /**
     * Send an email message.
     *
     * @param \Swift_Message $message
     */
    public function email(\Swift_Message $message) {
        $config = $this->config->mail();
        $transport_classname = '\\Swift_'.ucfirst($config['transport']).'Transport';
        $transport = $transport_classname::newInstance();
        $mailer = \Swift_Mailer::newInstance($transport);
        $mailer->send($message);
    }
}
