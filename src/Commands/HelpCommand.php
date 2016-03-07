<?php

namespace App\Commands;

class HelpCommand extends CommandBase
{

    public function run()
    {
        $iterator = new \RecursiveDirectoryIterator(__DIR__, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);
        $commands = [];
        foreach ($files as $file) {
            $suffix = 'Command.php';
            if (substr($file->getBasename(), -strlen($suffix)) === $suffix) {
                $commands[] = strtolower(substr($file->getBasename(), 0, -strlen($suffix)));
            }
        }
        $this->write("The following commands are available:");
        foreach ($commands as $cmd) {
            $this->write("   $cmd");
        }
    }
}
