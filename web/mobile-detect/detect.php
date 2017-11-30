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

use Detection\MobileDetect;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;

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

$logger = new Logger('ua-comparator');

$stream = new StreamHandler('log/error-yzalis.log', Logger::ERROR);
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

$start    = microtime(true);
$parser   = new MobileDetect();
$parser->setUserAgent($_GET['useragent']);

if ($parser->isTablet()) {
    $type = 'Tablet';
} elseif ($parser->isMobile()) {
    $type = 'Mobile Phone';
} else {
    $type = null;
}

$duration = microtime(true) - $start;
$memory   = memory_get_usage(true);

$resultArray = [
    'device' => [
        'type' => $type,
    ],
];

header('Content-Type: application/json', true);

echo htmlentities(json_encode(
    [
        'result'   => $resultArray,
        'duration' => $duration,
        'memory'   => $memory,
    ]
));
