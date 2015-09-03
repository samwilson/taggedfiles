<?php

namespace App;

class Template {

    /** @var array */
    private $data = array();

    /** @var string */
    private $template = false;

    /** @var Modules */
    protected $modules;

    public function __construct($template) {
        $this->template = $template;
        $this->data['app_title'] = App::name() . ' ' . App::version();
        $this->data['app_version'] = App::version();

        if (!isset($_SESSION)) {
            session_start();
        }
        $this->data['alerts'] = (isset($_SESSION['alerts'])) ? $_SESSION['alerts'] : array();
        $_SESSION['alerts'] = array();
    }

    public function __set($name, $value) {
        $this->data[$name] = $value;
    }

    public function render($echo = false) {
        $this->queries = DB::getQueries();

        // Load template directories.
        $loader = new \Twig_Loader_Filesystem();
        $loader->addPath('templates');

        // Set up Twig.
        $twig = new \Twig_Environment($loader, array(
            'debug' => true,
            'strct_variables' => true
        ));
        $twig->addExtension(new \Twig_Extension_Debug());

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
     *
     * @param string $type One of 'success', 'warning', 'info', or 'alert'.
     * @param string $message The text of the message.
     * @param boolean $delayed Whether to delay the message until the next request.
     */
    public function message($type, $message, $delayed = false) {
        $msg = array(
            'type' => $type,
            'message' => $message,
        );
        if ($delayed) {
            $_SESSION['alerts'][] = $msg;
        } else {
            $this->data['alerts'][] = $msg;
        }
    }

}
