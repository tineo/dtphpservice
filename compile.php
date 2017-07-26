<?php
/**
 * Created by PhpStorm.
 * User: tineo
 * Date: 03/01/16
 * Time: 08:41 PM
 */
$phar = new Phar('src.phar', 0, 'src.phar');
$phar->buildFromDirectory(__DIR__ . '/src');
$phar->compressFiles(Phar::GZ);
$phar->setDefaultStub();