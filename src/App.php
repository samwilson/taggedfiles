<?php

namespace App;

class App {

    public static function name() {
        return 'Archorgau';
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
        $datadirEnv = self::env('DATADIR', __DIR__ . '/../data');
        $datadir = rtrim($datadirEnv, '/');
        if (!is_dir($datadir)) {
            throw new \Exception("Data directory is not a directory: $datadir");
        }
        return $datadir;
    }

    /**
     * Turn a spaced or underscored string to camelcase (with no spaces or underscores).
     *
     * @param string $str
     * @return string
     */
    public static function camelcase($str) {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
    }

    /**
     * Get the filesystem manager.
     *
     * @return \League\Flysystem\MountManager
     * @throws \Exception
     */
    public static function getFilesystem() {
        if (!is_file(getenv('CONFIG_FILE'))) {
            throw new \Exception("Config file not found: " . env('CONFIG_FILE'));
        }
        $config = require_once getenv('CONFIG_FILE');
        $manager = new \League\Flysystem\MountManager();
        foreach ($config['filesystems'] as $name => $fsConfig) {
            $adapterName = '\\League\\Flysystem\\Adapter\\' . self::camelcase($fsConfig['type']);
            $adapter = new $adapterName($fsConfig['root']);
            $fs = new \League\Flysystem\Filesystem($adapter);
            $manager->mountFilesystem($name, $fs);
        }
        return $manager;
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
