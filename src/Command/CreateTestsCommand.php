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

namespace BrowscapHelper\Command;

use BrowscapHelper\Helper\Browser;
use BrowscapHelper\Helper\Device;
use BrowscapHelper\Helper\Engine;
use BrowscapHelper\Helper\Platform;
use BrowscapHelper\Helper\TargetDirectory;
use BrowscapHelper\Source\DetectorSource;
use BrowscapHelper\Source\DirectorySource;
use BrowserDetector\BrowserDetector;
use BrowserDetector\Version\VersionFactory;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\UnreadableFileException;
use Monolog\Handler;
use Monolog\Logger;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UaBrowserType\TypeLoader;
use UaResult\Company\CompanyLoader;
use UaResult\Result\Result;
use Wurfl\Request\GenericRequestFactory;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class CreateTestsCommand extends Command
{
    /**
     * @var string
     */
    private $sourcesDirectory = '';

    /**
     * @param string $sourcesDirectory
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct($sourcesDirectory)
    {
        $this->sourcesDirectory = $sourcesDirectory;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('create-tests')
            ->setDescription('Creates tests from the apache log files')
            ->addOption(
                'resources',
                null,
                InputOption::VALUE_REQUIRED,
                'Where the resource files are located',
                $this->sourcesDirectory
            );
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @throws \LogicException When this abstract method is not implemented
     * @return null|int        null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /*******************************************************************************
         * loading files
         ******************************************************************************/

        $output->writeln('init logger ...');
        $logger = new Logger('browser-detector-helper');
        $logger->pushHandler(new Handler\NullHandler());
        $logger->pushHandler(new Handler\StreamHandler('error.log', Logger::ERROR));

        $output->writeln('init cache ...');
        $adapter  = new Local(__DIR__ . '/../../cache/');
        $cache    = new FilesystemCachePool(new Filesystem($adapter));

        $output->writeln('init detector ...');
        $detector = new BrowserDetector($cache, $logger);

        $output->writeln('reading already existing tests ...');
        $checks = [];

        foreach ((new DetectorSource())->getUserAgents($logger, $output) as $useragent) {
            if (isset($checks[$useragent])) {
                continue;
            }

            $checks[$useragent] = $useragent;
        }

        try {
            $number = (new TargetDirectory())->getNextTest($output);
        } catch (UnreadableFileException $e) {
            $logger->critical($e);
            $output->writeln($e->getMessage());

            return;
        }

        $output->writeln('reading new files ...');

        $sourcesDirectory = $input->getOption('resources');
        $outputBrowscap   = "<?php\n\nreturn [\n";
        $outputDetector   = [];
        $counter          = 0;
        $issue            = 'test-' . sprintf('%1$08d', $number);

        foreach ((new DirectorySource($sourcesDirectory))->getUserAgents($logger, $output) as $useragent) {
            $useragent = trim($useragent);

            if (isset($checks[$useragent])) {
                continue;
            }

            $this->parseLine($cache, $useragent, $counter, $outputBrowscap, $outputDetector, $number, $detector);
            $checks[$useragent] = $issue;
            ++$counter;
        }

        $outputBrowscap .= "];\n";

        file_put_contents('results/issue-' . sprintf('%1$05d', $number) . '.php', $outputBrowscap);

        $chunks          = array_chunk($outputDetector, 100, true);
        $targetDirectory = 'vendor/mimmi20/browser-detector-tests/tests/issues/' . sprintf('%1$05d', $number) . '/';
        if (!file_exists($targetDirectory)) {
            mkdir($targetDirectory);
        }

        foreach ($chunks as $chunkId => $chunk) {
            if (!count($chunk)) {
                continue;
            }

            file_put_contents(
                $targetDirectory . 'test-' . sprintf('%1$05d', $number) . '-' . sprintf('%1$05d', (int) $chunkId) . '.json',
                json_encode(
                    $chunk,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
                ) . PHP_EOL
            );
        }

        $output->writeln('');
        $output->writeln($counter . ' tests exported');
    }

    /**
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param string                            $ua
     * @param int                               $counter
     * @param string                            &$outputBrowscap
     * @param array                             &$outputDetector
     * @param int                               $testNumber
     * @param \BrowserDetector\BrowserDetector  $detector
     */
    private function parseLine(CacheItemPoolInterface $cache, $ua, $counter, &$outputBrowscap, array &$outputDetector, $testNumber, BrowserDetector $detector)
    {
        $platformCodename = 'unknown';

        list(
            $platformNameBrowscap,
            $platformMakerBrowscap,
            $platformDescriptionBrowscap,
            $platformVersionBrowscap,
            $win64,
            $win32,
            $win16,
            $standard,
            $platformBits,
            $platform) = (new Platform())->detect($cache, $ua, $detector, $platformCodename);

        $deviceCode = 'unknown';

        /** @var \UaResult\Device\DeviceInterface $device */
        $device = (new Device())->detect($cache, $ua, $platform, $detector, $deviceCode);

        /** @var \UaResult\Engine\EngineInterface $engine */
        list(
            $engine,
            $applets,
            $activex) = (new Engine())->detect($cache, $ua);

        $browserNameDetector = 'unknown';

        list(
            $browserNameBrowscap,
            $browserType,
            $browserBits,
            $browserMaker,
            $browserModus,
            $browserVersion,
            $browserNameDetector,
            $lite,
            $crawler) = (new Browser())->detect($cache, $ua, $detector, $engine, $browserNameDetector);

        $v          = explode('.', $browserVersion, 2);
        $maxVersion = $v[0];
        $minVersion = (isset($v[1]) ? $v[1] : '0');

        $formatedIssue   = sprintf('%1$05d', (int) $testNumber);
        $formatedCounter = sprintf('%1$05d', (int) $counter);

        $outputBrowscap .= "    'issue-$formatedIssue-$formatedCounter' => [
        'ua' => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $ua) . "',
        'properties' => [
            'Comment' => 'Default Browser',
            'Browser' => '" . str_replace(['\\', "'"], ['\\\\', "\\'"], $browserNameBrowscap) . "',
            'Browser_Type' => '$browserType',
            'Browser_Bits' => '$browserBits',
            'Browser_Maker' => '$browserMaker',
            'Browser_Modus' => '$browserModus',
            'Version' => '$browserVersion',
            'MajorVer' => '$maxVersion',
            'MinorVer' => '$minVersion',
            'Platform' => '$platformNameBrowscap',
            'Platform_Version' => '$platformVersionBrowscap',
            'Platform_Description' => '$platformDescriptionBrowscap',
            'Platform_Bits' => '$platformBits',
            'Platform_Maker' => '$platformMakerBrowscap',
            'Alpha' => false,
            'Beta' => false,
            'Win16' => " . ($win16 ? 'true' : 'false') . ",
            'Win32' => " . ($win32 ? 'true' : 'false') . ",
            'Win64' => " . ($win64 ? 'true' : 'false') . ",
            'Frames' => true,
            'IFrames' => true,
            'Tables' => true,
            'Cookies' => true,
            'BackgroundSounds' => " . ($activex ? 'true' : 'false') . ",
            'JavaScript' => true,
            'VBScript' => " . ($activex ? 'true' : 'false') . ",
            'JavaApplets' => " . ($applets ? 'true' : 'false') . ",
            'ActiveXControls' => " . ($activex ? 'true' : 'false') . ",
            'isMobileDevice' => " . ($device->getType()->isMobile() ? 'true' : 'false') . ",
            'isTablet' => " . ($device->getType()->isTablet() ? 'true' : 'false') . ",
            'isSyndicationReader' => false,
            'Crawler' => " . ($crawler ? 'true' : 'false') . ",
            'isFake' => false,
            'isAnonymized' => false,
            'isModified' => false,
            'CssVersion' => '0',
            'AolVersion' => '0',
            'Device_Name' => '" . $device->getMarketingName() . "',
            'Device_Maker' => '" . $device->getManufacturer() . "',
            'Device_Type' => '" . $device->getType()->getName() . "',
            'Device_Pointing_Method' => '" . $device->getPointingMethod() . "',
            'Device_Code_Name' => '" . $device->getDeviceName() . "',
            'Device_Brand_Name' => '" . $device->getBrand() . "',
            'RenderingEngine_Name' => '" . $engine->getName() . "',
            'RenderingEngine_Version' => 'unknown',
            'RenderingEngine_Maker' => '" . $engine->getManufacturer() . "',
        ],
        'lite' => " . ($lite ? 'true' : 'false') . ",
        'standard' => " . ($standard ? 'true' : 'false') . ",
    ],\n";

        $formatedIssue   = sprintf('%1$08d', (int) $testNumber);
        $formatedCounter = sprintf('%1$08d', (int) $counter);

        $request = (new GenericRequestFactory())->createRequestForUserAgent($ua);

        $browser = new \UaResult\Browser\Browser(
            $browserNameDetector,
            (new CompanyLoader($cache))->load($browserMaker),
            (new VersionFactory())->set($browserVersion),
            $engine,
            (new TypeLoader($cache))->load($browserType),
            $browserBits,
            false,
            false,
            $browserModus
        );

        $result = new Result($request, $device, $platform, $browser, $engine);

        $outputDetector['test-' . $formatedIssue . '-' . $formatedCounter] = [
            'ua'     => $ua,
            'result' => $result->toArray(),
        ];

        return;
    }
}
