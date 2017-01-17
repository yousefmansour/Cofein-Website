<?php
error_reporting();
include_once 'config/config.php';
require_once PATH_TO_SRC.'vendor/autoload.php';

mb_internal_encoding("UTF-8");
spl_autoload_register('autoload');

function autoload($class) {
    include 'classes/' . ucfirst($class) . '.php';
}

$app = new FrontController();




