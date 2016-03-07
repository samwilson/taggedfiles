<?php

namespace App\Commands;

abstract class CommandBase
{

    /** @var string[] CLI arguments. */
    protected $args;

    public function __construct($args = [])
    {
        $this->args = $args;
    }

    /**
     * Write a line to the terminal.
     * @param string $message
     * @param boolean $newline Whether to include a newline at the end.
     * @return void
     */
    protected function write($message, $newline = true)
    {
        if (basename($_SERVER['SCRIPT_NAME']) !== 'swidau') {
            // Only produce output when running the CLI tool.
            return;
        }
        echo $message . ($newline ? PHP_EOL : '');
    }
}
