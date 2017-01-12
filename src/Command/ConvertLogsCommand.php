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

use BrowscapHelper\Source\LogFileSource;
use Monolog\Handler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class ConvertLogsCommand extends Command
{
    /**
     * @var string
     */
    private $sourcesDirectory = '';

    /**
     * @var string
     */
    private $targetDirectory = '';

    /**
     * @param string $sourcesDirectory
     * @param string $targetDirectory
     *
     * @throws \Symfony\Component\Console\Exception\LogicException
     */
    public function __construct($sourcesDirectory, $targetDirectory)
    {
        $this->sourcesDirectory = $sourcesDirectory;
        $this->targetDirectory  = $targetDirectory;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this
            ->setName('convert-logs')
            ->setDescription('Reads the server logs, extracts the useragents and writes them into a file')
            ->addOption(
                'resources',
                null,
                InputOption::VALUE_REQUIRED,
                'Where the resource files are located',
                $this->sourcesDirectory
            )
            ->addOption(
                'target',
                null,
                InputOption::VALUE_REQUIRED,
                'Where the target files should be written',
                $this->targetDirectory
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
        $targetDirectory  = $input->getOption('target');
        $sourcesDirectory = $input->getOption('resources');

        $logger = new Logger('browser-detector-helper');
        $logger->pushHandler(new Handler\NullHandler());
        $logger->pushHandler(new Handler\StreamHandler('error.log', Logger::ERROR));

        $counter        = 0;
        $targetBulkFile = $targetDirectory . date('Y-m-d') . '-testagents.txt';

        $output->writeln("reading from directory '" . $sourcesDirectory . "'");
        $output->writeln("writing to file '" . $targetBulkFile . "'");

        foreach ((new LogFileSource($sourcesDirectory))->getUserAgents($logger, $output) as $agent) {
            file_put_contents($targetBulkFile, $agent . "\n", FILE_APPEND | LOCK_EX);
            ++$counter;
        }

        $output->writeln('');
        $output->writeln('finished reading files.');
        $output->writeln('');
        $output->writeln($counter . ' new  agents added');
    }
}
