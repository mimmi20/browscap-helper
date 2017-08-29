#!/usr/bin/env php
<?php


/**
 * This makes our life easier when dealing with paths. Everything is relative
 * to the application root now.
 */
chdir(dirname(__DIR__));

$autoloadPaths = [
    'vendor/autoload.php',
    '../../autoload.php',
];

foreach ($autoloadPaths as $path) {
    if (file_exists($path)) {
        require_once $path;
        break;
    }
}

ini_set('memory_limit', '-1');

use BrowscapPHP\BrowscapUpdater;
use Noodlehaus\Config;
use WurflCache\Adapter\File;

$bench = new Ubench();
$bench->start();

echo ' updating cache for BrowscapPHP\Browscap', PHP_EOL;

$config = new Config(['data/configs/config.json']);

if (!$config['modules']['browscap3']['enabled']) {
    exit;
}

$cacheDir = $config['modules']['browscap3']['cache-dir'];
$browscap = new BrowscapUpdater();
$cache    = new File([File::DIR => $cacheDir]);
$browscap->setCache($cache);
$browscap->convertFile(realpath('data/browser/full_php_browscap.ini'));

$bench->end();
echo ' ', $bench->getTime(true), ' seconds', PHP_EOL;
echo ' ', number_format($bench->getMemoryPeak(true)), ' bytes', PHP_EOL;
