<?php

namespace App;

class Template {

    /** @var array */
    private $data = array();

    /** @var string */
    private $template = false;

    const INFO = 'info';
    const WARNING = 'warning';
    const ERROR = 'error';

    public function __construct($template) {
        $this->template = $template;
        $config = new Config();
        $this->data['app_title'] = App::name() . ' ' . App::version();
        $this->data['app_version'] = App::version();
        $this->data['mode'] = $config->mode();
        $this->data['baseurl'] = $config->baseUrl();
        $this->data['baseurl_full'] = $config->baseUrl(true);
        $this->data['site_title'] = $config->siteTitle();

        $this->data['alerts'] = (isset($_SESSION['alerts'])) ? $_SESSION['alerts'] : array();
        $_SESSION['alerts'] = array();

        if (isset($_SESSION['userid'])) {
            $user = new User();
            $user->load($_SESSION['userid']);
            $this->data['user'] = $user;
        }
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    public function render($echo = false) {
        $this->queries = Db::getQueries();

        // Load template directories.
        $loader = new \Twig_Loader_Filesystem();
        $loader->addPath('templates');

        // Set up Twig.
        $twig = new \Twig_Environment($loader, array(
            'debug' => true,
            'strct_variables' => true
        ));
        $twig->addExtension(new \Twig_Extension_Debug());

        // Mardown support.
        $twig->addFilter(new \Twig_SimpleFilter('markdown',function ($text) {
            $parsedown = new \Parsedown();
            return $parsedown->text($text);
        }));

        // Render.
        $string = $twig->render($this->template, $this->data);
        if ($echo) {
            echo $string;
        } else {
            return $string;
        }
    }

    /**
     * Display a message to the user.
     * @link http://getbootstrap.com/components/#alerts
     * @param string $type One of: 'success', 'info', 'warning', or 'danger'.
     * @param string $message The text of the message.
     * @param boolean $delayed Whether to delay the message until the next request.
     */
    public function alert($type, $message, $delayed = false) {
        $alert = array(
            'type' => $type,
            'message' => $message,
        );
        if ($delayed) {
            $_SESSION['alerts'][] = $alert;
        } else {
            $this->data['alerts'][] = $alert;
        }
    }
}
