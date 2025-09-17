<?php

/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2025, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Command;

use BrowscapHelper\Helper\ExistingTestsLoader;
use BrowscapHelper\Helper\ExistingTestsRemover;
use BrowscapHelper\Helper\RewriteTests;
use BrowscapHelper\Source\CrawlerDetectSource;
use BrowscapHelper\Source\DonatjSource;
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\MatomoSource;
use BrowscapHelper\Source\MobileDetectSource;
use BrowscapHelper\Source\PdoSource;
use BrowscapHelper\Source\TxtCounterFileSource;
use BrowscapHelper\Source\TxtFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
use BrowscapHelper\Source\WhichBrowserSource;
use BrowscapHelper\Source\WootheeSource;
use BrowscapHelper\Traits\FilterHeaderTrait;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentSize;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentStyle;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptions;
use Ergebnis\Json\Normalizer\Exception\InvalidNewLineString;
use JsonException;
use Override;
use PDO;
use PDOException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use UnexpectedValueException;

use function array_key_exists;
use function count;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

final class CopyTestsCommand extends Command
{
    use FilterHeaderTrait;

    /** @throws LogicException */
    public function __construct(
        private readonly ExistingTestsLoader $testsLoader,
        private readonly ExistingTestsRemover $testsRemover,
        private readonly RewriteTests $rewriteTests,
        private readonly string $sourcesDirectory = '',
    ) {
        parent::__construct();
    }

    /**
     * Configures the current command.
     *
     * @throws InvalidArgumentException
     */
    #[Override]
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
                $this->sourcesDirectory,
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
     * @throws InvalidJsonEncodeOptions
     * @throws InvalidNewLineString
     * @throws InvalidIndentStyle
     * @throws InvalidIndentSize
     * @throws UnexpectedValueException
     * @throws \LogicException
     * @throws RuntimeException
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $testSource = 'tests';
        $txtChecks  = [];

        $sources = [new JsonFileSource($testSource)];

        $output->writeln('reading already existing tests ...', OutputInterface::VERBOSITY_NORMAL);

        foreach ($this->testsLoader->getProperties($output, $sources) as $row) {
            $row['headers'] = $this->filterHeaders($output, $row['headers']);

            $seachHeader = (string) UserAgent::fromHeaderArray($row['headers']);

            if (array_key_exists($seachHeader, $txtChecks)) {
                continue;
            }

            $txtChecks[$seachHeader] = 1;
        }

        $sourcesDirectory = $input->getOption('resources');

        $this->testsRemover->remove($output, $testSource);

        $output->writeln('init sources ...', OutputInterface::VERBOSITY_NORMAL);

        $sources = [
            new CrawlerDetectSource(),
            new DonatjSource(),
            new MatomoSource(),
            new MobileDetectSource(),
            new WhichBrowserSource(),
            new WootheeSource(),
            new TxtFileSource($sourcesDirectory),
            new TxtCounterFileSource($sourcesDirectory),
        ];

        try {
            $dbname   = 'ua';
            $host     = 'localhost';
            $port     = 3306;
            $charset  = 'utf8mb4';
            $user     = 'root';
            $password = '';

            $driverOptions = [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_DIRECT_QUERY => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
            ];

            $pdo = new PDO(
                sprintf('mysql:dbname=%s;host=%s;port=%s;charset=%s', $dbname, $host, $port, $charset),
                $user,
                $password,
                $driverOptions,
            );

            $sources[] = new PdoSource($pdo);
        } catch (PDOException) {
            $output->writeln(
                '<error>An error occured while initializing the database</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            $dbname   = 'ua3';
            $host     = 'localhost';
            $port     = 3306;
            $charset  = 'utf8mb4';
            $user     = 'root';
            $password = '';

            $driverOptions = [
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_PERSISTENT => true,
                PDO::MYSQL_ATTR_DIRECT_QUERY => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
            ];

            $pdo = new PDO(
                sprintf('mysql:dbname=%s;host=%s;port=%s;charset=%s', $dbname, $host, $port, $charset),
                $user,
                $password,
                $driverOptions,
            );

            $sources[] = new PdoSource($pdo);
        } catch (PDOException) {
            $output->writeln(
                '<error>An error occured while initializing the database</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );
        }

        $output->writeln('copy tests from sources ...', OutputInterface::VERBOSITY_NORMAL);
        $txtTotalCounter = 0;

        foreach ($this->testsLoader->getProperties($output, $sources) as $test) {
            $test['headers'] = $this->filterHeaders($output, $test['headers']);

            $seachHeader = (string) UserAgent::fromHeaderArray($test['headers']);

            if (array_key_exists($seachHeader, $txtChecks)) {
                continue;
            }

            try {
                json_encode($seachHeader, JSON_THROW_ON_ERROR);
            } catch (JsonException) {
                $output->writeln(
                    '<comment>' . sprintf(
                        'Header "%s" contained illegal characters --> skipped',
                        $seachHeader,
                    ) . '</comment>',
                    OutputInterface::VERBOSITY_VERY_VERBOSE,
                );

                continue;
            }

            $txtChecks[$seachHeader] = $test;
            ++$txtTotalCounter;
        }

        $output->writeln('rewrite tests ...', OutputInterface::VERBOSITY_NORMAL);

        $this->rewriteTests->rewrite($output, $txtChecks, $testSource);

        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(
            'tests copied for Browscap helper:    ' . $txtTotalCounter,
            OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(
            'tests available for Browscap helper: ' . count($txtChecks),
            OutputInterface::VERBOSITY_NORMAL,
        );

        return self::SUCCESS;
    }
}
