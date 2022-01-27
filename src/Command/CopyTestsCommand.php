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

use BrowscapHelper\Source\BrowscapSource;
use BrowscapHelper\Source\CbschuldSource;
use BrowscapHelper\Source\CrawlerDetectSource;
use BrowscapHelper\Source\DonatjSource;
use BrowscapHelper\Source\EndorphinSource;
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\MatomoSource;
use BrowscapHelper\Source\MobileDetectSource;
use BrowscapHelper\Source\SinergiSource;
use BrowscapHelper\Source\TxtCounterFileSource;
use BrowscapHelper\Source\TxtFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
use BrowscapHelper\Source\WhichBrowserSource;
use BrowscapHelper\Source\WootheeSource;
use BrowscapHelper\Source\ZsxsoftSource;
use JsonException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function array_key_exists;
use function count;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class CopyTestsCommand extends Command
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
            ->setName('copy-tests')
            ->setDescription('Copies tests from browscap and other libraries')
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
        $testSource = 'tests';
        $txtChecks  = [];

        $sources = [new JsonFileSource($testSource)];

        $output->writeln('reading already existing tests ...', OutputInterface::VERBOSITY_NORMAL);

        foreach ($this->getHelper('existing-tests-loader')->getProperties($output, $sources) as $row) {
            $seachHeader = (string) UserAgent::fromHeaderArray($row['headers']);

            if (array_key_exists($seachHeader, $txtChecks)) {
                $output->writeln('<error>' . sprintf('Header "%s" added more than once --> skipped', $seachHeader) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            $txtChecks[$seachHeader] = 1;
        }

        $sourcesDirectory = $input->getOption('resources');

        //$this->getHelper('existing-tests-remover')->remove($output, $testSource);

        $output->writeln('init sources ...', OutputInterface::VERBOSITY_NORMAL);

        $sources = [
            new BrowscapSource(),
            new CbschuldSource(),
            new CrawlerDetectSource(),
            new DonatjSource(),
            new EndorphinSource(),
            new MatomoSource(),
            new MobileDetectSource(),
            new SinergiSource(),
            new WhichBrowserSource(),
            new WootheeSource(),
            new ZsxsoftSource(),
            new TxtFileSource($sourcesDirectory),
            new TxtCounterFileSource($sourcesDirectory),
        ];

        $output->writeln('copy tests from sources ...', OutputInterface::VERBOSITY_NORMAL);
        $txtTotalCounter = 0;

        foreach ($this->getHelper('existing-tests-loader')->getProperties($output, $sources) as $test) {
            $seachHeader = (string) UserAgent::fromHeaderArray($test['headers']);

            if (array_key_exists($seachHeader, $txtChecks)) {
                continue;
            }

            try {
                json_encode($seachHeader, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                $output->writeln('<comment>' . sprintf('Header "%s" contained illegal characters --> skipped', $seachHeader) . '</comment>', OutputInterface::VERBOSITY_VERY_VERBOSE);

                continue;
            }

            $txtChecks[$seachHeader] = $test;
            ++$txtTotalCounter;
        }

        $output->writeln('rewrite tests ...', OutputInterface::VERBOSITY_NORMAL);

        $this->getHelper('rewrite-tests')->rewrite($output, $txtChecks, $testSource);

        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
        $output->writeln('tests copied for Browscap helper:    ' . $txtTotalCounter, OutputInterface::VERBOSITY_NORMAL);
        $output->writeln('tests available for Browscap helper: ' . count($txtChecks), OutputInterface::VERBOSITY_NORMAL);

        return self::SUCCESS;
    }
}
