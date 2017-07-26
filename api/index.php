<?php

ini_set('display_errors', 0);

require_once __DIR__.'/../vendor/autoload.php';

//if($_SERVER['HTTP_HOST'] != "dtodoaqui.com") {
    $app = require __DIR__ . '/../src/app.php';
//}else{
//    $app = require 'phar://'. __DIR__ . '/../src.phar/app.php';
//}
$app["app.files_path"] = __DIR__."/../files";
require __DIR__.'/../config/prod.php';


//if($_SERVER['HTTP_HOST'] != "dtodoaqui.com") {
    require __DIR__ . '/../src/controllers.php';
//}else{
//    require 'phar://' . __DIR__ . '/../src.phar/controllers.php';
//}
$app->run();
