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

use DeviceDetector\DeviceDetector;
use DeviceDetector\Parser\Client\Browser;
use DeviceDetector\Parser\Device\DeviceParserAbstract;
use DeviceDetector\Parser\OperatingSystem;
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

$stream = new StreamHandler('log/error-piwik.log', Logger::ERROR);
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

DeviceParserAbstract::setVersionTruncation(DeviceParserAbstract::VERSION_TRUNCATION_NONE);

header('Content-Type: application/json', true);

$start          = microtime(true);
$deviceDetector = new DeviceDetector($_GET['useragent']);
$deviceDetector->parse();

$os       = $deviceDetector->getOs();
$osFamily = OperatingSystem::getOsFamily($deviceDetector->getOs('short_name'));

$client        = $deviceDetector->getClient();
$browserFamily = Browser::getBrowserFamily($deviceDetector->getClient('short_name'));

$processed = [
    'user_agent' => $deviceDetector->getUserAgent(),
    'bot'        => ($deviceDetector->isBot() ? $deviceDetector->getBot() : false),
    'os'         => [
        'name'    => (isset($os['name']) ? $os['name'] : ''),
        'version' => (isset($os['version']) ? $os['version'] : null),
    ],
    'client' => [
        'name'    => (isset($client['name']) ? $client['name'] : ''),
        'version' => (isset($client['version']) ? $client['version'] : null),
        'engine'  => (isset($client['engine']) ? $client['engine'] : null),
    ],
    'device' => [
        'type'  => $deviceDetector->getDeviceName(),
        'brand' => $deviceDetector->getBrand(),
        'model' => $deviceDetector->getModel(),
    ],
    'os_family'      => $osFamily !== false ? $osFamily : 'Unknown',
    'browser_family' => $browserFamily !== false ? $browserFamily : 'Unknown',
];
$duration = microtime(true) - $start;

echo htmlentities(json_encode(
    [
        'result'   => $processed,
        'duration' => $duration,
        'memory'   => memory_get_usage(true),
    ]
));
