<?php

namespace App\Controllers;

use App\Config;
use App\User;
use Psr\Http\Message\ServerRequestInterface;
use Swift_Mailer;
use Swift_Message;

abstract class Base
{

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

    /**
     * Every controller gets the configuration, database, and a user. If the user has a session
     * in progress, the user is loaded from that.
     */
    public function __construct()
    {
        $this->config = new Config();
        $this->db = new \App\Db();

        // User.
        $this->user = new User($this->db);
        if (isset($_SESSION['userid'])) {
            $this->user->load($_SESSION['userid']);
        }
    }

    /**
     * Get a parameter value out of a request's body.
     * @param ServerRequestInterface $request
     * @param string $param The name of the parameter to get.
     * @param mixed $default
     * @return mixed
     */
    protected function getBodyParam(ServerRequestInterface $request, $param, $default = null)
    {
        $params = $request->getParsedBody();
        return (isset($params[$param])) ? $params[$param] : $default;
    }

    /**
     * Get a parameter from the query string of this request.
     * @param ServerRequestInterface $request
     * @param string $param The name of the parameter to get.
     * @param mixed $default
     * @return mixed
     */
    protected function getQueryParam(ServerRequestInterface $request, $param, $default = null)
    {
        $params = $request->getQueryParams();
        return (isset($params[$param])) ? $params[$param] : $default;
    }

    /**
     * Get either a body or a query parameter.
     * @param ServerRequestInterface $request
     * @param string $param The name of the parameter to get.
     * @param mixed $default
     * @return mixed
     */
    public function getAnyParam(ServerRequestInterface $request, $param, $default = null)
    {
        $bodyParam = $this->getBodyParam($request, $param, $default);
        if ($bodyParam !== null) {
            return $bodyParam;
        }
        return $this->getQueryParam($request, $param, $default);
    }

    /**
     * Send an email message.
     *
     * @param Swift_Message $message
     */
    public function email(Swift_Message $message)
    {
        $config = $this->config->mail();
        $transport_classname = '\\Swift_' . ucfirst($config['transport']) . 'Transport';
        $transport = $transport_classname::newInstance();
        $mailer = Swift_Mailer::newInstance($transport);
        $mailer->send($message);
    }
}
