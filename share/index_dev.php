<?php

use Symfony\Component\Debug\Debug;
require_once __DIR__.'/../vendor/autoload.php';

Debug::enable();

$app = require __DIR__.'/../src/app.php';
$app = require 'phar://'. __DIR__ . '/src.phar/app.php';

require __DIR__.'/../config/dev.php';
require 'phar://'. __DIR__ . '/src.phar/share.php';
$app->run();
