<?php

namespace App;

class Config {

    protected static $config = false;

    public function __construct()
    {
        if (!is_file(CONFIG_FILE)) {
            throw new \Exception("Config file not found: '".CONFIG_FILE."'");
        }
        if (self::$config === false) {
            self::$config = require CONFIG_FILE;
        }
    }

    protected function get($name, $default = null) {
        if (isset(self::$config[$name])) {
            return self::$config[$name];
        }
        return $default;
    }

    public function debug() {
        return (bool) $this->get('debug', false);
    }

    public function mode()
    {
        return $this->get('mode', 'production');
    }

    public function filesystems() {
        $default = [
            'cache' => [
                'type' => 'local',
                'root' => __DIR__ . '/../data/cache',
            ],
            'storage' => [
                'type' => 'local',
                'root' => __DIR__ . '/../data/storage',
            ],
        ];
        return $this->get('filesystems', $default);
    }

    /**
     * Get the Base URL of the application. Never has a trailing slash.
     * @return string
     */
    public function baseUrl() {
        $calculatedBaseUrl = substr($_SERVER['SCRIPT_NAME'], 0, -(strlen('index.php')));
        $baseUrl = $this->get('base_url', $calculatedBaseUrl);
        return rtrim($baseUrl, ' /');
    }

    public function siteTitle() {
        return $this->get('site_title', 'A Swidau Site');
    }

    public function databaseHost() {
        return self::get('databaseHost', 'localhost');
    }

    public function databaseName() {
        return self::get('databaseName', 'tabulate');
    }

    public function databaseUser() {
        return self::get('databaseUser', 'tabulate');
    }

    public function databasePassword() {
        return self::get('databasePassword', '');
    }

}
