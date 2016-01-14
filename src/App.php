<?php

namespace App;

class App {

    public static function name() {
        return 'swidau';
    }

    /**
     * Get the application's version.
     *
     * Conforms to Semantic Versioning guidelines.
     * @link http://semver.org
     * @return string
     */
    public static function version() {
        return '0.1.0';
    }

    /**
     * Get the site's base URL. Never has a trailing slash.
     * @return string
     */
    public static function baseurl() {
        $baseurl = self::env('BASEURL', '/swidau');
        return rtrim($baseurl, '/');
    }

    /**
     * Get the site's data directory. Never has a trailing slash.
     * @return string
     */
    public static function datadir() {
        $datadir = self::env('DATADIR', __DIR__.'/../data');
        return rtrim($datadir, '/');
    }

    public static function mode() {
        return self::env('MODE', 'production');
    }

    public static function env($name, $default) {
        $env = getenv($name);
        return ($env) ? $env : $default;
    }

    public static function exceptionHandler(\Exception $exception) {
        $template = new \App\Template('error.twig');
        $template->title = 'Error';
        $template->message('danger', $exception->getMessage());
        $template->e = $exception;
        $template->render(true);
    }

}
