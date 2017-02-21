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
use BrowscapHelper\Source\CollectionSource;
use BrowscapHelper\Source\DetectorSource;
use BrowscapHelper\Source\PiwikSource;
use BrowscapHelper\Source\UapCoreSource;
use BrowscapHelper\Source\WhichBrowserSource;
use BrowscapHelper\Source\WootheeSource;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use League\Flysystem\UnreadableFileException;
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
            ->setDescription('Copies tests from browscap and other libraries to browser-detector');
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
        $logger->pushHandler(new Handler\StreamHandler('log/error.log', Logger::ERROR));

        $output->writeln('init cache ...');
        $adapter  = new Local(__DIR__ . '/../../cache/');
        $cache    = new FilesystemCachePool(new Filesystem($adapter));

        $targetDirectoryHelper = new TargetDirectory();

        $output->writeln('detect next test number ...');
        try {
            $number = $targetDirectoryHelper->getNextTest($output);
        } catch (UnreadableFileException $e) {
            $logger->critical($e);
            $output->writeln($e->getMessage());

            return;
        }

        $output->writeln('detect directory to write new tests ...');
        try {
            $targetDirectory = $targetDirectoryHelper->getPath($output);
        } catch (UnreadableFileException $e) {
            $logger->critical($e);
            $output->writeln($e->getMessage());

            return;
        }

        if (!file_exists($targetDirectory)) {
            mkdir($targetDirectory);
        }

        $output->writeln('read existing tests ...');
        $existingTests = [];
        foreach ((new DetectorSource($logger, $output, $cache))->getUserAgents() as $ua) {
            $ua = trim($ua);

            if (isset($existingTests[$ua])) {
                continue;
            }

            $existingTests[$ua] = 1;
        }

        $output->writeln('init sources ...');
        $counter = 0;
        $source  = new CollectionSource(
            [
                new BrowscapSource($logger, $output, $cache),
                new PiwikSource($logger, $output),
                new UapCoreSource($logger, $output),
                new WhichBrowserSource($logger, $output),
                new WootheeSource($logger, $output),
            ]
        );

        $output->writeln('import tests ...');
        $chunkCounter = 0;
        $fileCounter  = 0;
        $data         = [];

        foreach ($source->getTests() as $ua => $result) {
            $ua = trim($ua);

            if (isset($existingTests[$ua])) {
                continue;
            }

            $targetFilename = 'test-' . sprintf('%1$05d', $number) . '-' . sprintf('%1$05d', (int) $fileCounter) . '.json';

            if (file_exists($targetDirectory . $targetFilename)) {
                $output->writeln('    target file for chunk ' . $fileCounter . ' already exists');
                continue;
            }

            $key = 'test-' . sprintf('%1$08d', $number) . '-' . sprintf('%1$08d', $chunkCounter);

            $data[$key] = [
                'ua'     => $ua,
                'result' => $result->toArray(),
            ];

            ++$counter;
            ++$chunkCounter;

            if ($chunkCounter >= 100) {
                $output->writeln('    writing file ' . $targetFilename);

                file_put_contents(
                    $targetDirectory . $targetFilename,
                    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                );

                $chunkCounter = 0;
                $data         = [];
                ++$fileCounter;
            }

            if ($fileCounter >= 200) {
                $fileCounter     = 0;
                $number          = $targetDirectoryHelper->getNextTest($output);
                $targetDirectory = $targetDirectoryHelper->getPath($output);
            }
        }

        $output->writeln('');
        $output->writeln('Es wurden ' . $counter . ' Tests exportiert');
    }
}
