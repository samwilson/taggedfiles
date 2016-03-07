<?php

namespace App\Commands;

use App\Config;
use App\Db;

class UpgradeCommand extends CommandBase
{

    public function run()
    {
        $config = new Config();
        $this->write("Upgrading ".$config->siteTitle()." . . . ");
        $db = new Db();
        $db->install();
        $this->write("Upgrade complete");
    }
}
