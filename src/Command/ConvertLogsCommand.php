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
namespace BrowscapHelper\Command;

use BrowscapHelper\Helper\TargetDirectory;
use BrowscapHelper\Source\LogFileSource;
use BrowscapHelper\Source\TxtFileSource;
use BrowscapHelper\Writer\TxtTestWriter;
use BrowscapHelper\Writer\TxtWriter;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class ConvertLogsCommand
 *
 * @category   Browscap Helper
 *
 * @author     Thomas Müller <mimmi20@live.de>
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
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @param \Monolog\Logger $logger
     * @param string          $sourcesDirectory
     * @param string          $targetDirectory
     */
    public function __construct(Logger $logger, string $sourcesDirectory, string $targetDirectory)
    {
        $this->sourcesDirectory = $sourcesDirectory;
        $this->targetDirectory  = $targetDirectory;
        $this->logger           = $logger;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
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
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $this->logger->pushHandler(new PsrHandler($consoleLogger));

        $targetDirectory  = $input->getOption('target');
        $sourcesDirectory = $input->getOption('resources');

        $counter        = 0;
        $targetBulkFile = $targetDirectory . '/' . date('Y-m-d') . '-testagents.txt';

        $output->writeln("reading from directory '" . $sourcesDirectory . "'");

        $targetDirectoryHelper = new TargetDirectory();
        $testSource            = 'tests/';

        $output->writeln('detect next test number for Browscap helper ...');

        try {
            $number = $targetDirectoryHelper->getNextTest($testSource);
        } catch (\UnexpectedValueException $e) {
            $this->logger->critical($e);
            $output->writeln($e->getMessage());

            return 1;
        }

        $output->writeln('next test for Browscap helper: ' . $number);
        $output->writeln('read existing tests for Browscap helper ...');

        $browscapChecks = [];
        $txtChecks      = [];

        foreach ((new TxtFileSource($this->logger, $testSource))->getUserAgents() as $useragent) {
            $useragent = trim($useragent);

            if (array_key_exists($useragent, $txtChecks)) {
                $this->logger->alert('    UA "' . $useragent . '" added more than once --> skipped');

                continue;
            }

            $txtChecks[$useragent] = 1;
        }

        $txtWriter       = new TxtWriter($this->logger, $targetBulkFile);
        $txtTestWriter   = new TxtTestWriter($this->logger);
        $allAgents       = [];
        $txtTotalCounter = 0;

        foreach ((new LogFileSource($this->logger, $sourcesDirectory))->getUserAgents() as $useragent) {
            $useragent = trim($useragent);

            if (isset($txtChecks[$useragent])) {
                $this->logger->info('    UA "' . $useragent . '" added more than once --> skipped');

                continue;
            }

            $txtChecks[$useragent] = 1;

            if ($txtTestWriter->write($useragent, $testSource, $number, $txtTotalCounter)) {
                ++$number;
            }

            if (array_key_exists($useragent, $allAgents)) {
                $this->logger->info('    UA "' . $useragent . '" added more than once --> skipped');

                continue;
            }

            $allAgents[$useragent] = 1;

            $txtWriter->write($useragent);
            ++$counter;
        }

        $output->writeln('');
        $output->writeln('tests converted for general use:     ' . $counter);
        $output->writeln('tests converted for Browscap helper: ' . $txtTotalCounter);

        return 0;
    }
}
