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

    public static function mode() {
        return self::env('MODE', 'production');
    }

    public static function env($name, $default) {
        $env = getenv($name);
        return ($env) ? $env : $default;
    }

}
