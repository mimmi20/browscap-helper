<?php

/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2026, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Command;

use BrowscapHelper\Entity\TestData;
use BrowscapHelper\Helper\ExistingTestsLoader;
use BrowscapHelper\Helper\ExistingTestsRemover;
use BrowscapHelper\Helper\RewriteTests;
use BrowscapHelper\Source\CrawlerDetectSource;
use BrowscapHelper\Source\DonatjSource;
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\MatomoSource;
use BrowscapHelper\Source\MobileDetectSource;
use BrowscapHelper\Source\PdoSource;
use BrowscapHelper\Source\SourceInterface;
use BrowscapHelper\Source\TxtCounterFileSource;
use BrowscapHelper\Source\TxtFileSource;
use BrowscapHelper\Source\WhichBrowserSource;
use BrowscapHelper\Source\WootheeSource;
use BrowscapHelper\Source\YamlFileSource;
use BrowscapHelper\Traits\FilterHeaderTrait;
use DateTimeImmutable;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentSize;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentStyle;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptions;
use Ergebnis\Json\Normalizer\Exception\InvalidNewLineString;
use JsonException;
use Override;
use PDO;
use Pdo\Mysql;
use PDOException;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use UnexpectedValueException;

use function array_key_exists;
use function count;
use function is_string;
use function json_encode;
use function sprintf;

use const JSON_THROW_ON_ERROR;

#[AsCommand(name: 'copy-tests')]
final class CopyTestsCommand extends Command
{
    use FilterHeaderTrait;

