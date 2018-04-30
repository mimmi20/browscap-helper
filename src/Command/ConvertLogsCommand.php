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
namespace BrowscapHelper\Command;

use BrowscapHelper\Source\LogFileSource;
use BrowscapHelper\Source\TxtFileSource;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class ConvertLogsCommand
 *
 * @category   Browscap Helper
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
     * @throws \LogicException       When this abstract method is not implemented
     * @throws \FileLoader\Exception
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $this->logger->pushHandler(new PsrHandler($consoleLogger));

        $sourcesDirectory = $input->getOption('resources');

        $output->writeln('reading already existing tests ...');

        $testSource = 'tests';
        $txtChecks  = [];

        foreach ($this->getHelper('useragent')->getUserAgents(new TxtFileSource($this->logger, $testSource), false) as $useragent) {
            if (array_key_exists($useragent, $txtChecks)) {
                $this->logger->alert('    UA "' . $useragent . '" added more than once --> skipped');

                continue;
            }

            $txtChecks[$useragent] = 1;
        }

        $output->writeln('remove existing tests ...');

        $finder = new Finder();
        $finder->files();
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($testSource);

        foreach ($finder as $file) {
            unlink($file->getPathname());
        }

        $output->writeln("reading new files from directory '" . $sourcesDirectory . "' ...");
        $txtTotalCounter = 0;

        foreach ($this->getHelper('useragent')->getUserAgents(new LogFileSource($this->logger, $sourcesDirectory)) as $useragent) {
            if (array_key_exists($useragent, $txtChecks)) {
                continue;
            }

            $txtChecks[$useragent] = 1;
            ++$txtTotalCounter;
        }

        $output->writeln('rewrite tests ...');

        $folderChunks = array_chunk(array_unique(array_keys($txtChecks)), 1000);

        foreach ($folderChunks as $folderId => $folderChunk) {
            $this->getHelper('txt-test-writer')->write(
                $folderChunk,
                $testSource,
                $folderId
            );

            $jsonTests = [];

            foreach ($folderChunk as $id => $useragent) {
                $jsonTests[$id] = ['user-agent' => $useragent];
            }

            $this->getHelper('json-test-writer')->write(
                $jsonTests,
                $testSource,
                $folderId
            );
        }

        $output->writeln('');
        $output->writeln('tests converted for Browscap helper: ' . $txtTotalCounter);
        $output->writeln('tests available for Browscap helper: ' . count($txtChecks));

        return 0;
    }
}
