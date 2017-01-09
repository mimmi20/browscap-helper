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

use BrowscapHelper\Helper\TargetDirectory;
use BrowscapHelper\Source\BrowscapSource;
use BrowscapHelper\Source\DetectorSource;
use BrowscapHelper\Source\PiwikSource;
use BrowscapHelper\Source\UapCoreSource;
use BrowscapHelper\Source\WhichBrowserSource;
use BrowscapHelper\Source\WootheeSource;
use Monolog\Handler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class CopyTestsCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('copy-tests')
            ->setDescription('Copies tests from browscap to browser-detector');
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
        $output->writeln('init logger ...');
        $logger = new Logger('browser-detector-helper');
        $logger->pushHandler(new Handler\NullHandler());
        $logger->pushHandler(new Handler\StreamHandler('error.log', Logger::ERROR));

        try {
            $number = (new TargetDirectory())->getNextTest($output);
        } catch (\League\Flysystem\UnreadableFileException $e) {
            $logger->critical($e);
            $output->writeln($e->getMessage());

            return;
        }

        try {
            $targetDirectory = (new TargetDirectory())->getPath($output);
        } catch (\League\Flysystem\UnreadableFileException $e) {
            $logger->critical($e);
            $output->writeln($e->getMessage());

            return;
        }

        if (!file_exists($targetDirectory)) {
            mkdir($targetDirectory);
        }

        $existingTests = array_flip((new DetectorSource())->getUserAgents($logger, $output));

        $counter = 0;
        $sources = [
            new BrowscapSource(),
            new PiwikSource(),
            new UapCoreSource(),
            new WhichBrowserSource(),
            new WootheeSource(),
        ];

        $tests = [];

        foreach ($sources as $source) {
            /** @var \BrowscapHelper\Source\SourceInterface $source */
            foreach ($source->getTests($logger, $output) as $ua => $test) {
                $ua = trim($ua);

                if (isset($existingTests[$ua])) {
                    continue;
                }

                $tests[$ua] = $test;
            }
        }

        $issue = 'test-' . sprintf('%1$08d', $number);

        $this->handleTests($output, $tests, $issue, $targetDirectory, $counter);

        $output->writeln('');
        $output->writeln('Es wurden ' . $counter . ' Tests exportiert');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param array                                             $tests
     * @param string                                            $newname
     * @param string                                            $targetDirectory
     * @param int                                               $counter
     */
    private function handleTests(OutputInterface $output, array $tests, $newname, $targetDirectory, &$counter)
    {
        $chunks = array_chunk($tests, 100, true);

        $output->writeln('    ' . count($chunks) . ' chunks found');

        foreach ($chunks as $chunkId => $chunk) {
            if (!count($chunk)) {
                $output->writeln('    skip empty chunk ' . $chunkId);
                continue;
            }

            $targetFilename = $newname . '-' . sprintf('%1$08d', (int) $chunkId) . '.json';

            if (file_exists($targetDirectory . $targetFilename)) {
                $output->writeln('    target file for chunk ' . $chunkId . ' already exists');
                continue;
            }

            $this->handleChunk($output, $chunk, $targetFilename, $targetDirectory, $counter);
        }
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param array                                             $chunk
     * @param string                                            $targetFilename
     * @param string                                            $targetDirectory
     * @param int                                               $counter
     */
    private function handleChunk(OutputInterface $output, array $chunk, $targetFilename, $targetDirectory, &$counter)
    {
        $data = [];

        foreach ($chunk as $key => $test) {
            if (isset($test['properties']['Platform'])) {
                $platform = $test['properties']['Platform'];
            } else {
                $platform = 'unknown';
            }

            if (isset($test['properties']['Platform_Version'])) {
                $version = $test['properties']['Platform_Version'];
            } else {
                $version = '0.0.0';
            }

            $codename      = $platform;
            $marketingname = $platform;

            switch ($platform) {
                case 'Win10':
                    if ('10.0' === $version) {
                        $codename      = 'Windows NT 10.0';
                        $marketingname = 'Windows 10';
                    } else {
                        $codename      = 'Windows NT 6.4';
                        $marketingname = 'Windows 10';
                    }
                    $version = '0.0.0';
                    break;
                case 'Win8.1':
                    $codename      = 'Windows NT 6.3';
                    $marketingname = 'Windows 8.1';
                    $version       = '0.0.0';
                    break;
                case 'Win8':
                    $codename      = 'Windows NT 6.2';
                    $marketingname = 'Windows 8';
                    $version       = '0.0.0';
                    break;
                case 'Win7':
                    $codename      = 'Windows NT 6.1';
                    $marketingname = 'Windows 7';
                    $version       = '0.0.0';
                    break;
                case 'WinVista':
                    $codename      = 'Windows NT 6.0';
                    $marketingname = 'Windows Vista';
                    $version       = '0.0.0';
                    break;
                case 'WinXP':
                    if ('5.2' === $version) {
                        $codename      = 'Windows NT 5.2';
                        $marketingname = 'Windows XP';
                    } else {
                        $codename      = 'Windows NT 5.1';
                        $marketingname = 'Windows XP';
                    }
                    $version = '0.0.0';
                    break;
                case 'Win2000':
                    $codename      = 'Windows NT 5.0';
                    $marketingname = 'Windows 2000';
                    $version       = '0.0.0';
                    break;
                case 'WinME':
                    $codename      = 'Windows ME';
                    $marketingname = 'Windows ME';
                    $version       = '0.0.0';
                    break;
                case 'Win98':
                    $codename      = 'Windows 98';
                    $marketingname = 'Windows 98';
                    $version       = '0.0.0';
                    break;
                case 'Win95':
                    $codename      = 'Windows 95';
                    $marketingname = 'Windows 95';
                    $version       = '0.0.0';
                    break;
                case 'Win3.1':
                    $codename      = 'Windows 3.1';
                    $marketingname = 'Windows 3.1';
                    $version       = '0.0.0';
                    break;
                case 'WinPhone10':
                    $codename      = 'Windows Phone OS';
                    $marketingname = 'Windows Phone OS';
                    $version       = '10.0.0';
                    break;
                case 'WinPhone8.1':
                    $codename      = 'Windows Phone OS';
                    $marketingname = 'Windows Phone OS';
                    $version       = '8.1.0';
                    break;
                case 'WinPhone8':
                    $codename      = 'Windows Phone OS';
                    $marketingname = 'Windows Phone OS';
                    $version       = '8.0.0';
                    break;
                case 'Win32':
                    $codename      = 'Windows';
                    $marketingname = 'Windows';
                    $version       = '0.0.0';
                    break;
                case 'WinNT':
                    if ('4.0' === $version) {
                        $codename      = 'Windows NT 4.0';
                        $marketingname = 'Windows NT';
                    } elseif ('4.1' === $version) {
                        $codename      = 'Windows NT 4.1';
                        $marketingname = 'Windows NT';
                    } elseif ('3.5' === $version) {
                        $codename      = 'Windows NT 3.5';
                        $marketingname = 'Windows NT';
                    } elseif ('3.1' === $version) {
                        $codename      = 'Windows NT 3.1';
                        $marketingname = 'Windows NT';
                    } else {
                        $codename      = 'Windows NT';
                        $marketingname = 'Windows NT';
                    }
                    $version = '0.0.0';
                    break;
                case 'MacOSX':
                    $codename      = 'Mac OS X';
                    $marketingname = 'Mac OS X';
                    break;
            }

            $data[$key] = [
                'ua'         => $test['ua'],
                'properties' => [
                    'Browser_Name'            => $test['properties']['Browser'],
                    'Browser_Type'            => $test['properties']['Browser_Type'],
                    'Browser_Bits'            => $test['properties']['Browser_Bits'],
                    'Browser_Maker'           => $test['properties']['Browser_Maker'],
                    'Browser_Modus'           => $test['properties']['Browser_Modus'],
                    'Browser_Version'         => $test['properties']['Version'],
                    'Platform_Codename'       => $codename,
                    'Platform_Marketingname'  => $marketingname,
                    'Platform_Version'        => $version,
                    'Platform_Bits'           => $test['properties']['Platform_Bits'],
                    'Platform_Maker'          => $test['properties']['Platform_Maker'],
                    'Platform_Brand_Name'     => $test['properties']['Platform_Maker'],
                    'Device_Name'             => $test['properties']['Device_Name'],
                    'Device_Maker'            => $test['properties']['Device_Maker'],
                    'Device_Type'             => $test['properties']['Device_Type'],
                    'Device_Pointing_Method'  => $test['properties']['Device_Pointing_Method'],
                    'Device_Dual_Orientation' => false,
                    'Device_Code_Name'        => $test['properties']['Device_Code_Name'],
                    'Device_Brand_Name'       => $test['properties']['Device_Brand_Name'],
                    'RenderingEngine_Name'    => $test['properties']['RenderingEngine_Name'],
                    'RenderingEngine_Version' => $test['properties']['RenderingEngine_Version'],
                    'RenderingEngine_Maker'   => $test['properties']['RenderingEngine_Maker'],
                ],
            ];

            ++$counter;
        }

        $output->writeln('    writing file ' . $targetFilename);

        file_put_contents(
            $targetDirectory . $targetFilename,
            json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }
}
