<?php

namespace App\Controllers;

class AssetsController extends \App\Controllers\Base {

    public function css($file) {
        echo file_get_contents($file);
        exit();
    }

}
