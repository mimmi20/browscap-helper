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

use BrowscapHelper\Helper\Device;
use BrowscapHelper\Helper\Engine;
use BrowscapHelper\Helper\Platform;
use BrowscapHelper\Reader\LogFileReader;
use BrowscapHelper\Reader\YamlFileReader;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Monolog\Logger;
use Monolog\Handler;
use BrowserDetector\BrowserDetector;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

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
     * @param string $targetDirectory
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

        $output->writeln('reading files from browscap ...');

        $browscapIssueDirectory = 'vendor/browscap/browscap/tests/fixtures/issues/';
        $browscapIssueIterator  = new \RecursiveDirectoryIterator($browscapIssueDirectory);
        $checks                 = [];

        $logger = new Logger('browser-detector-helper');
        $logger->pushHandler(new Handler\NullHandler());
        $logger->pushHandler(new Handler\StreamHandler('error.log', Logger::ERROR));

        $adapter  = new Local(__DIR__ . '/../../cache/');
        $cache    = new FilesystemCachePool(new Filesystem($adapter));
        //$detector = new BrowserDetector($cache, $logger);

        foreach (new \RecursiveIteratorIterator($browscapIssueIterator) as $file) {
            /** @var $file \SplFileInfo */
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $tests = require_once $file->getPathname();

            foreach ($tests as $key => $test) {
                if (isset($data[$key])) {
                    continue;
                }

                if (isset($checks[$test['ua']])) {
                    continue;
                }

                $data[$key]          = $test;
                $checks[$test['ua']] = $key;
            }
        }

        $output->writeln('reading new files ...');

        $sourcesDirectory = $input->getOption('resources');
        $sourcesIterator  = new \RecursiveDirectoryIterator($sourcesDirectory);
        $counter          = 0;

        foreach (new \RecursiveIteratorIterator($sourcesIterator) as $file) {
            /** @var $file \SplFileInfo */
            if (!$file->isFile()) {
                continue;
            }

            $output->writeln('file ' . $file->getBasename());
            $output->writeln('    checking ...');

            $fileContents = [];

            switch ($file->getExtension()) {
                case 'txt':
                    $reader = new LogFileReader();
                    $reader->setLocalFile($file->getPathname());

                    $fileContents = $reader->getAgents();
                    break;
                case 'yaml':
                    $reader = new YamlFileReader();
                    $reader->setLocalFile($file->getPathname());

                    $fileContents = $reader->getAgents();
                    break;
                default:
                    continue;
            }

            if (empty($fileContents)) {
                continue;
            }

            $output->writeln('    parsing ...');

            $counter += $this->parseFile($output, $cache, $fileContents, $file->getBasename(), $checks);
        }

        $output->writeln('');
        $output->writeln('Es wurden ' . $counter . ' Tests exportiert');
    }

    /**
     * @param array  $fileContents
     * @param string $issue
     * @param array  &$checks
     *
     * @return int
     */
    private function parseFile(OutputInterface $output, \Psr\Cache\CacheItemPoolInterface $cache, array $fileContents = [], $issue = '', array &$checks = [])
    {
        $outputBrowscap = "<?php\n\nreturn [\n";
        $outputDetector = [];
        $counter        = 0;

        foreach ($fileContents as $i => $ua) {
            $ua = trim($ua);

            if (isset($checks[$ua])) {
                continue;
            }

            $output->writeln('    handle useragent ' . $i . ' ...');
            $this->parseLine($cache, $ua, $i, $checks, $counter, $outputBrowscap, $outputDetector, $issue);
        }

        $outputBrowscap .= "];\n";

        file_put_contents('results/issue-' . $issue . '.php', $outputBrowscap);
        file_put_contents('results/browscap-issue-' . $issue . '.json', json_encode($outputDetector, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        return $counter;
    }

    /**
     * @param string $ua
     * @param int    $i
     * @param array  &$checks
     * @param int    &$counter
     * @param string &$outputBrowscap
     * @param array  &$outputDetector
     * @param string $issue
     */
    private function parseLine(\Psr\Cache\CacheItemPoolInterface $cache, $ua, $i, array &$checks, &$counter, &$outputBrowscap, array &$outputDetector, $issue)
    {
        $engineVersion = 'unknown';

        list(
            $browserNameBrowscap,
            $browserType,
            $browserBits,
            $browserMaker,
            $browserModus,
            $browserVersion,
            $browserNameDetector,
            $lite,
            $crawler) = (new \BrowscapHelper\Helper\Browser())->detect($ua);

        list(
            $platformNameBrowscap,
            $platformMakerBrowscap,
            $platformDescriptionBrowscap,
            $platformVersionBrowscap,
            $win64,
            $win32,
            $win16,
            $platformCodenameDetector,
            $platformMarketingnameDetector,
            $platformMakerNameDetector,
            $platformMakerBrandnameDetector,
            $platformVersionDetector,
            $standard,
            $platformBits) = (new Platform())->detect($ua);
        
        list(
            $deviceName,
            $deviceMaker,
            $deviceType,
            $pointingMethod,
            $deviceCodename,
            $deviceBrandname,
            $mobileDevice,
            $isTablet,
            $deviceOrientation) = (new Device())->detect($cache, $ua);

        list(
            $engineName,
            $engineMaker,
            $applets,
            $activex) = (new Engine())->detect($ua);

        $v          = explode('.', $browserVersion, 2);
        $maxVersion = $v[0];
        $minVersion = (isset($v[1]) ? $v[1] : '0');

        $outputBrowscap .= "    'issue-$issue-$i' => [
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
            'isMobileDevice' => " . ($mobileDevice ? 'true' : 'false') . ",
            'isTablet' => " . ($isTablet ? 'true' : 'false') . ",
            'isSyndicationReader' => false,
            'Crawler' => " . ($crawler ? 'true' : 'false') . ",
            'isFake' => false,
            'isAnonymized' => false,
            'isModified' => false,
            'CssVersion' => '0',
            'AolVersion' => '0',
            'Device_Name' => '" . $deviceName . "',
            'Device_Maker' => '" . $deviceMaker . "',
            'Device_Type' => '" . $deviceType . "',
            'Device_Pointing_Method' => '" . $pointingMethod . "',
            'Device_Code_Name' => '" . $deviceCodename . "',
            'Device_Brand_Name' => '" . $deviceBrandname . "',
            'RenderingEngine_Name' => '$engineName',
            'RenderingEngine_Version' => '$engineVersion',
            'RenderingEngine_Maker' => '$engineMaker',
        ],
        'lite' => " . ($lite ? 'true' : 'false') . ",
        'standard' => " . ($standard ? 'true' : 'false') . ",
    ],\n";

        $formatedIssue   = sprintf('%1$05d', (int) $issue);
        $formatedCounter = sprintf('%1$05d', (int) $counter);

        $outputDetector['browscap-issue-' . $formatedIssue . '-' . $formatedCounter] = [
            'ua' => $ua,
            'properties' => [
                'Browser_Name'            => $browserNameDetector,
                'Browser_Type'            => $browserType,
                'Browser_Bits'            => $browserBits,
                'Browser_Maker'           => $browserMaker,
                'Browser_Modus'           => $browserModus,
                'Browser_Version'         => $browserVersion,
                'Platform_Codename'       => $platformCodenameDetector,
                'Platform_Marketingname'  => $platformMarketingnameDetector,
                'Platform_Version'        => $platformVersionDetector,
                'Platform_Bits'           => $platformBits,
                'Platform_Maker'          => $platformMakerNameDetector,
                'Platform_Brand_Name'     => $platformMakerBrandnameDetector,
                'Device_Name'             => $deviceName,
                'Device_Maker'            => $deviceMaker,
                'Device_Type'             => $deviceType,
                'Device_Pointing_Method'  => $pointingMethod,
                'Device_Dual_Orientation' => $deviceOrientation,
                'Device_Code_Name'        => $deviceCodename,
                'Device_Brand_Name'       => $deviceBrandname,
                'RenderingEngine_Name'    => $engineName,
                'RenderingEngine_Version' => $engineVersion,
                'RenderingEngine_Maker'   => $engineMaker,
            ],
        ];

        ++$counter;

        $checks[$ua] = $i;

        return;
    }
}
