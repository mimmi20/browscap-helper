<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2018, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper;

use BrowscapHelper\Command\Helper\BrowscapTestWriter;
use BrowscapHelper\Command\Helper\DetectorTestWriter;
use BrowscapHelper\Command\Helper\RegexFactory;
use BrowscapHelper\Command\Helper\TxtTestWriter;
use BrowscapHelper\Command\Helper\Useragent;
use BrowserDetector\Detector;
use Monolog\ErrorHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\Console\Application;

/**
 * Class BrowscapHelper
 *
 * @category   Browscap Helper
 */
class BrowscapHelper extends Application
{
    /**
     * @var string
     */
    public const DEFAULT_RESOURCES_FOLDER = '../sources';

    /**
     * BrowscapHelper constructor.
     *
     * @throws \Exception
     * @throws \Psr\SimpleCache\InvalidArgumentException
     */
    public function __construct()
    {
        parent::__construct('Browscap Helper Project', 'dev-master');

        $sourcesDirectory = realpath(__DIR__ . '/../sources/');
        $targetDirectory  = realpath(__DIR__ . '/../results/');

        $formatter = new LineFormatter(null, null, true, true);
        $stream    = new StreamHandler('log/error.log', Logger::NOTICE);
        $stream->setFormatter($formatter);

        $logger = new Logger('browser-detector-helper');
        $logger->pushHandler($stream);

        ErrorHandler::register($logger);

        $cache    = new FilesystemCache('', 0, __DIR__ . '/../cache/');
        $detector = new Detector($cache, $logger);
        $detector->warmupCache();

        $commands = [
            new Command\ConvertLogsCommand($logger, $sourcesDirectory, $targetDirectory),
            new Command\CopyTestsCommand($logger, $sourcesDirectory, $targetDirectory),
            new Command\CreateTestsCommand($logger, $sourcesDirectory, $targetDirectory),
            new Command\RewriteTestsCommand($logger, $cache, $detector),
        ];

        foreach ($commands as $command) {
            $this->add($command);
        }

        $targetDirectoryHelper = new Command\Helper\TargetDirectory();
        $this->getHelperSet()->set($targetDirectoryHelper);

        $browscapTestWriter = new BrowscapTestWriter($logger, $targetDirectory);
        $this->getHelperSet()->set($browscapTestWriter);

        $txtTestWriter = new TxtTestWriter();
        $this->getHelperSet()->set($txtTestWriter);

        $detectorTestWriter = new DetectorTestWriter($logger);
        $this->getHelperSet()->set($detectorTestWriter);

        $regexFactory = new RegexFactory($cache, $logger);
        $this->getHelperSet()->set($regexFactory);

        $uaHelper = new Useragent($logger);
        $this->getHelperSet()->set($uaHelper);
    }
}
