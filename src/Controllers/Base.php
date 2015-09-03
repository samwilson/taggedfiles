<?php

namespace App\Controllers;

abstract class Base {

    public function __construct($baseDir) {
        $this->baseDir = $baseDir;
    }

    public function getBaseDir() {
        
    }

}
