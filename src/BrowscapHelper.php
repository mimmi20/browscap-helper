<?php
/**
 * Copyright (c) 1998-2014 Browser Capabilities Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   Browscap
 * @copyright  1998-2014 Browser Capabilities Project
 * @license    MIT
 */

namespace BrowscapHelper;

use BrowserDetector\Detector;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Console\Application;

/**
 * Class Browscap
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class BrowscapHelper extends Application
{
    /**
     * @var string
     */
    const DEFAULT_RESOURCES_FOLDER = '../sources';

    public function __construct()
    {
        parent::__construct('Browscap Helper Project', 'dev-master');

        $sourcesDirectory = realpath(__DIR__ . '/../sources/') . '/';
        $targetDirectory  = realpath(__DIR__ . '/../results/') . '/';

        $logger = new Logger('browser-detector-helper');
        $logger->pushHandler(new StreamHandler(realpath(__DIR__ . '/../log/') . '/error.log', Logger::ERROR));
        ErrorHandler::register($logger);

        $adapter  = new Local('cache/');
        $cache    = new FilesystemCachePool(new Filesystem($adapter));
        $cache->setLogger($logger);

        $detector = new Detector($cache, $logger);

        $commands = [
            new Command\ConvertLogsCommand($logger, $sourcesDirectory, $targetDirectory),
            new Command\CopyTestsCommand($logger, $cache),
            new Command\CreateTestsCommand($logger, $cache, $detector, $sourcesDirectory),
            new Command\RewriteTestsCommand($logger, $cache, $detector),
        ];

        foreach ($commands as $command) {
            $this->add($command);
        }
    }
}