    /** @throws LogicException */
    public function __construct(
        private readonly ExistingTestsLoader $existingTestsLoader,
        private readonly ExistingTestsRemover $existingTestsRemover,
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
                mode: InputOption::VALUE_REQUIRED,
                description: 'Where the resource files are located',
                default: $this->sourcesDirectory,
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
     *
     * @phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleLogger = new ConsoleLogger($output, [
            LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ALERT => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::CRITICAL => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ERROR => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::WARNING => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::NOTICE => OutputInterface::VERBOSITY_VERBOSE,
            LogLevel::INFO => OutputInterface::VERBOSITY_VERBOSE,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_DEBUG,
        ]);

        $testSource      = 'tests';
        $txtTotalCounter = 0;

        /** @var array<TestData> $txtChecks */
        $txtChecks = [];

        $sourcesExisting = [
            new JsonFileSource(consoleLogger: $consoleLogger, dir: $testSource),
            new YamlFileSource(consoleLogger: $consoleLogger, dir: $testSource),
        ];

        $output->writeln('reading already existing tests ...', OutputInterface::VERBOSITY_NORMAL);

        $txtChecks = $this->readTests(
            $output,
            $consoleLogger,
            $sourcesExisting,
            $txtChecks,
            $txtTotalCounter,
        );

        $sourcesDirectory = $input->getOption('resources');

        $this->existingTestsRemover->remove($output, $testSource);

        $output->writeln('init sources ...', OutputInterface::VERBOSITY_NORMAL);

        $sourcesNew = [
            new CrawlerDetectSource(consoleLogger: $consoleLogger),
            new DonatjSource(consoleLogger: $consoleLogger),
            new MatomoSource(consoleLogger: $consoleLogger),
            new MobileDetectSource(consoleLogger: $consoleLogger),
            new WhichBrowserSource(consoleLogger: $consoleLogger),
            new WootheeSource(consoleLogger: $consoleLogger),
            new TxtFileSource(consoleLogger: $consoleLogger, dir: $sourcesDirectory),
            new TxtCounterFileSource(consoleLogger: $consoleLogger, dir: $sourcesDirectory),
        ];

        try {
            $sourcesNew[] = $this->addPdoSource(consoleLogger: $consoleLogger, dbname: 'g');
        } catch (PDOException) {
            $output->writeln(
                '<error>An error occured while initializing the database "g"</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            $sourcesNew[] = $this->addPdoSource(consoleLogger: $consoleLogger, dbname: 'a');
        } catch (PDOException) {
            $output->writeln(
                '<error>An error occured while initializing the database "a"</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            $sourcesNew[] = $this->addPdoSource(consoleLogger: $consoleLogger, dbname: 'k');
        } catch (PDOException) {
            $output->writeln(
                '<error>An error occured while initializing the database "k"</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            $sourcesNew[] = $this->addPdoSource(consoleLogger: $consoleLogger, dbname: 's');
        } catch (PDOException) {
            $output->writeln(
                '<error>An error occured while initializing the database "s"</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            $sourcesNew[] = $this->addPdoSource(consoleLogger: $consoleLogger, dbname: 'v');
        } catch (PDOException) {
            $output->writeln(
                '<error>An error occured while initializing the database "v"</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );
        }

        $output->writeln('copy tests from sources ...', OutputInterface::VERBOSITY_NORMAL);
        $txtTotalCounter = 0;

        $txtChecks = $this->readTests(
            $output,
            $consoleLogger,
            $sourcesNew,
            $txtChecks,
            $txtTotalCounter,
        );

        $output->writeln('rewrite tests ...', OutputInterface::VERBOSITY_NORMAL);

        if ($txtChecks !== []) {
            $this->rewriteTests->rewrite($output, $txtChecks, $testSource);
        }

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

    /** @throws PDOException */
    private function addPdoSource(
        ConsoleLogger $consoleLogger,
        string $dbname,
        string $host = 'localhost',
        int $port = 3306,
        string $charset = 'utf8mb4',
        string $user = 'root',
        string $password = '',
    ): PdoSource {
        $driverOptions = [
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_PERSISTENT => true,
            Mysql::ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
        ];

        $pdo = new PDO(
            sprintf('mysql:dbname=%s;host=%s;port=%s;charset=%s', $dbname, $host, $port, $charset),
            $user,
            $password,
            $driverOptions,
        );

        return new PdoSource(pdo: $pdo, consoleLogger: $consoleLogger, dbName: $dbname);
    }

    /**
     * @param array<int, SourceInterface> $sources
     * @param array<string, TestData>     $txtChecks
     *
     * @return array<string, TestData>
     *
     * @throws RuntimeException
     */
    private function readTests(
        OutputInterface $output,
        LoggerInterface $logger,
        array $sources,
        array $txtChecks,
        int &$txtTotalCounter,
    ): array {
        foreach (
            $this->existingTestsLoader->getProperties(
                output: new NullOutput(),
                logger: $logger,
                sources: $sources,
            ) as $testProperty
        ) {
            $property = [
                'headers' => $this->filterHeaders($output, $testProperty->getResult()->getHeaders()),
                'date-first' => $testProperty->getDateFirst(),
                'date-last' => $testProperty->getDateLast(),
            ];

            $seachHeader = Yaml::dump($property['headers']);

            if (array_key_exists($seachHeader, $txtChecks)) {
                if (is_string($property['date-first'])) {
                    $dateOld = $txtChecks[$seachHeader]->getDateFirst();
                    $dateNew = DateTimeImmutable::createFromFormat('Y-m-d', $property['date-first']);

                    if ($dateNew !== false && $dateOld > $dateNew) {
                        /** @var non-empty-string $firstDate */
                        // @phpstan-ignore-next-line varTag.type
                        $firstDate = $dateNew->format('Y-m-d');

                        /** @var non-empty-string $lastDate */
                        // @phpstan-ignore-next-line varTag.type
                        $lastDate = $txtChecks[$seachHeader]->getDateLast()->format('Y-m-d');

                        $txtChecks[$seachHeader] = $txtChecks[$seachHeader]->withDateLast($dateNew);
                        $txtChecks[$seachHeader] = $txtChecks[$seachHeader]->withSources(
                            [
                                $testProperty->getSourceName() => [
                                    'date-first' => $firstDate,
                                    'date-last' => $lastDate,
                                ],
                            ],
                        );
                    }
                }

                if (is_string($property['date-last'])) {
                    $dateOld = $txtChecks[$seachHeader]->getDateLast();
                    $dateNew = DateTimeImmutable::createFromFormat('Y-m-d', $property['date-last']);

                    if ($dateNew !== false && $dateOld < $dateNew) {
                        /** @var non-empty-string $firstDate */
                        // @phpstan-ignore-next-line varTag.type
                        $firstDate = $txtChecks[$seachHeader]->getDateFirst()->format('Y-m-d');

                        /** @var non-empty-string $lastDate */
                        // @phpstan-ignore-next-line varTag.type
                        $lastDate = $dateNew->format('Y-m-d');

                        $txtChecks[$seachHeader] = $txtChecks[$seachHeader]->withDateLast($dateNew);
                        $txtChecks[$seachHeader] = $txtChecks[$seachHeader]->withSources(
                            [
                                $testProperty->getSourceName() => [
                                    'date-first' => $firstDate,
                                    'date-last' => $lastDate,
                                ],
                            ],
                        );
                    }
                }

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

            if (is_string($property['date-first'])) {
                $date = DateTimeImmutable::createFromFormat('Y-m-d', $property['date-first']);

                if ($date === false) {
                    $date = new DateTimeImmutable('now');
                }

                $property['date-first'] = $date;
            } else {
                $property['date-first'] = new DateTimeImmutable('now');
            }

            if (is_string($property['date-last'])) {
                $date = DateTimeImmutable::createFromFormat('Y-m-d', $property['date-last']);

                if ($date === false) {
                    $date = new DateTimeImmutable('now');
                }

                $property['date-last'] = $date;
            } else {
                $property['date-last'] = new DateTimeImmutable('now');
            }

            /** @var non-empty-string $firstDate */
            // @phpstan-ignore-next-line varTag.type
            $firstDate = $property['date-first']->format('Y-m-d');

            /** @var non-empty-string $lastDate */
            // @phpstan-ignore-next-line varTag.type
            $lastDate = $property['date-last']->format('Y-m-d');

            $txtChecks[$seachHeader] = new TestData(
                headers: $property['headers'],
                dateFirst: $property['date-first'],
                dateLast: $property['date-last'],
                sources: [
                    $testProperty->getSourceName() => [
                        'date-first' => $firstDate,
                        'date-last' => $lastDate,
                    ],
                ],
            );

            ++$txtTotalCounter;
        }

        return $txtChecks;
    }
}
