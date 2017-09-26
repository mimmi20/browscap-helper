<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

use BrowscapPHP\Browscap;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use WurflCache\Adapter\File;

chdir(dirname(dirname(__DIR__)));

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

header('Content-Type: application/json', true);

$buildNumber = (int) file_get_contents('vendor/browscap/browscap/BUILD_NUMBER');
$iniFile     = 'data/browscap-ua-test-' . $buildNumber . '/full_php_browscap.ini';

$logger = new Logger('ua-comparator');

$stream = new StreamHandler('log/error-browscap3.log', Logger::ERROR);
$stream->setFormatter(new LineFormatter('[%datetime%] %channel%.%level_name%: %message% %extra%' . "\n"));

/** @var callable $memoryProcessor */
$memoryProcessor = new MemoryUsageProcessor(true);
$logger->pushProcessor($memoryProcessor);

/** @var callable $peakMemoryProcessor */
$peakMemoryProcessor = new MemoryPeakUsageProcessor(true);
$logger->pushProcessor($peakMemoryProcessor);

$logger->pushHandler($stream);
$logger->pushHandler(new ErrorLogHandler(ErrorLogHandler::OPERATING_SYSTEM, Logger::ERROR));

ErrorHandler::register($logger);

$cache = new File([File::DIR => 'data/cache/browscap3/']);

$browscap = new Browscap();
$browscap
    ->setLogger($logger)
    ->setCache($cache);

$start    = microtime(true);
$result   = $browscap->getBrowser($_GET['useragent']);
$duration = microtime(true) - $start;

echo htmlentities(json_encode(
    [
        'result'   => $result,
        'duration' => $duration,
        'memory'   => memory_get_usage(true),
    ]
));
