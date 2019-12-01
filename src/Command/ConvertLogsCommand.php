<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2019, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Command;

use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\LogFileSource;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

final class ConvertLogsCommand extends Command
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
    public function __construct(string $sourcesDirectory, string $targetDirectory)
    {
        $this->sourcesDirectory = $sourcesDirectory;
        $this->targetDirectory  = $targetDirectory;

        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
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
     * @throws \Symfony\Component\Console\Exception\LogicException           When this abstract method is not implemented
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleLogger = new ConsoleLogger($output);

        $sourcesDirectory = $input->getOption('resources');

        $testSource = 'tests';
        $txtChecks  = [];

        $output->writeln('reading already existing tests ...');

        foreach ($this->getHelper('existing-tests-loader')->getHeaders($consoleLogger, [new JsonFileSource($consoleLogger, $testSource)]) as $seachHeader) {
            if (array_key_exists($seachHeader, $txtChecks)) {
                $consoleLogger->alert('    Header "' . $seachHeader . '" added more than once --> skipped');

                continue;
            }

            $txtChecks[$seachHeader] = 1;
        }

        $output->writeln('remove existing tests ...');

        $this->getHelper('existing-tests-remover')->remove($testSource);

        $output->writeln('init sources ...');

        $source = new LogFileSource($consoleLogger, $sourcesDirectory);

        $output->writeln('copy tests from sources ...');
        $txtTotalCounter = 0;

        foreach ($this->getHelper('existing-tests-loader')->getHeaders($consoleLogger, [$source]) as $seachHeader) {
            if (array_key_exists($seachHeader, $txtChecks)) {
                $consoleLogger->debug('    Header "' . $seachHeader . '" added more than once --> skipped');

                continue;
            }

            $txtChecks[$seachHeader] = 1;
            ++$txtTotalCounter;
        }

        $output->writeln('rewrite tests ...');

        $this->getHelper('rewrite-tests')->rewrite($consoleLogger, $txtChecks, $testSource);

        $output->writeln('');
        $output->writeln('tests converted for Browscap helper: ' . $txtTotalCounter);
        $output->writeln('tests available for Browscap helper: ' . count($txtChecks));

        return 0;
    }
}
