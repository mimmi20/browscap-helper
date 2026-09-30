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
use Override;
use PDO;
use Pdo\Mysql;
use PDOException;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;
use UnexpectedValueException;

use function array_key_exists;
use function count;
use function is_string;
use function sprintf;

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
     * @throws UnexpectedValueException
     * @throws \LogicException
     * @throws RuntimeException
     *
     * @phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $testSource      = 'tests';
        $txtChecks       = [];
        $txtTotalCounter = 0;

        $sourcesExisting = [new JsonFileSource($testSource), new YamlFileSource($testSource)];

        $output->writeln('reading already existing tests ...', OutputInterface::VERBOSITY_NORMAL);

        $txtChecks = $this->readTests($output, $sourcesExisting, $txtChecks, $txtTotalCounter);

        $sourcesDirectory = $input->getOption('resources');

        $this->existingTestsRemover->remove($output, $testSource);

        $output->writeln('init sources ...', OutputInterface::VERBOSITY_NORMAL);

        $sourcesNew = [
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
            $sourcesNew[] = $this->addPdoSource(dbname: 'g');
        } catch (PDOException) {
            $output->writeln(
                '<error>An error occured while initializing the database "g"</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            $sourcesNew[] = $this->addPdoSource(dbname: 'a');
        } catch (PDOException) {
            $output->writeln(
                '<error>An error occured while initializing the database "a"</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            $sourcesNew[] = $this->addPdoSource(dbname: 'k');
        } catch (PDOException) {
            $output->writeln(
                '<error>An error occured while initializing the database "k"</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            $sourcesNew[] = $this->addPdoSource(dbname: 's');
        } catch (PDOException) {
            $output->writeln(
                '<error>An error occured while initializing the database "s"</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            $sourcesNew[] = $this->addPdoSource(dbname: 'v');
        } catch (PDOException) {
            $output->writeln(
                '<error>An error occured while initializing the database "v"</error>',
                OutputInterface::VERBOSITY_NORMAL,
            );
        }

        $output->writeln('copy tests from sources ...', OutputInterface::VERBOSITY_NORMAL);
        $txtTotalCounter = 0;

        $txtChecks = $this->readTests($output, $sourcesNew, $txtChecks, $txtTotalCounter);

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

    /** @throws PDOException */
    private function addPdoSource(
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

        return new PdoSource($pdo);
    }

    /**
     * @param array<int, SourceInterface>                                                                 $sources
     * @param array<string, array{headers: array<string, string>, date-first: string, date-last: string}> $txtChecks
     *
     * @return array<string, array{headers: array<string, string>, date-first: string, date-last: string}>
     *
     * @throws RuntimeException
     */
    private function readTests(OutputInterface $output, array $sources, array $txtChecks, int &$txtTotalCounter): array
    {
        foreach ($this->existingTestsLoader->getProperties($output, $sources) as $property) {
            $property['headers'] = $this->filterHeaders($output, $property['headers']);

            $seachHeader = Yaml::dump($property['headers']);

            if (array_key_exists($seachHeader, $txtChecks)) {
                if (array_key_exists('date-first', $property) && is_string($property['date-first'])) {
                    $dateOld = DateTimeImmutable::createFromFormat(
                        'Y-m-d',
                        $txtChecks[$seachHeader]['date-first'],
                    );
                    $dateNew = DateTimeImmutable::createFromFormat('Y-m-d', $property['date-first']);

                    if ($dateOld !== false && $dateNew !== false && $dateOld > $dateNew) {
                        $txtChecks[$seachHeader]['date-first'] = $dateNew->format('Y-m-d');
                    }
                }

                if (array_key_exists('date-last', $property) && is_string($property['date-last'])) {
                    $dateOld = DateTimeImmutable::createFromFormat(
                        'Y-m-d',
                        $txtChecks[$seachHeader]['date-last'],
                    );
                    $dateNew = DateTimeImmutable::createFromFormat('Y-m-d', $property['date-last']);

                    if ($dateOld !== false && $dateNew !== false && $dateOld < $dateNew) {
                        $txtChecks[$seachHeader]['date-last'] = $dateNew->format('Y-m-d');
                    }
                }

                continue;
            }

            if (array_key_exists('date-first', $property)) {
                if (is_string($property['date-first'])) {
                    $dateNew = DateTimeImmutable::createFromFormat('Y-m-d', $property['date-first']);

                    $property['date-first'] = $dateNew === false
                        ? (new DateTimeImmutable('now'))->format('Y-m-d')
                        : $dateNew->format('Y-m-d');
                } else {
                    $property['date-first'] = (new DateTimeImmutable('now'))->format('Y-m-d');
                }
            }

            if (array_key_exists('date-last', $property)) {
                if (is_string($property['date-last'])) {
                    $dateNew = DateTimeImmutable::createFromFormat('Y-m-d', $property['date-last']);

                    $property['date-last'] = $dateNew === false
                        ? (new DateTimeImmutable('now'))->format('Y-m-d')
                        : $dateNew->format('Y-m-d');
                } else {
                    $property['date-last'] = (new DateTimeImmutable('now'))->format('Y-m-d');
                }
            }

            $txtChecks[$seachHeader] = [
                'headers' => $property['headers'],
                'date-first' => $property['date-first'],
                'date-last' => $property['date-last'],
            ];
            ++$txtTotalCounter;
        }

        return $txtChecks;
    }
}
