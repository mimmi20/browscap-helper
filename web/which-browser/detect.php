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

use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\ErrorLogHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\MemoryPeakUsageProcessor;
use Monolog\Processor\MemoryUsageProcessor;
use WhichBrowser\Parser;

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

$logger = new Logger('ua-comparator');

$stream = new StreamHandler('log/error-which-browser.log', Logger::ERROR);
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

$start       = microtime(true);
$parser      = new Parser(['User-Agent' => $_GET['useragent']]);
$resultArray = [
    'browser' => [
        'using'   => (isset($parser->browser->using) ? $parser->browser->using : null),
        'family'  => null,
        'channel' => (isset($parser->browser->channel) ? $parser->browser->channel : null),
        'stock'   => $parser->browser->stock,
        'hidden'  => $parser->browser->hidden,
        'mode'    => $parser->browser->mode,
        'type'    => $parser->browser->type,
        'name'    => (isset($parser->browser->name) ? $parser->browser->name : null),
        'alias'   => (isset($parser->browser->alias) ? $parser->browser->alias : null),
        'version' => (isset($parser->browser->version) ? $parser->browser->version : null),
    ],
    'engine' => [
        'name'    => (isset($parser->engine->name) ? $parser->engine->name : null),
        'alias'   => (isset($parser->engine->alias) ? $parser->engine->alias : null),
        'version' => (isset($parser->engine->version) ? $parser->engine->version : null),
    ],
    'os' => [
        'family'  => (isset($parser->os->family) ? $parser->os->family : null),
        'name'    => (isset($parser->os->name) ? $parser->os->name : null),
        'alias'   => (isset($parser->os->alias) ? $parser->os->alias : null),
        'version' => (isset($parser->os->version) ? $parser->os->version : null),
    ],
    'device' => [
        'manufacturer' => (isset($parser->device->manufacturer) ? $parser->device->manufacturer : null),
        'model'        => (isset($parser->device->model) ? $parser->device->model : null),
        'series'       => (isset($parser->device->series) ? $parser->device->series : null),
        'carrier'      => (isset($parser->device->carrier) ? $parser->device->carrier : null),
        'identifier'   => (isset($parser->device->identifier) ? $parser->device->identifier : null),
        'flag'         => (isset($parser->device->flag) ? $parser->device->flag : null),
        'type'         => $parser->device->type,
        'subtype'      => $parser->device->subtype,
        'identified'   => $parser->device->identified,
        'generic'      => $parser->device->generic,
    ],
    'camouflage' => $parser->camouflage,
];
$duration = microtime(true) - $start;

echo htmlentities(json_encode(
    [
        'result'   => $resultArray,
        'duration' => $duration,
        'memory'   => memory_get_usage(true),
    ]
));
