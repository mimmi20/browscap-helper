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

use Browscap\Generator\BuildGenerator;
use Browscap\Helper\CollectionCreator;
use Browscap\Writer\Factory\FullPhpWriterFactory;
use BrowscapPHP\Helper\LoggerHelper;
use Noodlehaus\Config;

$bench = new Ubench();
$bench->start();

echo ' creating browscap.ini', PHP_EOL;

$buildFolder = 'data/browser/';

$config   = new Config(['data/configs/config.json']);
$cacheDir = $config['modules']['browscap3']['cache-dir'];

$loggerHelper = new LoggerHelper();
$logger       = $loggerHelper->create(false);

$buildGenerator = new BuildGenerator(
    'vendor/browscap/browscap/resources/',
    $buildFolder
);

$writerCollectionFactory = new FullPhpWriterFactory();
$writerCollection        = $writerCollectionFactory->createCollection($logger, $buildFolder);

$buildGenerator
    ->setLogger($logger)
    ->setCollectionCreator(new CollectionCreator())
    ->setWriterCollection($writerCollection);

$version = (string) file_get_contents('vendor/browscap/browscap/BUILD_NUMBER');

$buildGenerator->run($version, false);

$bench->end();
echo ' ', $bench->getTime(true), ' seconds ', PHP_EOL;
echo ' ', number_format($bench->getMemoryPeak(true)), ' bytes', PHP_EOL;
