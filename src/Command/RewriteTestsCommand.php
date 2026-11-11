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

use BrowscapHelper\Entity\TestProperties;
use BrowscapHelper\Entity\TestResult;
use BrowscapHelper\Helper\ExistingTestsLoader;
use BrowscapHelper\Helper\ExistingTestsRemover;
use BrowscapHelper\Helper\JsonNormalizer;
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\YamlFileSource;
use BrowscapHelper\Traits\FilterHeaderTrait;
use BrowserDetector\Detector;
use BrowserDetector\DetectorFactory;
use BrowserDetector\Iterator\FilterIterator;
use BrowserDetector\Loader\CompanyLoaderFactory;
use BrowserDetector\Loader\CompanyLoaderInterface;
use BrowserDetector\Version\Exception\NotNumericException;
use BrowserDetector\Version\NullVersion;
use BrowserDetector\Version\VersionBuilder;
use BrowserDetector\Version\VersionInterface;
use DateInterval;
use DateTimeImmutable;
use DeviceDetector\ClientHints;
use DeviceDetector\DeviceDetector;
use Ergebnis\Json\Exception\FileCanNotBeRead;
use Ergebnis\Json\Exception\FileDoesNotContainJson;
use Ergebnis\Json\Exception\FileDoesNotExist;
use Ergebnis\Json\Json;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentSize;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentStyle;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptions;
use Ergebnis\Json\Normalizer\Exception\InvalidNewLineString;
use Exception;
use JsonException;
use Override;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionProperty;
use RuntimeException;
use SplFileInfo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use Throwable;
use UaBrowserType\Type as ClientType;
use UaData\Company;
use UaDataMapper\InputMapper;
use UaDeviceType\Type as DeviceType;
use UaLoader\Exception\NotFoundException;
use UaNormalizer\Normalizer\NormalizerChain;
use UaNormalizer\NormalizerFactory;
use UaRequest\Header\HeaderInterface;
use UaRequest\RequestBuilder;
use UaResult\Bits\Bits;
use UaResult\Browser\Browser;
use UaResult\Device\Architecture;
use UaResult\Device\Device;
use UaResult\Device\Display;
use UaResult\Device\FormFactor;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;
use UnexpectedValueException;

use function array_any;
use function array_chunk;
use function array_filter;
use function array_key_exists;
use function array_map;
use function array_multisort;
use function array_walk;
use function assert;
use function date;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function in_array;
use function is_array;
use function is_scalar;
use function is_string;
use function json_decode;
use function json_encode;
use function ksort;
use function max;
use function mb_str_pad;
use function mb_strlen;
use function mb_strtolower;
use function mb_trim;
use function memory_get_peak_usage;
use function memory_get_usage;
use function memory_reset_peak_usage;
use function microtime;
use function min;
use function mkdir;
use function number_format;
use function preg_match;
use function preg_match_all;
use function sprintf;
use function str_contains;
use function str_ends_with;
use function str_replace;
use function str_starts_with;
use function uksort;
use function var_export;

use const JSON_PRESERVE_ZERO_FRACTION;
use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const JSON_UNESCAPED_SLASHES;
use const JSON_UNESCAPED_UNICODE;
use const PHP_EOL;
use const SORT_ASC;
use const SORT_DESC;
use const SORT_NUMERIC;
use const SORT_STRING;
use const STR_PAD_LEFT;

/** @phpcs:disable SlevomatCodingStandard.Classes.ClassLength.ClassTooLong */
#[AsCommand(name: 'rewrite-tests')]
final class RewriteTestsCommand extends Command
{
    use FilterHeaderTrait;

    private const int DETECT_LOWER_VERSION_ANDROID_IOS = 9;

    private const int DETECT_LOWER_VERSION_WINDOWS = 7;

    private const float DETECT_LOWER_VERSION_MACOS = 10.5;

    private const int COMPARE_MATOMO_LOWER_VERSION_ANDROID_IOS = 13;

    private const int COMPARE_MATOMO_LOWER_VERSION_WINDOWS = 10;

    private const int COMPARE_MATOMO_LOWER_VERSION_MACOS = 12;

    /**
     * last update: 2026-10-20
     */
    private const string COMPARE_DATE_START = '2019-01-01';

    private const string COMPARE_DATE_END = '2026-11-30';

    private const bool COMPARE_ALL = false;

    private const bool ADD_INTEGRATION_TESTS = false;

    /** @var array<string, int> */
    private array $tests = [];
    private NormalizerChain $normalizerChain;
    private CompanyLoaderInterface $companyLoader;
    private VersionBuilder $versionBuilder;
    private RequestBuilder $requestBuilder;
    private DeviceDetector $deviceDetector;
    private Detector $detector;

    /** @throws LogicException */
    public function __construct(
        private readonly ExistingTestsLoader $existingTestsLoader,
        private readonly ExistingTestsRemover $existingTestsRemover,
        private readonly JsonNormalizer $jsonNormalizer,
    ) {
        parent::__construct();

        $normalizerFactory     = new NormalizerFactory();
        $this->normalizerChain = $normalizerFactory->build();
        $this->versionBuilder  = new VersionBuilder();
    }

    /**
     * Configures the current command.
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    #[Override]
    protected function configure(): void
    {
        $this
            ->setName('rewrite-tests')
            ->setDescription('Rewrites existing tests');
    }

    /**
     * Executes the current command.
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
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws LogicException
     * @throws DirectoryNotFoundException
     * @throws InvalidNewLineString
     * @throws InvalidIndentStyle
     * @throws InvalidIndentSize
     * @throws InvalidJsonEncodeOptions
     * @throws InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws RuntimeException
     * @throws \LogicException
     * @throws Exception
     *
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     * @phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $startTimeExec = new DateTimeImmutable('now');

        $output->writeln(messages: 'init Detector ...', options: OutputInterface::VERBOSITY_NORMAL);

        $detectorCache = new class () implements CacheInterface {
            /**
             * Fetches a value from the cache.
             *
             * @param string $key     the unique key of this item in the cache
             * @param mixed  $default default value to return if the key does not exist
             *
             * @return mixed the value of the item from the cache, or $default in case of cache miss
             *
             * @throws void
             *
             * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            #[Override]
            public function get(string $key, mixed $default = null): mixed
            {
                return null;
            }

            /**
             * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
             *
             * @param string                $key   the key of the item to store
             * @param mixed                 $value the value of the item to store, must be serializable
             * @param DateInterval|int|null $ttl   Optional. The TTL value of this item. If no value is sent and
             *   the driver supports TTL then the library may set a default value
             *   for it or let the driver take care of that.
             *
             * @return bool true on success and false on failure
             *
             * @throws void
             *
             * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            #[Override]
            public function set(string $key, mixed $value, int | DateInterval | null $ttl = null): bool
            {
                return false;
            }

            /**
             * Delete an item from the cache by its unique key.
             *
             * @param string $key the unique cache key of the item to delete
             *
             * @return bool True if the item was successfully removed. False if there was an error.
             *
             * @throws void
             *
             * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            #[Override]
            public function delete(string $key): bool
            {
                return false;
            }

            /**
             * Wipes clean the entire cache's keys.
             *
             * @return bool true on success and false on failure
             *
             * @throws void
             */
            #[Override]
            public function clear(): bool
            {
                return false;
            }

            /**
             * Obtains multiple cache items by their unique keys.
             *
             * @param iterable<string> $keys    a list of keys that can obtained in a single operation
             * @param mixed            $default default value to return for keys that do not exist
             *
             * @return iterable<string, mixed> A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
             *
             * @throws void
             *
             * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            #[Override]
            public function getMultiple(iterable $keys, mixed $default = null): iterable
            {
                return [];
            }

            /**
             * Persists a set of key => value pairs in the cache, with an optional TTL.
             *
             * @param iterable<string, mixed> $values a list of key => value pairs for a multiple-set operation
             * @param DateInterval|int|null   $ttl    Optional. The TTL value of this item. If no value is sent and
             *      the driver supports TTL then the library may set a default value
             *      for it or let the driver take care of that.
             *
             * @return bool true on success and false on failure
             *
             * @throws void
             *
             * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            #[Override]
            public function setMultiple(iterable $values, int | DateInterval | null $ttl = null): bool
            {
                return false;
            }

            /**
             * Deletes multiple cache items in a single operation.
             *
             * @param iterable<string> $keys a list of string-based keys to be deleted
             *
             * @return bool True if the items were successfully removed. False if there was an error.
             *
             * @throws void
             *
             * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            #[Override]
            public function deleteMultiple(iterable $keys): bool
            {
                return false;
            }

            /**
             * Determines whether an item is present in the cache.
             *
             * NOTE: It is recommended that has() is only to be used for cache warming type purposes
             * and not to be used within your live applications operations for get/set, as this method
             * is subject to a race condition where your has() will return true and immediately after,
             * another script can remove it making the state of your app out of date.
             *
             * @param string $key the cache item key
             *
             * @throws void
             *
             * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            #[Override]
            public function has(string $key): bool
            {
                return false;
            }
        };

        $consoleLogger = new ConsoleLogger($output, [
            LogLevel::EMERGENCY => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ALERT => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::CRITICAL => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::ERROR => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::WARNING => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::NOTICE => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::INFO => OutputInterface::VERBOSITY_NORMAL,
            LogLevel::DEBUG => OutputInterface::VERBOSITY_NORMAL,
        ]);

        $companyLoaderFactory = new CompanyLoaderFactory(logger: $consoleLogger);
        $this->companyLoader  = $companyLoaderFactory();

        $detectorFactory = new DetectorFactory(
            psrCache: $detectorCache,
            logger: $consoleLogger,
            autoUpdate: false,
        );

        $this->detector = $detectorFactory();

        $output->writeln(messages: 'init Matomo ...', options: OutputInterface::VERBOSITY_NORMAL);

        $this->deviceDetector = new DeviceDetector();

        $output->writeln(
            messages: 'removing old existing files from vendor ...',
            options: OutputInterface::VERBOSITY_NORMAL,
        );

        $basePath                = 'vendor/mimmi20/browser-detector/';
        $detectorTargetDirectory = $basePath . 'data/tests/';
        $testSource              = 'tests';

        $this->existingTestsRemover->remove(output: $output, testSource: $detectorTargetDirectory);

        $sources = [new JsonFileSource($testSource), new YamlFileSource($testSource)];

        $output->writeln(
            messages: 'removing old existing files from .build ...',
            options: OutputInterface::VERBOSITY_NORMAL,
        );

        $this->existingTestsRemover->remove(output: $output, testSource: '.build');

        /** @var array<string, Result> $integrationTests */
        $integrationTests = [];

        $output->writeln(
            messages: 'reading already existing integration tests ...',
            options: OutputInterface::VERBOSITY_NORMAL,
        );

        $requestBuilder = (new ReflectionProperty($this->detector, 'requestBuilder'))->getValue(
            $this->detector,
        );
        assert($requestBuilder instanceof RequestBuilder);

        $this->requestBuilder = $requestBuilder;

        $output->writeln('reading path ' . $basePath . 'data/integration-tests/', OutputInterface::VERBOSITY_VERY_VERBOSE);

        try {
            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($basePath . 'data/integration-tests/'),
            );
            $files    = new FilterIterator($iterator, 'json');

            foreach ($files as $file) {
                assert($file instanceof SplFileInfo);
                $pathName = $file->getPathname();
                $filepath = str_replace('\\', '/', $pathName);
                assert(is_string($filepath));

                $output->write("\r" . 'reading file ' . $filepath, false,OutputInterface::VERBOSITY_VERY_VERBOSE);

                $content = file_get_contents($filepath);

                if (in_array($content, [false, '', PHP_EOL], strict: true)) {
                    $consoleLogger->error('no content');
                    continue;
                }

                try {
                    $data = json_decode($content, associative: true, flags: JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    $consoleLogger->error($e);

                    continue;
                }

                if (!is_array($data)) {
                    $consoleLogger->error('$data is not an Array');
                    continue;
                }

                foreach ($data as $test) {
                    if (!is_array($test)) {
                        $consoleLogger->error('$test is not an Array');
                        continue;
                    }

                    /** @var array<non-empty-string, non-empty-string> $headers */
                    $headers = $test['headers'] ?? null;

                    if (!is_array($headers)) {
                        $consoleLogger->error('$headers is not an Array');

                        continue;
                    }

                    /** @var array{headers?: array<non-empty-string, non-empty-string>, device?: array{architecture: string, deviceName: string|null, marketingName: string|null, manufacturer: string, brand: string, type: string, ismobile: bool, istv: bool, display: array{width: int|null, height: int|null, touch: bool|null, size: float|null}, dualOrientation: bool|null, simCount: int|null, bits: int}, client?: array{name: string|null, modus: string|null, version: string|null, manufacturer: string, type: string, isbot: bool, bits: int}, os?: array{name: string|null, marketingName: string|null, version: string|null, manufacturer: string, bits: int}, engine?: array{name: string|null, version: string|null, manufacturer: string}}|null $expected */
                    $expected = $test['expected'] ?? null;

                    if (!is_array($expected)) {
                        $consoleLogger->error('$expected is not an Array');

                        continue;
                    }

                    if (
                        array_any($expected, static function (mixed $value, mixed $key): bool {
                            return !is_string($key) || $key === '';
                        })
                    ) {
                        $consoleLogger->error('$expected is not an expected Array');

                        continue;
                    }

                    if (
                        array_any($headers, static function (mixed $v, mixed $k): bool {
                            if (!is_string($v) || $v === '') {
                                return true;
                            }

                            return !is_string($k) || $k === '';
                        })
                    ) {
                        $consoleLogger->error('$headers is not an expected Array');

                        continue;
                    }

                    $result = $this->buildResult(consoleLogger: $consoleLogger, data: $expected, headers: $headers);

                    if ($result instanceof Result) {
                        $integrationTests = $this->updateIntegrationTests(
                            result: $result,
                            integrationTests: $integrationTests,
                            addIntegrationTests: true,
                        );
                    }

                    // if (($headers['user-agent'] ?? '') === 'Lightpanda/1.0 internal-testing-crawler') {
                    //     $output->writeln('');
                    //     $output->writeln(sprintf('<options=bold,underscore>%s</>', $seachHeader));
                    //     $output->writeln('');
                    // }
                }
            }
        } catch (UnexpectedValueException $e) {
            $consoleLogger->error($e);
        }

