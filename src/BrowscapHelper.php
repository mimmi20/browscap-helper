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
 * Class BrowscapHelper
 *
 * @category   Browscap Helper
 *
 * @author     Thomas Mueller <mimmi20@live.de>
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
        $logger->pushHandler(new StreamHandler('log/error.log', Logger::NOTICE));
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
