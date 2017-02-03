<?php

namespace App;

use League\Flysystem\Filesystem;
use League\Flysystem\MountManager;

class App
{

    public static function name()
    {
        return 'Tagged Files';
    }

    /**
     * Get the application's version.
     *
     * Conforms to Semantic Versioning guidelines.
     * @link http://semver.org
     * @return string
     */
    public static function version()
    {
        return '0.1.0';
    }

    /**
     * Turn a spaced or underscored string to camelcase (with no spaces or underscores).
     *
     * @param string $str
     * @return string
     */
    public static function camelcase($str)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $str)));
    }

    /**
     * Get the filesystem manager.
     *
     * @return MountManager
     * @throws \Exception
     */
    public static function getFilesystem()
    {
        $config = new Config();
        $manager = new MountManager();
        foreach ($config->filesystems() as $name => $fsConfig) {
            $adapterName = '\\League\\Flysystem\\Adapter\\' . self::camelcase($fsConfig['type']);
            $adapter = new $adapterName($fsConfig['root']);
            $fs = new Filesystem($adapter);
            $manager->mountFilesystem($name, $fs);
        }
        return $manager;
    }

    public static function exceptionHandler(\Exception $exception)
    {
        $template = new Template('error.twig');
        $template->title = 'Error';
        $template->alert('danger', $exception->getMessage());
        $template->e = $exception;
        $template->render(true);
    }

    /**
     * Delete a directory and its contents.
     * @link http://stackoverflow.com/a/8688278/99667
     * @param $path
     * @return bool
     */
    public static function deleteDir($path)
    {
        if (empty($path)) {
            return false;
        }
        return is_file($path) ?
            @unlink($path) :
            array_map([__CLASS__, __FUNCTION__], glob($path . '/*')) == @rmdir($path);
    }
}