        $this->existingTestsRemover->remove(
            output: $output,
            testSource: $basePath . 'data/integration-tests/',
        );

        $output->writeln(
            messages: 'reading already existing tests ...',
            options: OutputInterface::VERBOSITY_NORMAL,
        );

        $txtChecks                  = [];
        $resultChecks               = [];
        $checkedPlatforms           = [];
        $checkedEngines             = [];
        $notFoundFormfactors        = [];
        $messageLength              = 0;
        $counter                    = 0;
        $duplicates                 = 0;
        $errors                     = 0;
        $checked                    = 0;
        $skippedBeforeCheck         = 0;
        $skippedAfterCheck          = 0;
        $skippedInvalidData         = 0;
        $skippedVersion             = 0;
        $skippedHeaderCheck         = 0;
        $testCount                  = 0;
        $baseMessage                = 'checking Header ';
        $timeCheck                  = 0.0;
        $timeDetect                 = 0.0;
        $timeCompare                = 0.0;
        $timeRead                   = 0.0;
        $timeWrite                  = 0.0;
        $counterDifferentFromMatomo = 0;
        $counterComparedWithMatomo  = 0;

        foreach (
            $this->existingTestsLoader->getProperties(
                output: new NullOutput(),
                logger: $consoleLogger,
                sources: $sources,
                messageLength: $messageLength,
            ) as $testProperty
        ) {
            ++$counter;

            $today = date('Y-m-d');

            if (!$testProperty instanceof TestProperties) {
                ++$skippedBeforeCheck;

                if (!array_key_exists('skippedBeforeCheck', $resultChecks['general'])) {
                    $resultChecks['general']['skippedBeforeCheck'] = 0;
                }

                ++$resultChecks['general']['skippedBeforeCheck'];

                if (!array_key_exists('skippedBeforeCheck', $resultChecks[$today])) {
                    $resultChecks[$today]['skippedBeforeCheck'] = 0;
                }

                ++$resultChecks[$today]['skippedBeforeCheck'];

                continue;
            }

            $headers = $this->filterHeaders($output, $testProperty->getResult()->getHeaders());

            if (!array_key_exists('general', $resultChecks)) {
                $resultChecks['general'] = [];
            }

            if (!array_key_exists('all', $resultChecks['general'])) {
                $resultChecks['general']['all'] = 0;
            }

            ++$resultChecks['general']['all'];

            if (!array_key_exists($testProperty->getDateLast(), $resultChecks)) {
                $resultChecks[$testProperty->getDateLast()] = [];
            }

            if (!array_key_exists('all', $resultChecks[$testProperty->getDateLast()])) {
                $resultChecks[$testProperty->getDateLast()]['all'] = 0;
            }

            ++$resultChecks[$testProperty->getDateLast()]['all'];

            $actualTimeExec = new DateTimeImmutable('now');

            $interval = $actualTimeExec->diff($startTimeExec);

            $loopMessage = sprintf(
                '%s[%s] - [%s] - [%s] - [%s] - ',
                $baseMessage,
                mb_str_pad(
                    string: number_format(num: $counter, thousands_separator: '.'),
                    length: 14,
                    pad_type: STR_PAD_LEFT,
                ),
                $interval->format('%H:%I:%S.%F'),
                mb_str_pad(
                    string: number_format(
                        num: memory_get_usage(real_usage: true),
                        thousands_separator: '.',
                    ) . 'B',
                    length: 16,
                    pad_type: STR_PAD_LEFT,
                ),
                mb_str_pad(
                    string: number_format(
                        num: memory_get_peak_usage(real_usage: true),
                        thousands_separator: '.',
                    ) . 'B',
                    length: 16,
                    pad_type: STR_PAD_LEFT,
                ),
            );

            memory_reset_peak_usage();

            $message = $loopMessage . 'check';
            $diff    = $this->messageLength($output, $message, $messageLength);

            $output->write(
                messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
            $output->writeln(
                sprintf(' <bg=red>%d</>', $messageLength),
                OutputInterface::VERBOSITY_DEBUG,
            );

            $startTime = microtime(as_float: true);

            ksort($headers);

            $seachHeader = Yaml::dump($headers);

            $accepted = array_key_exists($seachHeader, $integrationTests)
                || $this->acceptByDate($testProperty, $output, $loopMessage);

            $timeCheck += microtime(as_float: true) - $startTime;

            // if (($headers['user-agent'] ?? '') === 'Lightpanda/1.0 internal-testing-crawler') {
            //     $output->writeln('');
            //     $output->writeln(sprintf('<options=bold,underscore>%s</>', var_export($headers, true)));
            //     $output->writeln(sprintf('<options=bold,underscore>%s</>', var_export(array_key_exists($seachHeader, $integrationTests), true)));
            //     $output->writeln(sprintf('<options=bold,underscore>$accepted: %s</>', var_export($accepted, true)));
            //     $output->writeln(sprintf('<options=bold,underscore>%s</>', $seachHeader));
            //     $output->writeln('');
            // } else {
            //     continue;
            // }

            if (!$accepted) {
                ++$skippedBeforeCheck;

                if (!array_key_exists('skippedBeforeCheck', $resultChecks['general'])) {
                    $resultChecks['general']['skippedBeforeCheck'] = 0;
                }

                ++$resultChecks['general']['skippedBeforeCheck'];

                if (
                    !array_key_exists(
                        'skippedBeforeCheck',
                        $resultChecks[$testProperty->getDateLast()],
                    )
                ) {
                    $resultChecks[$testProperty->getDateLast()]['skippedBeforeCheck'] = 0;
                }

                ++$resultChecks[$testProperty->getDateLast()]['skippedBeforeCheck'];

                continue;
            }

            $normalizedHeaders = $this->normalizeHeaders($headers);

            if (array_key_exists('sec-ch-ua-form-factors', $headers)) {
                $matches = [];

                if (
                    preg_match_all(
                        '~["\']([a-z]+)["\']~i',
                        $headers['sec-ch-ua-form-factors'],
                        $matches,
                    )
                ) {
                    foreach ($matches[1] as $factor) {
                        $detectedFactor = FormFactor::tryFrom($factor);

                        if ($detectedFactor !== null) {
                            continue;
                        }

                        if (!array_key_exists($factor, $notFoundFormfactors)) {
                            $notFoundFormfactors[$factor] = 0;
                        }

                        ++$notFoundFormfactors[$factor];

                        $output->writeln(
                            messages: "\n" . mb_str_pad(
                                string: sprintf(
                                    'The FormFactor "<fg=blue>%s</>", was not found in the Enum. Please add it',
                                    $factor,
                                ),
                                length: $messageLength,
                            ),
                            options: OutputInterface::VERBOSITY_NORMAL,
                        );
                    }
                }
            }

            if (
                array_key_exists('x-requested-with', $headers)
                && array_key_exists('http-x-requested-with', $headers)
            ) {
                $message = $loopMessage . '<error>"x-requested-with" header is available twice</error>';
                $diff    = $this->messageLength($output, $message, $messageLength);

                $output->writeln(
                    messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );
            }

            $hasInvalidHeaders = $this->hasInvalidHeaders($headers)
                || $this->hasInvalidHeaders($normalizedHeaders);

            if ($hasInvalidHeaders) {
                ++$skippedInvalidData;

                if (!array_key_exists('skippedInvalidData', $resultChecks['general'])) {
                    $resultChecks['general']['skippedInvalidData'] = 0;
                }

                ++$resultChecks['general']['skippedInvalidData'];

                if (
                    !array_key_exists(
                        'skippedInvalidData',
                        $resultChecks[$testProperty->getDateLast()],
                    )
                ) {
                    $resultChecks[$testProperty->getDateLast()]['skippedInvalidData'] = 0;
                }

                ++$resultChecks[$testProperty->getDateLast()]['skippedInvalidData'];

                continue;
            }

            if (array_key_exists($seachHeader, $txtChecks)) {
                ++$skippedBeforeCheck;

                if (!array_key_exists('skippedBeforeCheck', $resultChecks['general'])) {
                    $resultChecks['general']['skippedBeforeCheck'] = 0;
                }

                ++$resultChecks['general']['skippedBeforeCheck'];

                if (
                    !array_key_exists(
                        'skippedBeforeCheck',
                        $resultChecks[$testProperty->getDateLast()],
                    )
                ) {
                    $resultChecks[$testProperty->getDateLast()]['skippedBeforeCheck'] = 0;
                }

                ++$resultChecks[$testProperty->getDateLast()]['skippedBeforeCheck'];

                continue;
            }

            ++$checked;

            if (!array_key_exists('checked', $resultChecks['general'])) {
                $resultChecks['general']['checked'] = 0;
            }

            ++$resultChecks['general']['checked'];

            if (!array_key_exists('checked', $resultChecks[$testProperty->getDateLast()])) {
                $resultChecks[$testProperty->getDateLast()]['checked'] = 0;
            }

            ++$resultChecks[$testProperty->getDateLast()]['checked'];

            $txtChecks[$seachHeader] = true;

            if (!array_key_exists('skippedHeaderCheck', $resultChecks['general'])) {
                $resultChecks['general']['skippedHeaderCheck'] = 0;
            }

            // $resultChecks['general']['skippedHeaderCheck']++;

            if (!array_key_exists('skippedHeaderCheck', $resultChecks[$testProperty->getDateLast()])) {
                $resultChecks[$testProperty->getDateLast()]['skippedHeaderCheck'] = 0;
            }

            // $resultChecks[$testProperty->getDateLast()]['skippedHeaderCheck']++;

            // if (
            //    !array_key_exists('x-requested-with', $headers)
            //    && !array_key_exists('http-x-requested-with', $headers)
            // ) {
            //    ++$skippedHeaderCheck;
            //
            //    return;
            // }
            //
            // if (
            //    !array_key_exists('sec-ch-ua-platform', $headers)
            // ) {
            //    ++$skippedHeaderCheck;
            //
            //    return;
            // }
            //
            // if (
            //    !array_key_exists('sec-ch-ua-model', $headers)
            // ) {
            //    ++$skippedHeaderCheck;
            //
            //    return;
            // }
            //
            // if (
            //    !array_key_exists('x-puffin-ua', $headers)
            // ) {
            //    ++$skippedHeaderCheck;
            //
            //    return;
            // }

            $message = $loopMessage . 'redetect';
            $diff    = $this->messageLength($output, $message, $messageLength);

            $output->write(
                messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
            $output->writeln(
                sprintf(' <bg=red>%d</>', $messageLength),
                OutputInterface::VERBOSITY_DEBUG,
            );

            $startTime = microtime(as_float: true);

            $testResult = $this->handleTest(
                output: $output,
                consoleLogger: $consoleLogger,
                headers: $headers,
                parentMessage: $message,
                messageLength: $messageLength,
            );

            $timeDetect += microtime(as_float: true) - $startTime;

            $result = $testResult->getResult();

            if ($testResult->getStatus() === TestResult::STATUS_SKIPPED) {
                ++$skippedAfterCheck;

                if (!array_key_exists('skippedAfterCheck', $resultChecks['general'])) {
                    $resultChecks['general']['skippedAfterCheck'] = 0;
                }

                ++$resultChecks['general']['skippedAfterCheck'];

                if (
                    !array_key_exists(
                        'skippedAfterCheck',
                        $resultChecks[$testProperty->getDateLast()],
                    )
                ) {
                    $resultChecks[$testProperty->getDateLast()]['skippedAfterCheck'] = 0;
                }

                ++$resultChecks[$testProperty->getDateLast()]['skippedAfterCheck'];

                // if (($headers['user-agent'] ?? '') === 'Lightpanda/1.0 internal-testing-crawler') {
                //     $output->writeln('');
                //     $output->writeln('<options=bold,blink>1</>');
                //     $output->writeln(sprintf('<options=bold,underscore>%s</>', var_export($result, true)));
                //     $output->writeln('');
                // }

                continue;
            }

            if ($testResult->getStatus() === TestResult::STATUS_DUPLICATE) {
                ++$duplicates;

                if (!array_key_exists('duplicates', $resultChecks['general'])) {
                    $resultChecks['general']['duplicates'] = 0;
                }

                ++$resultChecks['general']['duplicates'];

                if (!array_key_exists('duplicates', $resultChecks[$testProperty->getDateLast()])) {
                    $resultChecks[$testProperty->getDateLast()]['duplicates'] = 0;
                }

                ++$resultChecks[$testProperty->getDateLast()]['duplicates'];

                if ($result instanceof Result) {
                    try {
                        $integrationTests = $this->updateIntegrationTests(
                            result: $result,
                            integrationTests: $integrationTests,
                            addIntegrationTests: self::ADD_INTEGRATION_TESTS,
                        );
                    } catch (UnexpectedValueException | NotNumericException $e) {
                        $consoleLogger->error($e);
                    }
                }

                // if (($headers['user-agent'] ?? '') === 'Lightpanda/1.0 internal-testing-crawler') {
                //     $output->writeln('');
                //     $output->writeln('<options=bold,blink>2</>');
                //     $output->writeln(sprintf('<options=bold,underscore>%s</>', var_export($result, true)));
                //     $output->writeln('');
                // }

                continue;
            }

            if ($testResult->getStatus() === TestResult::STATUS_ERROR || !$result instanceof Result) {
                ++$errors;

                if (!array_key_exists('errors', $resultChecks['general'])) {
                    $resultChecks['general']['errors'] = 0;
                }

                ++$resultChecks['general']['errors'];

                if (!array_key_exists('errors', $resultChecks[$testProperty->getDateLast()])) {
                    $resultChecks[$testProperty->getDateLast()]['errors'] = 0;
                }

                ++$resultChecks[$testProperty->getDateLast()]['errors'];

                // if (($headers['user-agent'] ?? '') === 'Lightpanda/1.0 internal-testing-crawler') {
                //     $output->writeln('');
                //     $output->writeln('<options=bold,blink>3</>');
                //     $output->writeln(sprintf('<options=bold,underscore>%s</>', var_export($result, true)));
                //     $output->writeln('');
                // }

                continue;
            }

            $headers = $testResult->getHeaders();

            $osName     = mb_strtolower($result->getOs()->getName() ?? '');
            $engineName = mb_strtolower($result->getEngine()->getName() ?? '');
            $versionOs  = $result->getOs()->getVersion();

            try {
                $osVersion = $versionOs->getVersion(VersionInterface::IGNORE_MICRO) ?? '-';
            } catch (UnexpectedValueException) {
                $osVersion = 'e';
            }

            $versionEngine = $result->getEngine()->getVersion();

            try {
                $engineVersion = $versionEngine->getVersion(VersionInterface::IGNORE_MICRO) ?? '-';
            } catch (UnexpectedValueException) {
                $engineVersion = 'e';
            }

            $isSkipped = $this->isVersionskipped(
                versionOs: $versionOs,
                osName: $osName,
                testProperties: $testProperty,
                resultChecks: $resultChecks,
                skippedVersion: $skippedVersion,
            );

            if ($isSkipped) {
                // if (($headers['user-agent'] ?? '') === 'Lightpanda/1.0 internal-testing-crawler') {
                //     $output->writeln('');
                //     $output->writeln('<options=bold,blink>6</>');
                //     $output->writeln(sprintf('<options=bold,underscore>%s</>', var_export($result, true)));
                //     $output->writeln('');
                // }

                continue;
            }

            $compareWithMatomo = self::COMPARE_ALL;

            if (!$compareWithMatomo) {
                $compareWithMatomo = $this->compareWithMatomo(
                    osName: $osName,
                    osVersion: $osVersion,
                    engineName: $engineName,
                    engineVersion: $engineVersion,
                    checkedPlatforms: $checkedPlatforms,
                    checkedEngines: $checkedEngines,
                );
            }

            if ($compareWithMatomo) {
                $startTime = microtime(as_float: true);

                try {
                    $this->compareDeviceWithMapper(
                        output: $output,
                        consoleLogger: $consoleLogger,
                        testProperties: $testProperty,
                        testResult: $testResult,
                        headers: $headers,
                        loopMessage: $loopMessage,
                        messageLength: $messageLength,
                        counterDifferentFromMatomo: $counterDifferentFromMatomo,
                        resultChecks: $resultChecks,
                    );
                } catch (UnexpectedValueException $e) {
                    ++$errors;

                    if (!array_key_exists('errors', $resultChecks['general'])) {
                        $resultChecks['general']['errors'] = 0;
                    }

                    ++$resultChecks['general']['errors'];

                    if (!array_key_exists('errors', $resultChecks[$testProperty->getDateLast()])) {
                        $resultChecks[$testProperty->getDateLast()]['errors'] = 0;
                    }

                    ++$resultChecks[$testProperty->getDateLast()]['errors'];

                    $exception = new Exception('An error occured while comparing a result', 0, $e);

                    $addMessage = sprintf('<error>%s</error>', (string) $exception);

                    $message = $loopMessage . $addMessage;

                    $output->writeln(
                        messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                        options: OutputInterface::VERBOSITY_NORMAL,
                    );

                    // if (($headers['user-agent'] ?? '') === 'Lightpanda/1.0 internal-testing-crawler') {
                    //     $output->writeln('');
                    //     $output->writeln('<options=bold,blink>7</>');
                    //     $output->writeln(sprintf('<options=bold,underscore>%s</>', var_export($result, true)));
                    //     $output->writeln('');
                    // }

                    continue;
                }

                $timeCompare += microtime(as_float: true) - $startTime;

                ++$counterComparedWithMatomo;

                if (!array_key_exists('comparedWithMatomo', $resultChecks['general'])) {
                    $resultChecks['general']['comparedWithMatomo'] = 0;
                }

                ++$resultChecks['general']['comparedWithMatomo'];

                if (
                    !array_key_exists(
                        'comparedWithMatomo',
                        $resultChecks[$testProperty->getDateLast()],
                    )
                ) {
                    $resultChecks[$testProperty->getDateLast()]['comparedWithMatomo'] = 0;
                }

                ++$resultChecks[$testProperty->getDateLast()]['comparedWithMatomo'];
            }

            $deviceManufaturer = mb_strtolower(
                str_replace(
                    [' ', 'ü'],
                    ['-', 'ue'],
                    $result->getDevice()->getManufacturer()->getBrandname() ?? '',
                ),
            );
            $deviceManufaturer = $deviceManufaturer === '' ? 'unknown' : str_replace(
                ['.', ' '],
                ['', '-'],
                $deviceManufaturer,
            );

            $clientManufaturer = mb_strtolower(
                str_replace(
                    [' ', 'ü'],
                    ['-', 'ue'],
                    $result->getBrowser()->getManufacturer()->getBrandname() ?? '',
                ),
            );
            $clientManufaturer = $clientManufaturer === '' ? 'unknown' : str_replace(
                ['.', ' '],
                ['', '-'],
                $clientManufaturer,
            );

            $deviceType = mb_strtolower($result->getDevice()->getType()->getType());
            $clientType = mb_strtolower($result->getBrowser()->getType()->getType());

            if (!file_exists(sprintf('.build/%s', $deviceManufaturer))) {
                mkdir(sprintf('.build/%s', $deviceManufaturer));
            }

            if (!file_exists(sprintf('.build/%s/%s', $deviceManufaturer, $deviceType))) {
                mkdir(sprintf('.build/%s/%s', $deviceManufaturer, $deviceType));
            }

            if (
                !file_exists(
                    sprintf('.build/%s/%s/%s', $deviceManufaturer, $deviceType, $clientManufaturer),
                )
            ) {
                mkdir(sprintf('.build/%s/%s/%s', $deviceManufaturer, $deviceType, $clientManufaturer));
            }

            $file = sprintf(
                '.build/%s/%s/%s/%s.json',
                $deviceManufaturer,
                $deviceType,
                $clientManufaturer,
                $clientType,
            );

            $saved = $this->saveResult(
                output: $output,
                result: $result,
                file: $file,
                loopMessage: $loopMessage,
                errors: $errors,
                messageLength: $messageLength,
                timeRead: $timeRead,
                timeWrite: $timeWrite,
            );

            if ($saved === false) {
                ++$errors;

                $addMessage = sprintf('<error>An error occured while saving file %s</error>', $file);
                $message    = $loopMessage . $addMessage;
                $diff       = $this->messageLength($output, $message, $messageLength);

                $output->writeln(
                    messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );

                // if (($headers['user-agent'] ?? '') === 'Lightpanda/1.0 internal-testing-crawler') {
                //     $output->writeln('');
                //     $output->writeln('<options=bold,blink>8</>');
                //     $output->writeln(sprintf('<options=bold,underscore>%s</>', var_export($result, true)));
                //     $output->writeln('');
                // }

                continue;
            }

            $addMessage = sprintf('write to temporary file %s - <info>done</info>', $file);

            unset($file);

            $message = $loopMessage . $addMessage;
            $diff    = $this->messageLength($output, $message, $messageLength);

            $output->write(
                messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
            $output->writeln(
                sprintf(' <bg=red>%d</>', $messageLength),
                OutputInterface::VERBOSITY_DEBUG,
            );

            ++$testCount;

            if (!array_key_exists('test', $resultChecks['general'])) {
                $resultChecks['general']['test'] = 0;
            }

            ++$resultChecks['general']['test'];

            if (!array_key_exists('test', $resultChecks[$testProperty->getDateLast()])) {
                $resultChecks[$testProperty->getDateLast()]['test'] = 0;
            }

            ++$resultChecks[$testProperty->getDateLast()]['test'];

            try {
                $integrationTests = $this->updateIntegrationTests(
                    result: $result,
                    integrationTests: $integrationTests,
                    addIntegrationTests: self::ADD_INTEGRATION_TESTS,
                );
            } catch (UnexpectedValueException | NotNumericException $e) {
                $consoleLogger->error($e);
            }

            // if (($headers['user-agent'] ?? '') === 'Lightpanda/1.0 internal-testing-crawler') {
            //     $output->writeln('');
            //     $output->writeln('<options=bold,blink>9</>');
            //     $output->writeln(sprintf('<options=bold,underscore>%s</>', var_export($result, true)));
            //     $output->writeln('');
            // }

            //            if ($counterDifferentFromMatomo >= 10) {
            //                exit;
            //            }
        }

        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);

        $this->jsonNormalizer->init($output);

        $messageLength = 0;
        $baseMessage   = 're-write test files in directory ';

        $fileFinder = new Finder();
        $fileFinder->notName('*.gitkeep');
        $fileFinder->ignoreDotFiles(ignoreDotFiles: true);
        $fileFinder->ignoreVCS(ignoreVCS: true);
        $fileFinder->sortByName();
        $fileFinder->ignoreUnreadableDirs();
        $fileFinder->files();
        $fileFinder->in('.build');

        foreach ($fileFinder as $file) {
            $matches = [];

            if (
                !preg_match(
                    '/\.build\\\(?P<deviceManufaturer>[^\\\]+)\\\(?P<deviceType>[^\\\]+)\\\(?P<clientManufaturer>[^\\\]+)\\\(?P<clientType>[^\\\]+)\.json/',
                    $file->getPathname(),
                    $matches,
                )
            ) {
                ++$errors;

                $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
                $consoleLogger->error(
                    sprintf(
                        'the path "%s" does not match required structure',
                        $file->getPathname(),
                    ),
                );

                return 1;
            }

            try {
                /** @var non-empty-list<array{headers?: array<non-empty-string, non-empty-string>, device?: array{architecture: string, deviceName: string|null, marketingName: string|null, manufacturer: string, brand: string, type: string, ismobile: bool, istv: bool, display: array{width: int|null, height: int|null, touch: bool|null, size: float|null}, dualOrientation: bool|null, simCount: int|null, bits: int}, client?: array{name: string|null, modus: string|null, version: string|null, manufacturer: string, type: string, isbot: bool, bits: int}, os?: array{name: string|null, marketingName: string|null, version: string|null, manufacturer: string, bits: int}, engine?: array{name: string|null, version: string|null, manufacturer: string}, date-first?: string, date-last?: string}> $data */
                $data = json_decode(
                    $file->getContents(),
                    associative: true,
                    flags: JSON_THROW_ON_ERROR,
                );
            } catch (JsonException $e) {
                ++$errors;

                $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
                $consoleLogger->error(
                    new Exception(
                        'An error occured while encoding a resultset',
                        0,
                        $e,
                    ),
                );

                return 1;
            }

            /** @var non-empty-list<Result> $tests */
            $tests = array_filter(
                array_map(
                    function (mixed $xpart) use ($consoleLogger): Result | null {
                        if (!is_array($xpart)) {
                            $consoleLogger->error('$part is not an Array');

                            return null;
                        }

                        $headers = $xpart['headers'] ?? null;

                        if (!is_array($headers)) {
                            $consoleLogger->error('$headers is not an Array');

                            return null;
                        }

                        return $this->buildResult(
                            consoleLogger: $consoleLogger,
                            data: $xpart,
                            headers: $headers,
                        );
                    },
                    $data,
                ),
                static fn (mixed $xpart): bool => $xpart !== null,
            );

            array_walk(
                $tests,
                function (Result $result) use (&$integrationTests, $output, $consoleLogger): void {
                    try {
                        $integrationTests = $this->updateIntegrationTests(
                            result: $result,
                            integrationTests: $integrationTests,
                            addIntegrationTests: self::ADD_INTEGRATION_TESTS,
                        );
                    } catch (UnexpectedValueException | NotNumericException $e) {
                        $consoleLogger->error($e);
                    }
                },
            );

            foreach (array_chunk($tests, 100) as $number => $results) {
                try {
                    $this->rewriteFile(
                        output: $output,
                        basePath: $basePath,
                        baseMessage: $baseMessage,
                        matches: $matches,
                        results: $results,
                        number: $number,
                        messageLength: $messageLength,
                    );
                } catch (RuntimeException $e) {
                    ++$errors;
                    $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
                    $output->writeln(
                        messages: '<error>' . $e . '</error>',
                        options: OutputInterface::VERBOSITY_NORMAL,
                    );
                }
            }
        }

        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(
            messages: 'rewrite integration tests ...',
            options: OutputInterface::VERBOSITY_NORMAL,
        );

        foreach (array_chunk($integrationTests, 100) as $number => $parts) {
            try {
                $normalized = $this->jsonNormalizer->normalize(
                    output: $output,
                    testCases: $parts,
                    message: 'normalizing',
                    messageLength: $messageLength,
                );
            } catch (RuntimeException $e) {
                $output->writeln(messages: '', options: OutputInterface::VERBOSITY_VERBOSE);
                $output->writeln(
                    messages: '<error>' . $e . '</error>',
                    options: OutputInterface::VERBOSITY_NORMAL,
                );

                continue;
            }

            $path  = $basePath;
            $path .= sprintf('data/integration-tests/tests-%07d.json', $number);

            if ($normalized === null) {
                throw new RuntimeException(sprintf('file "%s" contains invalid json', $path));
            }

            $success = @file_put_contents($path, $normalized);

            if ($success !== false) {
                continue;
            }

            ++$errors;
            $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
            $consoleLogger->error(
                sprintf(
                    'An error occured while writing file %s',
                    $path,
                ),
            );
        }

        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(messages: 'rewrite tests ...', options: OutputInterface::VERBOSITY_NORMAL);

        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(
            messages: sprintf(
                'time checking:              %s sec',
                mb_str_pad(number_format($timeCheck, 3, ',', '.'), 12, ' ', STR_PAD_LEFT),
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(
            messages: sprintf(
                'time detecting:             %s sec',
                mb_str_pad(number_format($timeDetect, 3, ',', '.'), 12, ' ', STR_PAD_LEFT),
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(
            messages: sprintf(
                'time detection with Matomo: %s sec',
                mb_str_pad(number_format($timeCompare, 3, ',', '.'), 12, ' ', STR_PAD_LEFT),
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(
            messages: sprintf(
                'time reading cache:         %s sec',
                mb_str_pad(number_format($timeRead, 3, ',', '.'), 12, ' ', STR_PAD_LEFT),
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(
            messages: sprintf(
                'time writing cache:         %s sec',
                mb_str_pad(number_format($timeWrite, 3, ',', '.'), 12, ' ', STR_PAD_LEFT),
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );

        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);

        if ($notFoundFormfactors !== []) {
            $table = new Table($output);
            $table->setHeaders(['FormFactor, not found in Enum', 'Counter']);
            $table->setRows([]);

            $cy = [];
            $cx = [];

            foreach ($notFoundFormfactors as $factor => $factorCounter) {
                $cy[$factor] = $factorCounter;
                $cx[$factor] = $factor;
            }

            array_multisort(
                $cy,
                SORT_DESC,
                SORT_NUMERIC,
                $cx,
                SORT_ASC,
                SORT_STRING,
                $notFoundFormfactors,
            );

            foreach ($notFoundFormfactors as $factor => $factorCounter) {
                $table->addRow(
                    ['<info>' . $factor . '</info>', '<error>' . $factorCounter . '</error>'],
                );
            }

            $table->render();

            $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        }

        if ($checkedPlatforms !== []) {
            $cx = [];
            $cy = [];

            foreach ($checkedPlatforms as $os => $versions) {
                $c = 0;
                $x = [];
                $y = [];

                foreach ($versions as $version => $counterVersions) {
                    $c          += $counterVersions['count'];
                    $x[$version] = $counterVersions['count'];
                    $y[$version] = $version;
                }

                array_multisort($x, SORT_DESC, SORT_NUMERIC, $y, SORT_DESC, SORT_NUMERIC, $versions);

                $cx[$os]               = $c;
                $cy[$os]               = $os;
                $checkedPlatforms[$os] = $versions;
            }

            array_multisort(
                $cx,
                SORT_DESC,
                SORT_NUMERIC,
                $cy,
                SORT_ASC,
                SORT_STRING,
                $checkedPlatforms,
            );

            $table = new Table($output);
            $table->setHeaders(['OS', 'Version', 'Tests']);
            $table->setRows([]);

            foreach ($checkedPlatforms as $os => $versions) {
                $c = 0;

                foreach ($versions as $version => $counterVersions) {
                    $count     = $counterVersions['count'];
                    $checkmark = $counterVersions['checked'] ?? false
                        ? ' <fg=green>+</>'
                        : ' <fg=red>-</>';
                    $table->addRow(
                        [$os, $version, $count . $checkmark],
                    );

                    $c += $count;
                }

                $table->addRow(
                    [
                        '<info>' . $os . '</info>',
                        '<info>summary</info>',
                        '<error>' . $c . '</error>',
                    ],
                );
                $table->addRow(new TableSeparator());
            }

            $table->render();

            $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        }

        if ($checkedEngines !== []) {
            $cx = [];
            $cy = [];

            foreach ($checkedEngines as $engine => $versions) {
                $c = 0;
                $x = [];
                $y = [];

                foreach ($versions as $version => $counterVersions) {
                    $c          += $counterVersions['count'];
                    $x[$version] = $counterVersions['count'];
                    $y[$version] = $version;
                }

                array_multisort($x, SORT_DESC, SORT_NUMERIC, $y, SORT_DESC, SORT_NUMERIC, $versions);

                $cx[$engine]             = $c;
                $cy[$engine]             = $engine;
                $checkedEngines[$engine] = $versions;
            }

            array_multisort($cx, SORT_DESC, SORT_NUMERIC, $cy, SORT_ASC, SORT_STRING, $checkedEngines);

            $table = new Table($output);
            $table->setHeaders(['Engine', 'Version', 'Tests']);
            $table->setRows([]);

            foreach ($checkedEngines as $engine => $versions) {
                $c = 0;

                foreach ($versions as $version => $counterVersions) {
                    $count     = $counterVersions['count'];
                    $checkmark = $counterVersions['checked'] ?? false
                        ? ' <fg=green>+</>'
                        : ' <fg=red>-</>';
                    $table->addRow(
                        [$engine, $version, $count . $checkmark],
                    );

                    $c += $count;
                }

                $table->addRow(
                    [
                        '<info>' . $engine . '</info>',
                        '<info>summary</info>',
                        '<error>' . $c . '</error>',
                    ],
                );
                $table->addRow(new TableSeparator());
            }

            $table->render();

            $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        }

        if ($resultChecks !== []) {
            $generalData = $resultChecks['general'];
            unset($resultChecks['general']);

            uksort(
                $resultChecks,
                static function (string $a, string $b): int {
                    $da = new DateTimeImmutable($a);
                    $db = new DateTimeImmutable($b);

                    return $da <=> $db;
                },
            );

            $table = new Table($output);
            $table->setHeaders(
                [
                    'Date',
                    'useragents processed',
                    'skipped before checks',
                    'skipped invalid',
                    'useragents checked',
                    'skipped after checks',
                    'skipped after version check',
                    'skipped after header check',
                    'errors',
                    'duplicates',
                    'tests written',
                    'compared with Matomo',
                    'different from Matomo',
                ],
            );
            $table->setRows(
                [
                    [
                        '',
                        number_format(num: $generalData['all'] ?? 0, thousands_separator: '.'),
                        number_format(
                            num: $generalData['skippedBeforeCheck'] ?? 0,
                            thousands_separator: '.',
                        ),
                        number_format(
                            num: $generalData['skippedInvalidData'] ?? 0,
                            thousands_separator: '.',
                        ),
                        number_format(num: $generalData['checked'] ?? 0, thousands_separator: '.'),
                        number_format(
                            num: $generalData['skippedAfterCheck'] ?? 0,
                            thousands_separator: '.',
                        ),
                        number_format(
                            num: $generalData['skippedVersion'] ?? 0,
                            thousands_separator: '.',
                        ),
                        number_format(
                            num: $generalData['skippedHeaderCheck'] ?? 0,
                            thousands_separator: '.',
                        ),
                        number_format(num: $generalData['errors'] ?? 0, thousands_separator: '.'),
                        number_format(num: $generalData['duplicates'] ?? 0, thousands_separator: '.'),
                        number_format(num: $generalData['test'] ?? 0, thousands_separator: '.'),
                        number_format(
                            num: $generalData['comparedWithMatomo'] ?? 0,
                            thousands_separator: '.',
                        ),
                        number_format(
                            num: $generalData['differentFromMatomo'] ?? 0,
                            thousands_separator: '.',
                        ),
                    ],
                ],
            );

            foreach ($resultChecks as $date => $data) {
                $differentFromMatomo = $data['differentFromMatomo'] ?? 0;
                $comparedWithMatomo  = $data['comparedWithMatomo'] ?? 0;

                /* @phpstan-ignore booleanNot.alwaysTrue, booleanOr.alwaysTrue */
                if ((!self::COMPARE_ALL || $comparedWithMatomo === 0) && $differentFromMatomo === 0) {
                    continue;
                }

                $table->addRow(
                    [
                        $date,
                        number_format(num: $data['all'] ?? 0, thousands_separator: '.'),
                        number_format(num: $data['skippedBeforeCheck'] ?? 0, thousands_separator: '.'),
                        number_format(num: $data['skippedInvalidData'] ?? 0, thousands_separator: '.'),
                        number_format(num: $data['checked'] ?? 0, thousands_separator: '.'),
                        number_format(num: $data['skippedAfterCheck'] ?? 0, thousands_separator: '.'),
                        number_format(num: $data['skippedVersion'] ?? 0, thousands_separator: '.'),
                        number_format(num: $data['skippedHeaderCheck'] ?? 0, thousands_separator: '.'),
                        number_format(num: $data['errors'] ?? 0, thousands_separator: '.'),
                        number_format(num: $data['duplicates'] ?? 0, thousands_separator: '.'),
                        number_format(num: $data['test'] ?? 0, thousands_separator: '.'),
                        number_format(num: $comparedWithMatomo, thousands_separator: '.'),
                        sprintf(
                            '<fg=%s>%s</>',
                            $differentFromMatomo === 0 ? 'green' : 'red',
                            number_format(num: $differentFromMatomo, thousands_separator: '.'),
                        ),
                    ],
                );
            }

            $table->render();

            $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        }

        $table = new Table($output);
        $table->setHeaders(['', 'Tests']);
        $table->setRows(
            [
                [
                    'useragents processed',
                    mb_str_pad(
                        number_format(num: $counter, thousands_separator: '.'),
                        12,
                        ' ',
                        STR_PAD_LEFT,
                    ),
                ],
                [
                    'skipped before checks',
                    mb_str_pad(
                        number_format(num: $skippedBeforeCheck, thousands_separator: '.'),
                        12,
                        ' ',
                        STR_PAD_LEFT,
                    ),
                ],
                [
                    'skipped invalid',
                    mb_str_pad(
                        number_format(num: $skippedInvalidData, thousands_separator: '.'),
                        12,
                        ' ',
                        STR_PAD_LEFT,
                    ),
                ],
                [
                    'useragents checked',
                    mb_str_pad(
                        number_format(num: $checked, thousands_separator: '.'),
                        12,
                        ' ',
                        STR_PAD_LEFT,
                    ),
                ],
            ],
        );
        $table->addRow(new TableSeparator());
        $table->addRows(
            [
                [
                    'skipped after checks',
                    mb_str_pad(
                        number_format(num: $skippedAfterCheck, thousands_separator: '.'),
                        12,
                        ' ',
                        STR_PAD_LEFT,
                    ),
                ],
                [
                    'skipped after version check',
                    mb_str_pad(
                        number_format(num: $skippedVersion, thousands_separator: '.'),
                        12,
                        ' ',
                        STR_PAD_LEFT,
                    ),
                ],
                [
                    'skipped after header check',
                    mb_str_pad(
                        number_format(num: $skippedHeaderCheck, thousands_separator: '.'),
                        12,
                        ' ',
                        STR_PAD_LEFT,
                    ),
                ],
                [
                    'errors',
                    mb_str_pad(
                        number_format(num: $errors, thousands_separator: '.'),
                        12,
                        ' ',
                        STR_PAD_LEFT,
                    ),
                ],
                [
                    'duplicates',
                    mb_str_pad(
                        number_format(num: $duplicates, thousands_separator: '.'),
                        12,
                        ' ',
                        STR_PAD_LEFT,
                    ),
                ],
                [
                    'tests written',
                    mb_str_pad(
                        number_format(num: $testCount, thousands_separator: '.'),
                        12,
                        ' ',
                        STR_PAD_LEFT,
                    ),
                ],
            ],
        );
        $table->addRow(new TableSeparator());
        $table->addRows(
            [
                [
                    'compared with Matomo',
                    mb_str_pad(
                        number_format(num: $counterComparedWithMatomo, thousands_separator: '.'),
                        12,
                        ' ',
                        STR_PAD_LEFT,
                    ),
                ],
                [
                    'different from Matomo',
                    mb_str_pad(
                        number_format(num: $counterDifferentFromMatomo, thousands_separator: '.'),
                        12,
                        ' ',
                        STR_PAD_LEFT,
                    ),
                ],
            ],
        );

        $table->render();

        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);

        return self::SUCCESS;
    }

    /**
     * @param array<non-empty-string, non-empty-string> $headers
     *
     * @throws void
     *
     * @phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
     */
    private function handleTest(
        OutputInterface $output,
        LoggerInterface $consoleLogger,
        array $headers,
        string $parentMessage,
        int &$messageLength = 0,
    ): TestResult {
        $message = $parentMessage . ' - <info>detect for new result ...</info>';
        $diff    = $this->messageLength($output, $message, $messageLength);

        $output->write(
            messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
            options: OutputInterface::VERBOSITY_VERY_VERBOSE,
        );
        $output->writeln(sprintf(' <bg=red>%d</>', $messageLength), OutputInterface::VERBOSITY_DEBUG);

        try {
            /** @var array{headers?: array<non-empty-string, non-empty-string>, device?: array{architecture: string, deviceName: string|null, marketingName: string|null, manufacturer: string, brand: string, type: string, ismobile: bool, istv: bool, display: array{width: int|null, height: int|null, touch: bool|null, size: float|null}, dualOrientation: bool|null, simCount: int|null, bits: int}, client?: array{name: string|null, modus: string|null, version: string|null, manufacturer: string, type: string, isbot: bool, bits: int}, os?: array{name: string|null, marketingName: string|null, version: string|null, manufacturer: string, bits: int}, engine?: array{name: string|null, version: string|null, manufacturer: string}} $newResult */
            $newResult = $this->detector->getBrowser($headers);
        } catch (InvalidArgumentException | UnexpectedValueException $e) {
            $consoleLogger->error($e);

            return new TestResult(
                result: null,
                status: TestResult::STATUS_ERROR,
                headers: $headers,
                exit: TestResult::EXIT_NO_RESULT,
            );
        }

        $message = $parentMessage . ' - <info>analyze new result ...</info>';
        $diff    = $this->messageLength($output, $message, $messageLength);

        $output->write(
            messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
            options: OutputInterface::VERBOSITY_VERY_VERBOSE,
        );
        $output->writeln(sprintf(' <bg=red>%d</>', $messageLength), OutputInterface::VERBOSITY_DEBUG);

        if (
            ($newResult['device']['deviceName'] ?? null) === null
            && ($newResult['client']['isbot'] ?? false) === false
        ) {
            return new TestResult(
                result: $this->buildResult(
                    consoleLogger: $consoleLogger,
                    data: $newResult,
                    headers: $headers,
                ),
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_IS_NULL,
            );
        }

        if (($newResult['client']['name'] ?? null) === null) {
            return new TestResult(
                result: $this->buildResult(
                    consoleLogger: $consoleLogger,
                    data: $newResult,
                    headers: $headers,
                ),
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_CLIENT_IS_NULL,
            );
        }

        if (
            !is_scalar($newResult['device']['deviceName'] ?? null)
            && ($newResult['client']['isbot'] ?? false) === false
        ) {
            return new TestResult(
                result: $this->buildResult(
                    consoleLogger: $consoleLogger,
                    data: $newResult,
                    headers: $headers,
                ),
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_NOT_SCALAR,
            );
        }

        if (!is_scalar($newResult['client']['name'] ?? null)) {
            return new TestResult(
                result: null,
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_CLIENT_NOT_SCALAR,
            );
        }

        if (str_contains((string) $newResult['client']['name'], 'general')) {
            return new TestResult(
                result: $this->buildResult(
                    consoleLogger: $consoleLogger,
                    data: $newResult,
                    headers: $headers,
                ),
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_CLIENT_IS_GENERAL,
            );
        }

        if (($newResult['client']['name'] ?? null) === 'unknown') {
            return new TestResult(
                result: $this->buildResult(
                    consoleLogger: $consoleLogger,
                    data: $newResult,
                    headers: $headers,
                ),
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_CLIENT_IS_UNKNOW,
            );
        }

        if (
            str_contains((string) ($newResult['device']['deviceName'] ?? null), 'general')
            && ($newResult['client']['isbot'] ?? false) === false
        ) {
            return new TestResult(
                result: $this->buildResult(
                    consoleLogger: $consoleLogger,
                    data: $newResult,
                    headers: $headers,
                ),
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_IS_GENERAL,
            );
        }

        if (
            ($newResult['device']['deviceName'] ?? null) === 'unknown'
            && ($newResult['client']['isbot'] ?? false) === false
        ) {
            // only check for unknown device, if it's not a bot
            return new TestResult(
                result: $this->buildResult(
                    consoleLogger: $consoleLogger,
                    data: $newResult,
                    headers: $headers,
                ),
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_IS_UNKNOW,
            );
        }

        if (
            in_array(
                $newResult['client']['type'] ?? null,
                ['bot', 'crawler', 'search-bot', 'service-agent', 'offline-browser'],
                strict: true,
            )
            || ($newResult['client']['isbot'] ?? false) === true
        ) {
            assert(is_scalar($newResult['client']['name'] ?? null));
            assert(is_scalar($newResult['os']['name'] ?? null));
            assert(is_scalar($newResult['device']['deviceName'] ?? null));

            $keys = [
                (string) $newResult['client']['name'],
                (string) $newResult['os']['name'],
                (string) $newResult['device']['deviceName'],
            ];

            return $this->buildTestResult(
                consoleLogger: $consoleLogger,
                newResult: $newResult,
                keys: $keys,
                headers: $headers,
                exit: TestResult::EXIT_CLIENT_IS_BOT,
            );
        }

        if (
            in_array(
                $newResult['device']['deviceName'] ?? null,
                ['general Desktop', 'general Apple Device', 'PC', 'Macintosh', 'Linux Desktop', 'Windows Desktop'],
                strict: true,
            )
        ) {
            $keys = ['desktop'];

            return $this->buildTestResult(
                consoleLogger: $consoleLogger,
                newResult: $newResult,
                keys: $keys,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_IS_DESKTOP,
            );
        }

        $deviceType = DeviceType::fromName($newResult['device']['type'] ?? 'unknown');

        if ($deviceType->isMobile() || $deviceType->isTablet()) {
            $keys = ['mobile'];

            return $this->buildTestResult(
                consoleLogger: $consoleLogger,
                newResult: $newResult,
                keys: $keys,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_IS_MOBILE,
            );
        }

        if ($deviceType->isTv()) {
            $keys = ['tv'];

            return $this->buildTestResult(
                consoleLogger: $consoleLogger,
                newResult: $newResult,
                keys: $keys,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_IS_TV,
            );
        }

        $keys = ['other'];

        return $this->buildTestResult(
            consoleLogger: $consoleLogger,
            newResult: $newResult,
            keys: $keys,
            headers: $headers,
            exit: TestResult::EXIT_DEVICE_IS_OTHER,
        );
    }

    /**
     * @param array{headers?: array<non-empty-string, non-empty-string>, device?: array{architecture: string, deviceName: string|null, marketingName: string|null, manufacturer: string, brand: string, type: string, ismobile: bool, istv: bool, display: array{width: int|null, height: int|null, touch: bool|null, size: float|null}, dualOrientation: bool|null, simCount: int|null, bits: int}, client?: array{name: string|null, modus: string|null, version: string|null, manufacturer: string, type: string, isbot: bool, bits: int}, os?: array{name: string|null, marketingName: string|null, version: string|null, manufacturer: string, bits: int}, engine?: array{name: string|null, version: string|null, manufacturer: string}} $newResult
     * @param array<int, string>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               $keys
     * @param array<non-empty-string, non-empty-string>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                        $headers
     * @param TestResult::EXIT_*                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                               $exit
     *
     * @throws void
     */
    private function buildTestResult(LoggerInterface $consoleLogger, array $newResult, array $keys, array $headers, int $exit): TestResult
    {
        assert(is_scalar($newResult['client']['name'] ?? null));
        assert(is_scalar($newResult['engine']['name'] ?? null));
        assert(is_scalar($newResult['os']['name'] ?? null));
        assert(is_scalar($newResult['device']['deviceName'] ?? null));
        assert(is_scalar($newResult['device']['manufacturer'] ?? null));

        $keys = [
            ...$keys,
            (string) $newResult['client']['name'],
            (string) $newResult['engine']['name'],
            (string) $newResult['os']['name'],
            (string) $newResult['device']['deviceName'],
            (string) $newResult['device']['manufacturer'],
        ];

        $key = implode('-', $keys);

        if (array_key_exists($key, $this->tests)) {
            return new TestResult(
                result: $this->buildResult(
                    consoleLogger: $consoleLogger,
                    data: $newResult,
                    headers: $headers,
                ),
                status: TestResult::STATUS_DUPLICATE,
                headers: $headers,
                exit: $exit,
            );
        }

        $this->tests[$key] = 1;

        return new TestResult(
            result: $this->buildResult(
                consoleLogger: $consoleLogger,
                data: $newResult,
                headers: $headers,
            ),
            status: TestResult::STATUS_OK,
            headers: $headers,
            exit: $exit,
        );
    }

    /**
     * @param array<int|string, string> $matches
     * @param non-empty-list<Result>    $results
     *
     * @throws RuntimeException
     */
    private function rewriteFile(
        OutputInterface $output,
        string $basePath,
        string $baseMessage,
        array $matches,
        array $results,
        int $number,
        int &$messageLength,
    ): void {
        $path  = $basePath;
        $path .= sprintf(
            'data/tests/%s/%s/%s/%s/%07d.json',
            $matches['deviceManufaturer'],
            $matches['deviceType'],
            $matches['clientManufaturer'],
            $matches['clientType'],
            $number,
        );

        $p1 = sprintf('data/tests/%s', $matches['deviceManufaturer']);

        if (!file_exists($basePath . $p1)) {
            $output->write(
                messages: sprintf('create directory %s', $basePath . $p1),
            );
            mkdir($basePath . $p1);
        }

        $p2 = sprintf('data/tests/%s/%s', $matches['deviceManufaturer'], $matches['deviceType']);

        if (!file_exists($basePath . $p2)) {
            $output->write(
                messages: sprintf('create directory %s', $basePath . $p2),
            );
            mkdir($basePath . $p2);
        }

        $p3 = sprintf(
            'data/tests/%s/%s/%s',
            $matches['deviceManufaturer'],
            $matches['deviceType'],
            $matches['clientManufaturer'],
        );

        if (!file_exists($basePath . $p3)) {
            $output->write(
                messages: sprintf('create directory %s', $basePath . $p3),
            );
            mkdir($basePath . $p3);
        }

        $p4 = sprintf(
            'data/tests/%s/%s/%s/%s',
            $matches['deviceManufaturer'],
            $matches['deviceType'],
            $matches['clientManufaturer'],
            $matches['clientType'],
        );

        if (!file_exists($basePath . $p4)) {
            $output->write(
                messages: sprintf('create directory %s', $basePath . $p4),
            );
            mkdir($basePath . $p4);
        }

        $message  = $baseMessage;
        $message .= sprintf(
            'data/tests/%s/%s/%s/%s/%07d.json',
            $matches['deviceManufaturer'],
            $matches['deviceType'],
            $matches['clientManufaturer'],
            $matches['clientType'],
            $number,
        );
        $message .= ' - normalizing';

        $diff = $this->messageLength($output, $message, $messageLength);

        $output->write(
            messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
            options: OutputInterface::VERBOSITY_VERY_VERBOSE,
        );
        $output->writeln(sprintf(' <bg=red>%d</>', $messageLength), OutputInterface::VERBOSITY_DEBUG);

        $normalized = $this->jsonNormalizer->normalize(
            output: $output,
            testCases: $results,
            message: $message,
            messageLength: $messageLength,
        );

        if ($normalized === null) {
            throw new RuntimeException(sprintf('file "%s" contains invalid json', $path));
        }

        $message  = $baseMessage;
        $message .= sprintf(
            'data/tests/%s/%s/%s/%s/%07d.json',
            $matches['deviceManufaturer'],
            $matches['deviceType'],
            $matches['clientManufaturer'],
            $matches['clientType'],
            $number,
        );
        $message .= ' - writing';

        $diff = $this->messageLength($output, $message, $messageLength);

        $output->write(
            messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
            options: OutputInterface::VERBOSITY_VERY_VERBOSE,
        );
        $output->writeln(sprintf(' <bg=red>%d</>', $messageLength), OutputInterface::VERBOSITY_DEBUG);

        $success = @file_put_contents($path, $normalized);

        if ($success !== false) {
            return;
        }

        throw new RuntimeException(
            sprintf(
                'An error occured while writing file %s',
                $path,
            ),
        );
    }

    /** @throws void */
    private function saveResult(
        OutputInterface $output,
        Result $result,
        string $file,
        string $loopMessage,
        int &$errors,
        int &$messageLength,
        float &$timeRead,
        float &$timeWrite,
    ): bool {
        $tests = [];

        if (file_exists($file)) {
            $addMessage = sprintf('read temporary file %s', $file);
            $message    = $loopMessage . $addMessage;
            $diff       = $this->messageLength($output, $message, $messageLength);

            $output->write(
                messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
            $output->writeln(
                sprintf(' <bg=red>%d</>', $messageLength),
                OutputInterface::VERBOSITY_DEBUG,
            );

            $startTime = microtime(as_float: true);

            try {
                $json  = Json::fromFile($file);
                $tests = $json->decoded();

                assert(is_array($tests));

                $addMessage = sprintf('read temporary file %s - <info>done</info>', $file);
                $message    = $loopMessage . $addMessage;
                $diff       = $this->messageLength($output, $message, $messageLength);

                $output->write(
                    messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );
                $output->writeln(
                    sprintf(' <bg=red>%d</>', $messageLength),
                    OutputInterface::VERBOSITY_DEBUG,
                );
            } catch (FileCanNotBeRead | FileDoesNotContainJson | FileDoesNotExist $e) {
                ++$errors;

                $exception = new Exception('An error occured while decoding a result', 0, $e);

                $addMessage = sprintf('<error>%s</error>', (string) $exception);

                $message = $loopMessage . $addMessage;

                $output->writeln(
                    messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );

                return false;
            } finally {
                $timeRead += microtime(as_float: true) - $startTime;
            }
        } else {
            $addMessage = sprintf('temporary file %s <info>not found</info>', $file);
            $message    = $loopMessage . $addMessage;
            $diff       = $this->messageLength($output, $message, $messageLength);

            $output->write(
                messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
            $output->writeln(
                sprintf(' <bg=red>%d</>', $messageLength),
                OutputInterface::VERBOSITY_DEBUG,
            );
        }

        $addMessage = sprintf('write to temporary file %s', $file);
        $message    = $loopMessage . $addMessage;
        $diff       = $this->messageLength($output, $message, $messageLength);

        $output->write(
            messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(sprintf(' <bg=red>%d</>', $messageLength), OutputInterface::VERBOSITY_DEBUG);

        try {
            $tests[] = $result->toArray();
        } catch (UnexpectedValueException) {
            ++$errors;

            $addMessage = sprintf('<error>An error occured while handling file %s</error>', $file);
            $message    = $loopMessage . $addMessage;
            $diff       = $this->messageLength($output, $message, $messageLength);

            $output->writeln(
                messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                options: OutputInterface::VERBOSITY_NORMAL,
            );

            return false;
        }

        $startTime = microtime(as_float: true);

        try {
            $saved = file_put_contents(
                filename: $file,
                data: json_encode(
                    $tests,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION,
                ),
            );
        } catch (JsonException) {
            ++$errors;

            $addMessage = sprintf('<error>An error occured while encoding file %s</error>', $file);
            $message    = $loopMessage . $addMessage;
            $diff       = $this->messageLength($output, $message, $messageLength);

            $output->writeln(
                messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                options: OutputInterface::VERBOSITY_NORMAL,
            );

            return false;
        } finally {
            $timeWrite += microtime(as_float: true) - $startTime;

            unset($tests);
        }

        return $saved !== false;
    }

    /** @throws void */
    private function messageLength(OutputInterface $output, string $message, int &$messageLength): int
    {
        $messageLengthWithoutFormat = Helper::width(
            Helper::removeDecoration($output->getFormatter(), $message),
        );
        $messageLengthWithFormat    = Helper::width($message);

        $messageLength = min(
            max(
                $messageLength,
                $messageLengthWithFormat,
            ),
            200,
        );

        return $messageLengthWithFormat - $messageLengthWithoutFormat;
    }

    /**
     * @param array<string, string>             $headers
     * @param array<string, array<string, int>> $resultChecks
     *
     * @throws UnexpectedValueException
     */
    private function compareDeviceWithMapper(
        OutputInterface $output,
        LoggerInterface $consoleLogger,
        TestProperties $testProperties,
        TestResult $testResult,
        array $headers,
        string $loopMessage,
        int &$messageLength,
        int &$counterDifferentFromMatomo,
        array &$resultChecks,
    ): void {
        $inputMapper = new InputMapper();

        $clientHints = ClientHints::factory($headers);

        $this->deviceDetector->setUserAgent($headers['user-agent'] ?? '');
        $this->deviceDetector->setClientHints($clientHints);
        $this->deviceDetector->parse();

        $ddDeviceType = $inputMapper->mapDeviceType($this->deviceDetector->getDeviceName());

        if ($this->deviceDetector->getDeviceName() !== '' && $ddDeviceType === DeviceType::Unknown) {
            $output->writeln(
                messages: "\n" . mb_str_pad(
                    string: sprintf(
                        'The device type "<fg=magenta>%s (%s)</>" from Matomo for user-agent Header "%s" was not found in the Enum. Please add it.',
                        $this->deviceDetector->getDeviceName(),
                        $ddDeviceType->getType(),
                        $headers['user-agent'] ?? '',
                    ),
                    length: $messageLength,
                ),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
        }

        $result = $testResult->getResult();

        if (!$result instanceof Result) {
            return;
        }

        $resultTypeName = $result->getDevice()->getType()->getType();

        if ($resultTypeName !== 'unknown') {
            $resultType = DeviceType::fromName($resultTypeName);

            if ($resultType === DeviceType::Unknown) {
                $output->writeln(
                    messages: "\n" . mb_str_pad(
                        string: sprintf(
                            'The device type "<fg=magenta>%s</>" for user-agent Header "%s" was not found in the Enum. Please add it.',
                            $result->getDevice()->getType()->getType(),
                            $headers['user-agent'] ?? '',
                        ),
                        length: $messageLength,
                    ),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );
            }

            if ($resultType->hasTouch() && $result->getDevice()->getDisplay()->hasTouch() !== true) {
                $output->writeln(
                    messages: "\n" . mb_str_pad(
                        string: sprintf(
                            'The device for user-agent Header "%s" (%s) should have a touch screen, but this is not configured.',
                            $headers['user-agent'] ?? '',
                            $resultTypeName,
                        ),
                        length: $messageLength,
                    ),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );
            }

            if (!$resultType->hasTouch() && $result->getDevice()->getDisplay()->hasTouch() === true) {
                $output->writeln(
                    messages: "\n" . mb_str_pad(
                        string: sprintf(
                            'The device for user-agent Header "%s" (%s) should NOT have a touch screen, but this is not configured.',
                            $headers['user-agent'] ?? '',
                            $resultTypeName,
                        ),
                        length: $messageLength,
                    ),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );
            }
        }

        $ddModel = $inputMapper->mapDeviceMarketingName($this->deviceDetector->getModel());
        $ddBrand = $inputMapper->mapDeviceBrandName($this->deviceDetector->getBrandName());

        $isBot      = $this->deviceDetector->isBot();
        $osInfo     = $this->deviceDetector->getOs();
        $clientInfo = $this->deviceDetector->getClient();
        $botInfo    = $this->deviceDetector->getBot();

        $ddOsName = $inputMapper->mapOsName($osInfo['name'] ?? null);

        try {
            $ddOsVersion = $inputMapper->mapOsVersion($osInfo['version'] ?? null, $ddOsName);
        } catch (NotNumericException $e) {
            $consoleLogger->error($e);

            return;
        }

        $ddEngineName = $inputMapper->mapEngineName($isBot ? null : ($clientInfo['engine'] ?? null));

        try {
            $ddEngineVersion = $inputMapper->mapEngineVersion(
                $isBot ? null : ($clientInfo['engine_version'] ?? null),
            );
        } catch (NotNumericException $e) {
            $consoleLogger->error($e);

            return;
        }

        $ddClientName = $inputMapper->mapBrowserName(
            $isBot ? ($botInfo['name'] ?? null) : ($clientInfo['name'] ?? null),
        );

        try {
            $ddClientVersion = $inputMapper->mapBrowserVersion(
                $isBot ? null : ($clientInfo['version'] ?? null),
            );
        } catch (NotNumericException $e) {
            $consoleLogger->error($e);

            return;
        }

        $ddClientType = $inputMapper->mapBrowserType(
            $isBot ? ($botInfo['category'] ?? null) : ($clientInfo['type'] ?? null),
        );

        $brModel      = $inputMapper->mapDeviceName($result->getDevice()->getDeviceName());
        $brModel2     = $inputMapper->mapDeviceMarketingName(
            $result->getDevice()->getMarketingName(),
        );
        $brBrand      = $inputMapper->mapDeviceBrandName(
            $result->getDevice()->getBrand()->getBrandname(),
        );
        $brDeviceType = $inputMapper->mapDeviceType($result->getDevice()->getType()->getType());
        $brOsName     = $inputMapper->mapOsName($result->getOs()->getName());
        $brOsMName    = $inputMapper->mapOsName($result->getOs()->getMarketingName());

        try {
            $brOsVersion = $inputMapper->mapOsVersion(
                $result->getOs()->getVersion()->getVersion(),
                $brOsName,
            );
        } catch (NotNumericException $e) {
            $consoleLogger->error($e);

            return;
        }

        $brEngineName = $inputMapper->mapEngineName($result->getEngine()->getName());

        try {
            $brEngineVersion = $inputMapper->mapEngineVersion(
                $result->getEngine()->getVersion()->getVersion(),
            );
        } catch (NotNumericException $e) {
            $consoleLogger->error($e);

            return;
        }

        $brClientName = $inputMapper->mapBrowserName($result->getBrowser()->getName());

        try {
            $brClientVersion = $inputMapper->mapBrowserVersion(
                $result->getBrowser()->getVersion()->getVersion(),
            );
        } catch (NotNumericException $e) {
            $consoleLogger->error($e);

            return;
        }

        $brClientType = $inputMapper->mapBrowserType($result->getBrowser()->getType()->getType());

        $deviceBrandCheck   = $ddBrand !== $brBrand && $ddBrand !== null;
        $deviceNameCheck    = $ddModel !== null
            && mb_strtolower($ddModel) !== mb_strtolower($brModel ?? '')
            && mb_strtolower($ddModel) !== mb_strtolower($brModel2 ?? '')
            && $ddModel !== 'K';
        $deviceTypeCheck    = $ddDeviceType->getType() !== $brDeviceType->getType()
            && $ddDeviceType !== DeviceType::Unknown;
        $osNameCheck        = $ddOsName !== null
            && mb_strtolower($ddOsName) !== mb_strtolower($brOsName ?? '')
            && mb_strtolower($ddOsName) !== mb_strtolower($brOsMName ?? '');
        $osVersionCheck     = $ddOsVersion->getVersion(VersionInterface::IGNORE_MICRO)
            !== $brOsVersion->getVersion(VersionInterface::IGNORE_MICRO)
            && $ddOsVersion->getVersion(VersionInterface::IGNORE_MICRO) !== null;
        $engineNameCheck    = $ddEngineName !== $brEngineName && $ddEngineName !== null;
        $engineVersionCheck = $ddEngineVersion->getVersion(VersionInterface::IGNORE_MICRO)
            !== $brEngineVersion->getVersion(VersionInterface::IGNORE_MICRO)
            && $ddEngineVersion->getVersion(VersionInterface::IGNORE_MICRO) !== null;
        $clientNameCheck    = mb_strtolower($ddClientName ?? '') !== mb_strtolower($brClientName ?? '');
        $clientVersionCheck = $ddClientVersion->getVersion(VersionInterface::IGNORE_MINOR)
            !== $brClientVersion->getVersion(VersionInterface::IGNORE_MINOR)
            && $ddClientVersion->getVersion(VersionInterface::IGNORE_MINOR) !== null;
        $clientTypeCheck    = $ddClientType !== $brClientType
            && $ddClientType !== ClientType::Unknown;

        try {
            $checks = [
                '$deviceBrandCheck' => !$deviceBrandCheck,
                '$deviceNameCheck' => !$deviceNameCheck,
                '$deviceTypeCheck' => !$deviceTypeCheck,
                '$osNameCheck' => !$osNameCheck,
                '$osVersionCheck' => !$osVersionCheck,
                '$engineNameCheck' => !$engineNameCheck,
                '$engineVersionCheck' => !$engineVersionCheck,
                '$clientNameCheck' => !$clientNameCheck,
                '$clientVersionCheck' => !$clientVersionCheck,
                '$clientTypeCheck' => !$clientTypeCheck,
            ];
        } catch (Throwable $e) {
            $consoleLogger->error($e);

            return;
        }

        $return = true;

        foreach ($checks as $check) {
            $return = $return && $check;
        }

        if ($return) {
            return;
        }

        ++$counterDifferentFromMatomo;

        if (!array_key_exists('differentFromMatomo', $resultChecks['general'])) {
            $resultChecks['general']['differentFromMatomo'] = 0;
        }

        ++$resultChecks['general']['differentFromMatomo'];

        if (!array_key_exists('differentFromMatomo', $resultChecks[$testProperties->getDateLast()])) {
            $resultChecks[$testProperties->getDateLast()]['differentFromMatomo'] = 0;
        }

        ++$resultChecks[$testProperties->getDateLast()]['differentFromMatomo'];

        $getMessage = function () use ($output, $checks, $deviceBrandCheck, $deviceNameCheck, $deviceTypeCheck, $osNameCheck, $osVersionCheck, $engineNameCheck, $engineVersionCheck, $clientNameCheck, $clientVersionCheck, $clientTypeCheck, $loopMessage, $result, $headers, $ddModel, $ddBrand, $ddDeviceType, $isBot, $osInfo, $clientInfo, $botInfo, $ddOsName, $ddOsVersion, $ddEngineName, $ddEngineVersion, $ddClientName, $ddClientVersion, $ddClientType, $brModel, $brModel2, $brBrand, $brDeviceType, $brOsName, $brOsMName, $brOsVersion, $brEngineName, $brEngineVersion, $brClientName, $brClientVersion, $brClientType, $consoleLogger): string {
            $message        = $loopMessage;
            $someDifference = false;

            $format1d  = '<fg=yellow>';
            $format2d  = '<fg=yellow>';
            $format3d  = '<fg=yellow>';
            $format4d  = '<fg=yellow>';
            $format5d  = '<fg=yellow>';
            $format6d  = '<fg=yellow>';
            $format7d  = '<fg=yellow>';
            $format8d  = '<fg=yellow>';
            $format9d  = '<fg=yellow>';
            $format10d = '<fg=yellow>';
            $format1b  = '<fg=yellow>';
            $format2b  = '<fg=yellow>';
            $format3b  = '<fg=yellow>';
            $format4b  = '<fg=yellow>';
            $format5b  = '<fg=yellow>';
            $format6b  = '<fg=yellow>';
            $format7b  = '<fg=yellow>';
            $format8b  = '<fg=yellow>';
            $format9b  = '<fg=yellow>';
            $format10b = '<fg=yellow>';

            if ($deviceBrandCheck) {
                $format1b       = '<fg=green>';
                $format1d       = '<fg=red>';
                $someDifference = true;
            }

            if ($deviceNameCheck) {
                $format2b       = '<fg=green>';
                $format2d       = '<fg=red>';
                $someDifference = true;
            }

            if ($deviceTypeCheck) {
                $format3b       = '<fg=green>';
                $format3d       = '<fg=red>';
                $someDifference = true;
            }

            if ($osNameCheck) {
                $format4b       = '<fg=green>';
                $format4d       = '<fg=red>';
                $someDifference = true;
            }

            if ($osVersionCheck) {
                $format5b       = '<fg=green>';
                $format5d       = '<fg=red>';
                $someDifference = true;
            }

            if ($engineNameCheck) {
                $format6b       = '<fg=green>';
                $format6d       = '<fg=red>';
                $someDifference = true;
            }

            if ($engineVersionCheck) {
                $format7b       = '<fg=green>';
                $format7d       = '<fg=red>';
                $someDifference = true;
            }

            if ($clientNameCheck) {
                $format8b       = '<fg=green>';
                $format8d       = '<fg=red>';
                $someDifference = true;
            }

            if ($clientVersionCheck) {
                $format9b       = '<fg=green>';
                $format9d       = '<fg=red>';
                $someDifference = true;
            }

            if ($clientTypeCheck) {
                $format10b      = '<fg=green>';
                $format10d      = '<fg=red>';
                $someDifference = true;
            }

            if (!$someDifference) {
                $output->writeln('');
                $consoleLogger->error(var_export($checks, return: true));
                $output->writeln('');
            }

            $headerList = $this->getHeaderList($headers);

            return $message . PHP_EOL . sprintf(
                '    For the Headers' . PHP_EOL . '%s' . PHP_EOL
                . '    the device was detected as    "%s%s -> %s</>" "%s%s/%s -> %s/%s</>" (%s%s -> %s</>), ' . PHP_EOL
                . '        but Matomo detected it as "%s%s -> %s</>" "%s%s -> %s</>" (%s%s -> %s</>)' . PHP_EOL
                . '    the platform was detected as  "%s%s/%s -> %s/%s</>" "%s%s -> %s</>", ' . PHP_EOL
                . '        but Matomo detected it as "%s%s -> %s</>" "%s%s -> %s</>"' . PHP_EOL
                . '    the engine was detected as    "%s%s -> %s</>" "%s%s -> %s</>", ' . PHP_EOL
                . '        but Matomo detected it as "%s%s -> %s</>" "%s%s -> %s</>"' . PHP_EOL
                . '    the client was detected as    "%s%s -> %s</>" "%s%s -> %s</>" (%s%s -> %s</>), ' . PHP_EOL
                . '        but Matomo detected it as "%s%s -> %s</>" "%s%s -> %s</>" (%s%s -> %s</>)',
                implode(PHP_EOL, $headerList),
                $format1b,
                $result->getDevice()->getBrand()->getBrandname() ?? '<n/a>',
                $brBrand,
                $format2b,
                $result->getDevice()->getDeviceName() ?? '<n/a>',
                $result->getDevice()->getMarketingName() ?? '<n/a>',
                $brModel,
                $brModel2,
                $format3b,
                $result->getDevice()->getType()->getName() ?? '<n/a>',
                $brDeviceType->getType(),
                $format1d,
                $this->deviceDetector->getBrandName(),
                $ddBrand,
                $format2d,
                $this->deviceDetector->getModel(),
                $ddModel,
                $format3d,
                $this->deviceDetector->getDeviceName(),
                $ddDeviceType->getType(),
                $format4b,
                $result->getOs()->getName() ?? '<n/a>',
                $result->getOs()->getMarketingName() ?? '<n/a>',
                $brOsName,
                $brOsMName,
                $format5b,
                $result->getOs()->getVersion()->getVersion() ?? '<n/a>',
                $brOsVersion->getVersion(),
                $format4d,
                $osInfo['name'] ?? '',
                $ddOsName,
                $format5d,
                $osInfo['version'] ?? '',
                $ddOsVersion->getVersion(),
                $format6b,
                $result->getEngine()->getName() ?? '',
                $brEngineName,
                $format7b,
                $result->getEngine()->getVersion()->getVersion() ?? '',
                $brEngineVersion->getVersion(),
                $format6d,
                $isBot ? '' : ($clientInfo['engine'] ?? ''),
                $ddEngineName,
                $format7d,
                $isBot ? '' : ($clientInfo['engine_version'] ?? ''),
                $ddEngineVersion->getVersion(),
                $format8b,
                $result->getBrowser()->getName() ?? '',
                $brClientName,
                $format9b,
                $result->getBrowser()->getVersion()->getVersion() ?? '',
                $brClientVersion->getVersion(),
                $format10b,
                $result->getBrowser()->getType()->getType(),
                $brClientType->getType(),
                $format8d,
                $isBot ? ($botInfo['name'] ?? '') : ($clientInfo['name'] ?? ''),
                $ddClientName,
                $format9d,
                $isBot ? '' : ($clientInfo['version'] ?? ''),
                $ddClientVersion->getVersion(),
                $format10d,
                $isBot ? ($botInfo['category'] ?? '') : ($clientInfo['type'] ?? ''),
                $ddClientType->getType(),
            );
        };

        try {
            $message = $getMessage();
        } catch (UnexpectedValueException $e) {
            $consoleLogger->error($e);

            return;
        }

        $diff = $this->messageLength($output, $message, $messageLength);

        $output->writeln(
            messages: "\n" . mb_str_pad(
                string: $message,
                length: $messageLength + $diff,
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
    }

    /**
     * @param array<string, string> $headers
     *
     * @return array<int, string>
     *
     * @throws void
     */
    private function getHeaderList(array $headers): array
    {
        $headerList = [];

        foreach ($headers as $name => $value) {
            $headerList[] = '        "' . $name . '" => "' . $value . '"';
        }

        return $headerList;
    }

    /** @throws void */
    private function acceptByDate(TestProperties $testProperties, OutputInterface $output, string $loopMessage): bool
    {
        try {
            $date = new DateTimeImmutable($testProperties->getDateLast());
        } catch (Throwable $e) {
            $output->writeln('');
            $output->writeln(
                messages: sprintf('%s <error>invalid date: %s</error>', $loopMessage, $e),
                options: OutputInterface::VERBOSITY_NORMAL,
            );

            return false;
        }

        $compareDateStart = new DateTimeImmutable(self::COMPARE_DATE_START);
        $compareDateEnd   = new DateTimeImmutable(self::COMPARE_DATE_END);

        return $date > $compareDateStart && $date <= $compareDateEnd;
    }

    /**
     * @param array<string, array<string, array{count: int, checked?: bool}>> $checkedPlatforms
     * @param array<string, array<string, array{count: int, checked?: bool}>> $checkedEngines
     *
     * @throws void
     */
    private function compareWithMatomo(
        string $osName,
        string $osVersion,
        string $engineName,
        string $engineVersion,
        array &$checkedPlatforms,
        array &$checkedEngines,
    ): bool {
        if (!isset($checkedPlatforms[$osName][$osVersion])) {
            $checkedPlatforms[$osName][$osVersion]['count']   = 1;
            $checkedPlatforms[$osName][$osVersion]['checked'] = false;
        } else {
            ++$checkedPlatforms[$osName][$osVersion]['count'];
        }

        if (!isset($checkedEngines[$engineName][$engineVersion])) {
            $checkedEngines[$engineName][$engineVersion]['count']   = 1;
            $checkedEngines[$engineName][$engineVersion]['checked'] = false;
        } else {
            ++$checkedEngines[$engineName][$engineVersion]['count'];
        }

        $resultOs     = false;
        $resultEngine = false;

        if (
            in_array(
                $osName,
                [
                    'harmony-os',
                    'harmonyos',
                    'fire-os',
                    'fireos',
                    'fire os',
                    'fuchsia',
                    'puffin os',
                    'lineage os',
                    'wophone',
                    'star-blade os',
                    'xubuntu',
                    'yi',
                    // 'chinese operating system',
                    'nextstep',
                    'windows iot',
                    'ultrix',
                    'genix',
                    'news-os',
                    // 'turbolinux',
                    // 'backtrack linux',
                    // 'ark linux',
                    'blackpanther os',
                    'aros',
                    'zenwalk gnu linux',
                    // 'azure linux',
                    'wyderos',
                    'opensolaris',
                    'startos',
                    // 'ventana linux',
                    'joli os',
                    // 'debian with freebsd kernel',
                    'liberate',
                    // 'moblin',
                    // 'raspbian',
                    // 'rim tablet os',
                    // 'blackberry tablet os',
                    'morphos',
                    // 'mandriva linux',
                    // 'linux mint',
                    'inferno os',
                    // 'haiku',
                    // 'archlinux',
                    // 'cent os linux',
                    // 'orbis os',
                    // 'cellos',
                    'nintendo os',
                    // 'beos',
                    'chromeos',
                    'gentoo linux',
                    'kubuntu',
                    'slackware linux',
                    'redhat linux',
                    // 'solaris',
                    // 'syllable',
                    // 'suse linux',
                    // 'kin os',
                    'threadx',
                    // 'sailfishos',
                    'remix os',
                    // 'meego',
                    // 'palmos',
                    'risc os',
                    // 'hp-ux',
                    'pardus',
                    'danger os',
                    // 'lindowsos',
                    'nintendo switch os',
                    'tvos',
                    // 'windows 3.11',
                    // 'series 30',
                    'nintendo wii os',
                    // 'windows 2003',
                    // 'windows rt',
                    // 'tru64 unix',
                    // 'cp/m',
                    // 'cygwin',
                    // 'openvms',
                    'wear os',
                    'dragonfly bsd',
                    'darwin',
                    // 'bsd',
                    'watchos',
                    // 'windows 3.1',
                    // 'aix',
                    'macintosh',
                    'fedora linux',
                    'yun os',
                    // 'firefox os',
                    // 'windows 95',
                    'debian',
                    // 'irix',
                    // 'windows 98',
                    'openharmony',
                    // 'osf/1',
                    // 'haiku os',
                    'opensuse',
                    // 'linspire',
                    'android opensource project',
                    // 'maemo',
                    // 'bada',
                    'plasma mobile',
                    // 'series 40',
                    // 'nucleus os',
                    // 'amiga os',
                    'android tv',
                    // 'unix',
                    // 'windows nt',
                    'whale os',
                    // 'os/2',
                    'iphone os',
                    'macos',
                    'ipados',
                    'ubuntu',
                    'ios',
                    'miui os',
                    // 'brew',
                    'freebsd',
                    'openbsd',
                    'netbsd',
                    'mac os x',
                    // 'sunos',
                    // 'series 60',
                    // 'javaos',
                    'linux',
                    'windows',
                    'orsay',
                    // 'java-me',
                    // 'windows ce',
                    'tizen',
                    // 'opera tv',
                    // 'java me',
                    'android',
                    // 'cyanogenmod',
                    // 'mocordroid',
                    // 'mre',
                    // 'kaios',
                    'vizios',
                    // 'windows mobile os',
                    // 'smartisan os',
                    // 'webos',
                    // 'symbian os',
                    // 'mocor os',
                    'horizon',
                    // 'rim os',
                    'lg webos',
                    // 'windows phone os',
                    // 'open mandriva',
                    // 'nokia os',
                    'qtopia',
                    '',
                ],
                strict: true,
            )
        ) {
            try {
                $versionOs = (new VersionBuilder())->set($osVersion);

                $osVersionFloat = (float) $versionOs->getVersion(VersionInterface::IGNORE_MICRO);
            } catch (NotNumericException | UnexpectedValueException) {
                $osVersionFloat = 0.0;
            }

            if (
                in_array(
                    $osName,
                    ['android', 'ios', 'android tv', 'ipados', 'android opensource project'],
                    strict: true,
                )
            ) {
                if ($osVersionFloat >= self::COMPARE_MATOMO_LOWER_VERSION_ANDROID_IOS) {
                    $checkedPlatforms[$osName][$osVersion]['checked'] = true;

                    $resultOs = true;
                }
            } elseif (in_array($osName, ['windows', 'windows rt'], strict: true)) {
                if ($osVersionFloat >= self::COMPARE_MATOMO_LOWER_VERSION_WINDOWS) {
                    $checkedPlatforms[$osName][$osVersion]['checked'] = true;

                    $resultOs = true;
                }
            } elseif (in_array($osName, ['mac os x', 'macintosh'], strict: true)) {
                if ($osVersionFloat >= self::COMPARE_MATOMO_LOWER_VERSION_MACOS) {
                    $checkedPlatforms[$osName][$osVersion]['checked'] = true;

                    $resultOs = true;
                }
            } else {
                $checkedPlatforms[$osName][$osVersion]['checked'] = true;

                $resultOs = true;
            }
        }

        if (
            in_array(
                $engineName,
                [
                    'blink',
                    'webkit',
                    'gecko',
                    'u2',
                    'u3',
                    'u4',
                    't7',
                    't5',
                    // 'presto',
                    // 'trident',
                    '',
                ],
                strict: true,
            )
        ) {
            $checkedEngines[$engineName][$engineVersion]['checked'] = true;

            $resultEngine = true;
        }

        return $resultOs && $resultEngine;
    }

    /**
     * @param array<non-empty-string, non-empty-string> $headers
     *
     * @throws void
     */
    private function hasInvalidHeaders(array $headers): bool
    {
        return array_any(
            $headers,
            static function (string $v): bool {
                $v = mb_strtolower($v);

                return str_starts_with($v, '-1')
                    || str_ends_with($v, '\\')
                    || str_starts_with($v, '@@')
                    || str_contains($v, '{${print(')
                    || str_contains($v, '<?=print(')
                    || str_contains($v, '+print(')
                    || str_contains($v, ' print(')
                    || str_contains($v, 'gethostbyname(')
                    || str_contains($v, ' http/1.')
                    || str_contains($v, 'nslookup')
                    || str_contains($v, '${jndi')
                    || str_contains($v, 'pg_sleep(')
                    || str_contains($v, 'concat(')
                    || str_contains($v, 'waitfor delay ')
                    || str_contains($v, 'wget http://')
                    || str_contains($v, '<?php')
                    || str_contains($v, '<\'')
                    || str_contains($v, '">')
                    || str_contains($v, '/**/')
                    || str_contains($v, ':()')
                    || str_contains($v, '>&0')
                    || str_contains($v, ' convert(')
                    || str_contains($v, '(select')
                    || str_contains($v, '</a>')
                    || str_contains($v, ';echo')
                    || str_contains($v, '; echo')
                    || str_contains($v, 'bin/uname')
                    || str_contains($v, 'bin/bash')
                    || str_contains($v, '(curl ')
                    || str_contains($v, 'curl -O')
                    || str_contains($v, '<input')
                    || str_contains($v, '<img')
                    || str_contains($v, '<video')
                    || str_contains($v, '<source')
                    || str_contains($v, '<script')
                    || str_contains($v, '<a ')
                    || str_contains($v, 'file_put_contents(')
                    || str_contains($v, 'file_get_contents(')
                    || str_contains($v, 'fromcharcode(')
                    || str_contains($v, '>(')
                    || str_contains($v, '<(')
                    || str_contains($v, ' and "')
                    || str_contains($v, ' or \'')
                    || str_contains($v, 'union all')
                    || str_contains($v, ' if(')
                    || str_contains($v, '78.29.51.27')
                    || str_contains($v, 'blockchain')
                    || str_contains($v, '${:')
                    || str_contains($v, '<![cdata[')
                    || str_contains($v, 'now(')
                    || str_contains($v, 'sysdate(')
                    || str_contains($v, 'sleep(')
                    || str_contains($v, 'xor(')
                    || str_contains($v, 'declare @')
                    || str_contains($v, 'varchar(')
                    || str_contains($v, 'set @')
                    || str_contains($v, 'benchmark(')
                    || str_contains($v, 'md5(')
                    || str_contains($v, 'order by')
                    || str_contains($v, 'count(')
                    || str_contains($v, 'generate_series(')
                    || str_contains($v, 'like(')
                    || str_contains($v, 'upper(')
                    || str_contains($v, 'hex(')
                    || str_contains($v, 'randomblob(')
                    || str_contains($v, '\':wts:\'')
                    || str_contains($v, '\':xkg:\'')
                    || str_contains($v, 'iif(')
                    || str_contains($v, 'chr(')
                    || str_contains($v, 'new date(')
                    || str_contains($v, 'while(')
                    || str_contains($v, '[r]')
                    || str_contains($v, '[n]')
                    || str_contains($v, '[r=302]')
                    || str_contains($v, '[plm=0]')
                    || preg_match('/gecko\/\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\.\d{6}/i', $v)
                    || preg_match('/^veoh-.+ service \(/i', $v)
                    || str_contains($v, 'dns_get_record(')
                    || str_contains($v, 'base64 -d')
                    || str_contains($v, 'xargs getent')
                    || str_contains($v, 'require(')
                    || str_contains($v, 'lookup(')
                    || str_contains($v, 'tenable.was')
                    || str_contains($v, 'tenable:was')
                    || str_contains($v, 'ldap:')
                    || str_contains($v, '${j${')
                    || str_contains($v, 'getaddress(')
                    || str_contains($v, 'inet_aton(')
                    || str_contains($v, '${env:')
                    || str_contains($v, 'tostring(')
                    || str_contains($v, 'substring(')
                    || str_contains($v, '.get(')
                    || str_contains($v, 'nessus.org')
                    || (mb_strlen($v) > 0 && mb_trim($v) === '');
                // || str_contains($v, '­')
            },
        );
    }

    /**
     * @param array<non-empty-string, non-empty-string> $headers
     *
     * @return array<non-empty-string, non-empty-string>
     *
     * @throws void
     */
    private function normalizeHeaders(array $headers): array
    {
        $normalized = [];

        foreach ($headers as $key => $header) {
            try {
                $normalizedHeader = $this->normalizerChain->normalize($header);
            } catch (\UaNormalizer\Normalizer\Exception\Exception) {
                continue;
            }

            if ($normalizedHeader === '') {
                continue;
            }

            $normalized[$key] = $normalizedHeader;
        }

        return $normalized;
    }

    /**
     * @param array<string, array<string, int>> $resultChecks
     *
     * @throws void
     */
    private function skipVersion(TestProperties $testProperties, array &$resultChecks, int &$skippedVersion): void
    {
        ++$skippedVersion;

        if (!array_key_exists('skippedVersion', $resultChecks['general'])) {
            $resultChecks['general']['skippedVersion'] = 0;
        }

        ++$resultChecks['general']['skippedVersion'];

        if (!array_key_exists('skippedVersion', $resultChecks[$testProperties->getDateLast()])) {
            $resultChecks[$testProperties->getDateLast()]['skippedVersion'] = 0;
        }

        ++$resultChecks[$testProperties->getDateLast()]['skippedVersion'];
    }

    /**
     * @param array<string, array<string, int>> $resultChecks
     *
     * @throws void
     */
    private function isVersionskipped(
        VersionInterface | null $versionOs,
        string $osName,
        TestProperties $testProperties,
        array &$resultChecks,
        int &$skippedVersion,
    ): bool {
        if (!$versionOs instanceof VersionInterface) {
            return false;
        }

        $majorVersion = (int) $versionOs->getMajor();

        try {
            $majorMinorVersion = (float) $versionOs->getVersion(VersionInterface::IGNORE_MICRO);
        } catch (UnexpectedValueException) {
            $majorMinorVersion = 0.0;
        }

        if (
            in_array(
                $osName,
                ['android', 'ios', 'android tv', 'ipados', 'android opensource project'],
                strict: true,
            )
            && $majorVersion < self::DETECT_LOWER_VERSION_ANDROID_IOS
        ) {
            $this->skipVersion(
                testProperties: $testProperties,
                resultChecks: $resultChecks,
                skippedVersion: $skippedVersion,
            );

            return true;
        }

        if (
            in_array(
                $osName,
                ['windows', 'windows rt'],
                strict: true,
            )
            && $majorVersion < self::DETECT_LOWER_VERSION_WINDOWS
        ) {
            $this->skipVersion(
                testProperties: $testProperties,
                resultChecks: $resultChecks,
                skippedVersion: $skippedVersion,
            );

            return true;
        }

        if (
            in_array(
                $osName,
                ['mac os x', 'macintosh'],
                strict: true,
            )
            && $majorMinorVersion < self::DETECT_LOWER_VERSION_MACOS
        ) {
            $this->skipVersion(
                testProperties: $testProperties,
                resultChecks: $resultChecks,
                skippedVersion: $skippedVersion,
            );

            return true;
        }

        return false;
    }

    /**
     * @param array<string, Result> $integrationTests
     *
     * @return array<string, Result>
     *
     * @throws void
     */
    private function updateIntegrationTests(Result $result, array $integrationTests, bool $addIntegrationTests): array
    {
        $headers = $result->getHeaders();
        $k       = Yaml::dump($headers);

        if ($addIntegrationTests || array_key_exists($k, $integrationTests)) {
            $integrationTests[$k] = [
                'headers' => $headers,
                'expected' => $result,
            ];

            // if (($headers['user-agent'] ?? '') === 'Lightpanda/1.0 internal-testing-crawler') {
            //     var_dump($headers, $k, $part, $result);exit;
            // }
        }

        return $integrationTests;
    }

    /**
     * @param array{headers?: array<non-empty-string, non-empty-string>, device?: array{architecture: string, deviceName: string|null, marketingName: string|null, manufacturer: string, brand: string, type: string, ismobile: bool, istv: bool, display: array{width: int|null, height: int|null, touch: bool|null, size: float|null}, dualOrientation: bool|null, simCount: int|null, bits: int}, client?: array{name: string|null, modus: string|null, version: string|null, manufacturer: string, type: string, isbot: bool, bits: int}, os?: array{name: string|null, marketingName: string|null, version: string|null, manufacturer: string, bits: int}, engine?: array{name: string|null, version: string|null, manufacturer: string}, date-first?: string, date-last?: string} $data
     * @param array<non-empty-string, non-empty-string>                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 $headers
     *
     * @throws void
     */
    private function buildResult(LoggerInterface $consoleLogger, array $data, array $headers): Result | null
    {
        try {
            $osVersion = $this->versionBuilder->set($data['os']['version'] ?? '');
        } catch (NotNumericException) {
            $osVersion = new NullVersion();
        }

        try {
            $clientVersion = $this->versionBuilder->set($data['client']['version'] ?? '');
        } catch (NotNumericException) {
            $clientVersion = new NullVersion();
        }

        try {
            $engineVersion = $this->versionBuilder->set($data['engine']['version'] ?? '');
        } catch (NotNumericException) {
            $engineVersion = new NullVersion();
        }

        try {
            $deviceManufacturer = $this->companyLoader->load($data['device']['manufacturer'] ?? '');
        } catch (NotFoundException) {
            $deviceManufacturer = Company::unknown;
        }

        try {
            $deviceBrand = $this->companyLoader->load($data['device']['brand'] ?? '');
        } catch (NotFoundException) {
            $deviceBrand = Company::unknown;
        }

        try {
            $osManufacturer = $this->companyLoader->load($data['os']['manufacturer'] ?? '');
        } catch (NotFoundException) {
            $osManufacturer = Company::unknown;
        }

        try {
            $clientManufacturer = $this->companyLoader->load($data['client']['manufacturer'] ?? '');
        } catch (NotFoundException) {
            $clientManufacturer = Company::unknown;
        }

        try {
            $engineManufacturer = $this->companyLoader->load($data['engine']['manufacturer'] ?? '');
        } catch (NotFoundException) {
            $engineManufacturer = Company::unknown;
        }

        try {
            $genericRequest = $this->requestBuilder->buildRequest($headers);
        } catch (UnexpectedValueException $e) {
            $consoleLogger->error($e);

            return null;
        }

        return new Result(
            headers: array_map(
                callback: static fn (HeaderInterface $header): string => $header->getValue(),
                array: $genericRequest->getHeaders(),
            ),
            device: new Device(
                architecture: Architecture::unknown,
                deviceName: $data['device']['deviceName'] ?? null,
                marketingName: $data['device']['marketingName'] ?? null,
                manufacturer: $deviceManufacturer,
                brand: $deviceBrand,
                type: DeviceType::fromName($data['device']['type'] ?? null),
                display: new Display(
                    width: $data['device']['display']['width'] ?? null,
                    height: $data['device']['display']['height'] ?? null,
                    touch: $data['device']['display']['touch'] ?? null,
                    size: $data['device']['display']['size'] ?? null,
                ),
                dualOrientation: $data['device']['dualOrientation'] ?? null,
                simCount: $data['device']['simCount'] ?? null,
                bits: Bits::tryFrom($data['device']['bits'] ?? 0) ?? Bits::unknown,
            ),
            os: new Os(
                name: $data['os']['name'] ?? null,
                marketingName: $data['os']['marketingName'] ?? null,
                manufacturer: $osManufacturer,
                version: $osVersion,
                bits: Bits::tryFrom($data['os']['bits'] ?? 0) ?? Bits::unknown,
            ),
            browser: new Browser(
                name: $data['client']['name'] ?? null,
                manufacturer: $clientManufacturer,
                version: $clientVersion,
                type: ClientType::fromName($data['client']['type'] ?? null),
                bits: Bits::tryFrom($data['client']['bits'] ?? 0) ?? Bits::unknown,
                modus: $data['client']['modus'] ?? null,
            ),
            engine: new Engine(
                name: $data['engine']['name'] ?? null,
                manufacturer: $engineManufacturer,
                version: $engineVersion,
            ),
        );
    }
}
