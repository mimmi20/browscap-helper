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

use Crossjoin\Browscap\Browscap;
use Crossjoin\Browscap\Cache\File;
use Crossjoin\Browscap\Updater\Local;
use Noodlehaus\Config;

$bench = new Ubench();
$bench->start();

echo ' updating cache for Crossjoin\Browscap\Browscap (1.x)', PHP_EOL;

$config = new Config(['data/configs/config.json']);

if (!$config['modules']['crossjoin']['enabled']) {
    exit;
}

$cacheDir = $config['modules']['crossjoin']['cache-dir'];

File::setCacheDirectory($cacheDir);
Browscap::setDataSetType(Browscap::DATASET_TYPE_LARGE);

$updater = new Local();
$updater->setOption('LocalFile', realpath('data/browser/full_php_browscap.ini'));

Browscap::setUpdater($updater);

$browscap = new Browscap();
$browscap->getBrowser()->getData();

$bench->end();
echo ' ', $bench->getTime(true), ' seconds', PHP_EOL;
echo ' ', number_format($bench->getMemoryPeak(true)), ' bytes', PHP_EOL;
