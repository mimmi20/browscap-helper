<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Command;

use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\LogFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function array_key_exists;
use function count;
use function sprintf;

final class ConvertLogsCommand extends Command
{
    private string $sourcesDirectory = '';

    /**
     * @throws LogicException
     */
    public function __construct(string $sourcesDirectory)
    {
        $this->sourcesDirectory = $sourcesDirectory;

        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws InvalidArgumentException
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
     * @see    setCode()
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @throws LogicException           When this abstract method is not implemented
     * @throws InvalidArgumentException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $sourcesDirectory = $input->getOption('resources');

        $testSource = 'tests';
        $txtChecks  = [];

        $output->writeln('reading already existing tests ...', OutputInterface::VERBOSITY_NORMAL);

        foreach ($this->getHelper('existing-tests-loader')->getProperties($output, [new JsonFileSource($testSource)]) as $row) {
            $seachHeader = (string) UserAgent::fromHeaderArray($row['headers']);

            if (array_key_exists($seachHeader, $txtChecks)) {
                $output->writeln('<error>' . sprintf('Header "%s" added more than once --> skipped', $seachHeader) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            $txtChecks[$seachHeader] = 1;
        }

        $this->getHelper('existing-tests-remover')->remove($output, $testSource);

        $output->writeln('init sources ...', OutputInterface::VERBOSITY_NORMAL);

        $source = new LogFileSource($sourcesDirectory);

        $output->writeln('copy tests from sources ...', OutputInterface::VERBOSITY_NORMAL);
        $txtTotalCounter = 0;

        foreach ($this->getHelper('existing-tests-loader')->getProperties($output, [$source]) as $test) {
            $seachHeader = (string) UserAgent::fromHeaderArray($test['headers']);

            if (array_key_exists($seachHeader, $txtChecks)) {
                $output->writeln('<debug>' . sprintf('Header "%s" added more than once --> skipped', $seachHeader) . '</debug>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            $txtChecks[$seachHeader] = $test;
            ++$txtTotalCounter;
        }

        $output->writeln('rewrite tests ...', OutputInterface::VERBOSITY_NORMAL);

        $this->getHelper('rewrite-tests')->rewrite($output, $txtChecks, $testSource);

        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
        $output->writeln('tests converted for Browscap helper: ' . $txtTotalCounter, OutputInterface::VERBOSITY_NORMAL);
        $output->writeln('tests available for Browscap helper: ' . count($txtChecks), OutputInterface::VERBOSITY_NORMAL);

        return self::SUCCESS;
    }
}
