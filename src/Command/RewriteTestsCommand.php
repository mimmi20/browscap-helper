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

use BrowscapHelper\Entity\TestResult;
use BrowscapHelper\Helper\ExistingTestsLoader;
use BrowscapHelper\Helper\ExistingTestsRemover;
use BrowscapHelper\Helper\JsonNormalizer;
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
use BrowscapHelper\Traits\FilterHeaderTrait;
use BrowserDetector\Data\Company;
use BrowserDetector\Detector;
use BrowserDetector\DetectorFactory;
use BrowserDetector\Version\Exception\NotNumericException;
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
use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableSeparator;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Throwable;
use UaDataMapper\InputMapper;
use UaDeviceType\Type;
use UaResult\Device\FormFactor;
use UnexpectedValueException;

use function array_any;
use function array_chunk;
use function array_filter;
use function array_key_exists;
use function array_multisort;
use function assert;
use function file_exists;
use function file_put_contents;
use function implode;
use function in_array;
use function is_array;
use function is_scalar;
use function is_string;
use function json_decode;
use function json_encode;
use function max;
use function mb_str_pad;
use function mb_strtolower;
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
use function var_export;

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
final class RewriteTestsCommand extends Command
{
    use FilterHeaderTrait;

    private const int COMPARE_MATOMO_LOWER_VERSION = 9;

    private const int COMPARE_MAPPER_LOWER_VERSION = 12;

    /** @var array<string, int> */
    private array $tests = [];

    /** @throws LogicException */
    public function __construct(
        private readonly ExistingTestsLoader $testsLoader,
        private readonly ExistingTestsRemover $testsRemover,
        private readonly JsonNormalizer $jsonNormalizer,
    ) {
        parent::__construct();
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

        $logger   = new ConsoleLogger($output);
        $factory  = new DetectorFactory($detectorCache, $logger);
        $detector = $factory();

        $output->writeln(messages: 'init Matomo ...', options: OutputInterface::VERBOSITY_NORMAL);

        $dd = new DeviceDetector();

        $output->writeln(
            messages: 'removing old existing files from vendor ...',
            options: OutputInterface::VERBOSITY_NORMAL,
        );

        $basePath                = 'vendor/mimmi20/browser-detector/';
        $detectorTargetDirectory = $basePath . 'tests/data/';
        $testSource              = 'tests';

        $this->testsRemover->remove(output: $output, testSource: $detectorTargetDirectory);
        $this->testsRemover->remove(output: $output, testSource: $detectorTargetDirectory, dirs: true);

        $sources = [new JsonFileSource($testSource)];

        $output->writeln(
            messages: 'removing old existing files from .build ...',
            options: OutputInterface::VERBOSITY_NORMAL,
        );

        $this->testsRemover->remove(output: $output, testSource: '.build');
        $this->testsRemover->remove(output: $output, testSource: '.build', dirs: true);

        $output->writeln(
            messages: 'reading already existing tests ...',
            options: OutputInterface::VERBOSITY_NORMAL,
        );

        $txtChecks                  = [];
        $txtChecksOs                = [];
        $txtChecksFactor            = [];
        $messageLength              = 0;
        $counter                    = 0;
        $duplicates                 = 0;
        $errors                     = 0;
        $skipped                    = 0;
        $testCount                  = 0;
        $baseMessage                = 'checking Header ';
        $timeCheck                  = 0.0;
        $timeDetect                 = 0.0;
        $timeRead                   = 0.0;
        $timeWrite                  = 0.0;
        $counterDifferentFromMatomo = 0;
        $counterComparedWithMatomo  = 0;

        $clonedOutput = clone $output;
        $clonedOutput->setVerbosity(OutputInterface::VERBOSITY_QUIET);

        foreach ($this->testsLoader->getProperties($clonedOutput, $sources, $messageLength) as $test) {
            $this->handleTestCase(
                output: $output,
                detector: $detector,
                dd: $dd,
                test: $test,
                startTimeExec: $startTimeExec,
                baseMessage: $baseMessage,
                counter: $counter,
                skipped: $skipped,
                duplicates: $duplicates,
                errors: $errors,
                testCount: $testCount,
                messageLength: $messageLength,
                timeCheck: $timeCheck,
                timeDetect: $timeDetect,
                timeRead: $timeRead,
                timeWrite: $timeWrite,
                txtChecks: $txtChecks,
                txtChecksOs: $txtChecksOs,
                txtChecksFactor: $txtChecksFactor,
                counterDifferentFromMatomo: $counterDifferentFromMatomo,
                counterComparedWithMatomo: $counterComparedWithMatomo,
            );

            //            if ($counterDifferentFromMatomo >= 10) {
            //                exit;
            //            }
        }

        $messageLength = 0;

        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);

        $table = new Table($output);
        $table->setHeaders(['FormFactor', 'Counter']);
        $table->setRows([]);

        $cy = [];

        foreach ($txtChecksFactor as $factor => $factorCounter) {
            $cy[$factor] = $factorCounter;
        }

        array_multisort($cy, SORT_DESC, SORT_NUMERIC, $txtChecksFactor);

        foreach ($txtChecksFactor as $factor => $factorCounter) {
            $table->addRow(
                ['<info>' . $factor . '</info>', '<error>' . $factorCounter . '</error>'],
            );
        }

        $table->render();

        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        $cx = [];
        $cy = [];

        foreach ($txtChecksOs as $os => $versions) {
            $c = 0;
            $x = [];
            $y = [];

            foreach ($versions as $version => $counterVersions) {
                $c          += $counterVersions['count'];
                $x[$version] = $counterVersions['count'];
                $y[$version] = $version;
            }

            array_multisort($x, SORT_DESC, SORT_NUMERIC, $y, SORT_DESC, SORT_NUMERIC, $versions);

            $cx[$os]          = $c;
            $cy[$os]          = $os;
            $txtChecksOs[$os] = $versions;
        }

        array_multisort($cx, SORT_DESC, SORT_NUMERIC, $cy, SORT_ASC, SORT_STRING, $txtChecksOs);

        $table = new Table($output);
        $table->setHeaders(['OS', 'Version', 'Tests']);
        $table->setRows([]);

        foreach ($txtChecksOs as $os => $versions) {
            $c = 0;

            foreach ($versions as $version => $counterVersions) {
                $count     = $counterVersions['count'];
                $checkmark = $counterVersions['checked'] ?? false
                    ? ' <fg=green>+</>'
                    : ' <fg=red>-</>';
                $table->addRow([$os, $version, $count . $checkmark]);

                $c += $count;
            }

            $table->addRow(
                ['<info>' . $os . '</info>', '<info>summary</info>', '<error>' . $c . '</error>'],
            );
            $table->addRow(new TableSeparator());
        }

        $table->render();

        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);

        $this->jsonNormalizer->init($output);

        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(
            messages: sprintf(
                'time checking:      %s sec',
                mb_str_pad(number_format($timeCheck, 3, ',', '.'), 12, ' ', STR_PAD_LEFT),
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(
            messages: sprintf(
                'time detecting:     %s sec',
                mb_str_pad(number_format($timeDetect, 3, ',', '.'), 12, ' ', STR_PAD_LEFT),
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(
            messages: sprintf(
                'time reading cache: %s sec',
                mb_str_pad(number_format($timeRead, 3, ',', '.'), 12, ' ', STR_PAD_LEFT),
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(
            messages: sprintf(
                'time writing cache: %s sec',
                mb_str_pad(number_format($timeWrite, 3, ',', '.'), 12, ' ', STR_PAD_LEFT),
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(
            messages: mb_str_pad(
                number_format($counterDifferentFromMatomo, 0, ',', '.'),
                12,
                ' ',
                STR_PAD_LEFT,
            ) . ' Headers were detected, but different from Matomo',
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(
            messages: mb_str_pad(
                number_format($counterComparedWithMatomo, 0, ',', '.'),
                12,
                ' ',
                STR_PAD_LEFT,
            ) . ' Headers were compared with Matomo',
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(messages: 'rewrite tests ...', options: OutputInterface::VERBOSITY_NORMAL);

        $messageLength = 0;
        $baseMessage   = 're-write test files in directory ';

        $fileFinder = new Finder();
        $fileFinder->notName('*.gitkeep');
        $fileFinder->ignoreDotFiles(true);
        $fileFinder->ignoreVCS(true);
        $fileFinder->sortByName();
        $fileFinder->ignoreUnreadableDirs();
        $fileFinder->files();
        $fileFinder->in('.build');

        foreach ($fileFinder as $file) {
            try {
                $this->rewriteTests(
                    output: $output,
                    file: $file,
                    basePath: $basePath,
                    baseMessage: $baseMessage,
                    errors: $errors,
                    messageLength: $messageLength,
                );
            } catch (RuntimeException $e) {
                $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
                $output->writeln(
                    messages: '<error>' . $e . '</error>',
                    options: OutputInterface::VERBOSITY_NORMAL,
                );

                return 1;
            }
        }

        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);

        $dataToOutput = [
            'useragents processed' => $counter,
            'tests written' => $testCount,
            'skipped' => $skipped,
            'errors' => $errors,
            'duplicates' => $duplicates,
        ];

        foreach ($dataToOutput as $title => $number) {
            $output->writeln(
                messages: sprintf(
                    '%s%s',
                    mb_str_pad(
                        string: $title . ':',
                        length: 21,
                    ),
                    mb_str_pad(
                        string: number_format(num: $number, thousands_separator: '.'),
                        length: 14,
                        pad_type: STR_PAD_LEFT,
                    ),
                ),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
        }

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
        Detector $detector,
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
            $newResult = $detector->getBrowser($headers);
        } catch (InvalidArgumentException | UnexpectedValueException $e) {
            $output->writeln(
                messages: sprintf('<error>%s</error>', $e),
                options: OutputInterface::VERBOSITY_NORMAL,
            );

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

        if ($newResult['device']['deviceName'] === null) {
            return new TestResult(
                result: $newResult,
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_IS_NULL,
            );
        }

        if ($newResult['client']['name'] === null) {
            return new TestResult(
                result: $newResult,
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_CLIENT_IS_NULL,
            );
        }

        if (!is_scalar($newResult['device']['deviceName'])) {
            return new TestResult(
                result: $newResult,
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_NOT_SCALAR,
            );
        }

        if (!is_scalar($newResult['client']['name'])) {
            return new TestResult(
                result: null,
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_CLIENT_NOT_SCALAR,
            );
        }

        if (str_contains((string) $newResult['client']['name'], 'general')) {
            return new TestResult(
                result: $newResult,
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_CLIENT_IS_GENERAL,
            );
        }

        if ($newResult['client']['name'] === 'unknown') {
            return new TestResult(
                result: $newResult,
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_CLIENT_IS_UNKNOW,
            );
        }

        if (str_contains((string) $newResult['device']['deviceName'], 'general')) {
            return new TestResult(
                result: $newResult,
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_IS_GENERAL,
            );
        }

        if ($newResult['device']['deviceName'] === 'unknown') {
            return new TestResult(
                result: $newResult,
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_IS_UNKNOW,
            );
        }

        if (
            in_array(
                $newResult['client']['type'],
                ['bot', 'crawler', 'search-bot', 'service-agent', 'offline-browser'],
                true,
            )
        ) {
            assert(is_scalar($newResult['client']['name']));
            assert(is_scalar($newResult['os']['name']));
            assert(is_scalar($newResult['device']['deviceName']));

            $keys = [
                (string) $newResult['client']['name'],
                (string) $newResult['os']['name'],
                (string) $newResult['device']['deviceName'],
            ];

            $key = implode('-', $keys);

            if (array_key_exists($key, $this->tests)) {
                return new TestResult(
                    result: null,
                    status: TestResult::STATUS_DUPLICATE,
                    headers: $headers,
                    exit: TestResult::EXIT_CLIENT_IS_BOT,
                );
            }

            $this->tests[$key] = 1;

            return new TestResult(
                result: $newResult,
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_CLIENT_IS_BOT,
            );
        }

        if (
            in_array(
                $newResult['device']['deviceName'],
                ['general Desktop', 'general Apple Device', 'general Philips TV', 'PC', 'Macintosh', 'Linux Desktop', 'Windows Desktop'],
                true,
            )
        ) {
            assert(is_scalar($newResult['client']['name']));
            assert(is_scalar($newResult['os']['name']));
            assert(is_scalar($newResult['device']['deviceName']));
            assert(is_scalar($newResult['device']['manufacturer']));

            $keys = [
                'desktop',
                (string) $newResult['client']['name'],
                (string) $newResult['os']['name'],
                (string) $newResult['device']['deviceName'],
                (string) $newResult['device']['manufacturer'],
            ];

            $key = implode('-', $keys);

            if (array_key_exists($key, $this->tests)) {
                return new TestResult(
                    result: null,
                    status: TestResult::STATUS_DUPLICATE,
                    headers: $headers,
                    exit: TestResult::EXIT_DEVICE_IS_DESKTOP,
                );
            }

            $this->tests[$key] = 1;

            return new TestResult(
                result: $newResult,
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_IS_DESKTOP,
            );
        }

        $deviceType = Type::fromName($newResult['device']['type'] ?? 'unknown');

        if ($deviceType->isMobile() || $deviceType->isTablet()) {
            assert(is_scalar($newResult['engine']['name']));
            assert(is_scalar($newResult['os']['name']));
            assert(is_scalar($newResult['device']['deviceName']));
            assert(is_scalar($newResult['device']['manufacturer']));

            $keys = [
                'mobile',
                (string) $newResult['client']['name'],
                (string) $newResult['engine']['name'],
                (string) $newResult['os']['name'],
                (string) $newResult['device']['deviceName'],
                (string) $newResult['device']['manufacturer'],
            ];

            $key = implode('-', $keys);

            if (array_key_exists($key, $this->tests)) {
                return new TestResult(
                    result: null,
                    status: TestResult::STATUS_DUPLICATE,
                    headers: $headers,
                    exit: TestResult::EXIT_DEVICE_IS_MOBILE,
                );
            }

            $this->tests[$key] = 1;

            return new TestResult(
                result: $newResult,
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_IS_MOBILE,
            );
        }

        if ($deviceType->isTv()) {
            assert(is_scalar($newResult['device']['deviceName']));
            assert(is_scalar($newResult['device']['manufacturer']));
            assert(is_scalar($newResult['client']['name']));
            assert(is_scalar($newResult['os']['name']));

            $keys = [
                'tv',
                (string) $newResult['client']['name'],
                (string) $newResult['os']['name'],
                (string) $newResult['device']['deviceName'],
                (string) $newResult['device']['manufacturer'],
            ];

            $key = implode('-', $keys);

            if (array_key_exists($key, $this->tests)) {
                return new TestResult(
                    result: null,
                    status: TestResult::STATUS_DUPLICATE,
                    headers: $headers,
                    exit: TestResult::EXIT_DEVICE_IS_TV,
                );
            }

            $this->tests[$key] = 1;

            return new TestResult(
                result: $newResult,
                status: TestResult::STATUS_OK,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_IS_TV,
            );
        }

        assert(is_scalar($newResult['device']['deviceName']));
        assert(is_scalar($newResult['device']['manufacturer']));
        assert(is_scalar($newResult['client']['name']));
        assert(is_scalar($newResult['os']['name']));

        $keys = [
            'other',
            (string) $newResult['client']['name'],
            (string) $newResult['os']['name'],
            (string) $newResult['device']['deviceName'],
            (string) $newResult['device']['manufacturer'],
        ];

        $key = implode('-', $keys);

        if (array_key_exists($key, $this->tests)) {
            return new TestResult(
                result: null,
                status: TestResult::STATUS_DUPLICATE,
                headers: $headers,
                exit: TestResult::EXIT_DEVICE_IS_OTHER,
            );
        }

        $this->tests[$key] = 1;

        return new TestResult(
            result: $newResult,
            status: TestResult::STATUS_OK,
            headers: $headers,
            exit: TestResult::EXIT_DEVICE_IS_OTHER,
        );
    }

    /** @throws RuntimeException */
    private function rewriteTests(
        OutputInterface $output,
        SplFileInfo $file,
        string $basePath,
        string $baseMessage,
        int &$errors,
        int &$messageLength = 0,
    ): void {
        if (
            !preg_match(
                '/\.build\\\(?P<deviceManufaturer>[^\\\]+)\\\(?P<deviceType>[^\\\]+)\\\(?P<clientManufaturer>[^\\\]+)\\\(?P<clientType>[^\\\]+)\.json/',
                $file->getPathname(),
                $matches,
            )
        ) {
            ++$errors;

            $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
            $output->writeln(
                messages: sprintf(
                    '<error>the path "%s" does not match required structure</error>',
                    $file->getPathname(),
                ),
                options: OutputInterface::VERBOSITY_NORMAL,
            );

            return;
        }

        try {
            $data = json_decode($file->getContents(), false, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            ++$errors;

            $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
            $output->writeln(
                messages: '<error>' . (new Exception(
                    'An error occured while encoding a resultset',
                    0,
                    $e,
                )) . '</error>',
                options: OutputInterface::VERBOSITY_NORMAL,
            );

            return;
        }

        foreach (array_chunk($data, 100) as $number => $parts) {
            $this->rewriteFile(
                output: $output,
                basePath: $basePath,
                baseMessage: $baseMessage,
                matches: $matches,
                parts: $parts,
                number: $number,
                errors: $errors,
                messageLength: $messageLength,
            );
        }
    }

    /**
     * @param array<int|string, string> $matches
     * @param array<int|string, string> $parts
     *
     * @throws RuntimeException
     */
    private function rewriteFile(
        OutputInterface $output,
        string $basePath,
        string $baseMessage,
        array $matches,
        array $parts,
        int $number,
        int &$errors,
        int &$messageLength,
    ): void {
        $path  = $basePath;
        $path .= sprintf(
            'tests/data/%s/%s/%s/%s/%07d.json',
            $matches['deviceManufaturer'],
            $matches['deviceType'],
            $matches['clientManufaturer'],
            $matches['clientType'],
            $number,
        );

        $p1 = sprintf('tests/data/%s', $matches['deviceManufaturer']);

        if (!file_exists($basePath . $p1)) {
            mkdir($basePath . $p1);
        }

        $p2 = sprintf('tests/data/%s/%s', $matches['deviceManufaturer'], $matches['deviceType']);

        if (!file_exists($basePath . $p2)) {
            mkdir($basePath . $p2);
        }

        $p3 = sprintf(
            'tests/data/%s/%s/%s',
            $matches['deviceManufaturer'],
            $matches['deviceType'],
            $matches['clientManufaturer'],
        );

        if (!file_exists($basePath . $p3)) {
            mkdir($basePath . $p3);
        }

        $p4 = sprintf(
            'tests/data/%s/%s/%s/%s',
            $matches['deviceManufaturer'],
            $matches['deviceType'],
            $matches['clientManufaturer'],
            $matches['clientType'],
        );

        if (!file_exists($basePath . $p4)) {
            mkdir($basePath . $p4);
        }

        $message  = $baseMessage;
        $message .= sprintf(
            'tests/data/%s/%s/%s/%s/%07d.json',
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

        try {
            $normalized = $this->jsonNormalizer->normalize($output, $parts, $message, $messageLength);
        } catch (RuntimeException $e) {
            $output->writeln(messages: '', options: OutputInterface::VERBOSITY_VERBOSE);
            $output->writeln(
                messages: '<error>' . $e . '</error>',
                options: OutputInterface::VERBOSITY_NORMAL,
            );

            return;
        }

        if ($normalized === null) {
            throw new RuntimeException(sprintf('file "%s" contains invalid json', $path));
        }

        $message  = $baseMessage;
        $message .= sprintf(
            'tests/data/%s/%s/%s/%s/%07d.json',
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

        ++$errors;
        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(
            messages: '<error>' . sprintf(
                'An error occured while writing file %s',
                $path,
            ) . '</error>',
            options: OutputInterface::VERBOSITY_NORMAL,
        );
    }

    /**
     * @param array{headers: array<string, string>}                           $test
     * @param array<string, array<mixed>>                                     $txtChecks
     * @param array<string, array<string, array{count: int, checked?: bool}>> $txtChecksOs
     * @param array<string, int>                                              $txtChecksFactor
     *
     * @throws void
     *
     * @phpcs:disable SlevomatCodingStandard.Functions.FunctionLength.FunctionLength
     */
    private function handleTestCase(
        OutputInterface $output,
        Detector $detector,
        DeviceDetector $dd,
        array $test,
        DateTimeImmutable $startTimeExec,
        string $baseMessage,
        int &$counter,
        int &$skipped,
        int &$duplicates,
        int &$errors,
        int &$testCount,
        int &$messageLength,
        float &$timeCheck,
        float &$timeDetect,
        float &$timeRead,
        float &$timeWrite,
        array &$txtChecks,
        array &$txtChecksOs,
        array &$txtChecksFactor,
        int &$counterDifferentFromMatomo,
        int &$counterComparedWithMatomo,
    ): void {
        $test['headers'] = $this->filterHeaders($output, $test['headers']);

        $startTime = microtime(true);

        $seachHeader = (string) UserAgent::fromHeaderArray($test['headers']);

        ++$counter;

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
                string: number_format(num: memory_get_usage(true), thousands_separator: '.') . 'B',
                length: 16,
                pad_type: STR_PAD_LEFT,
            ),
            mb_str_pad(
                string: number_format(num: memory_get_peak_usage(true), thousands_separator: '.') . 'B',
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
        $output->writeln(sprintf(' <bg=red>%d</>', $messageLength), OutputInterface::VERBOSITY_DEBUG);

        $timeCheck += microtime(true) - $startTime;

        if (array_key_exists($seachHeader, $txtChecks)) {
            ++$skipped;

            return;
        }

        $txtChecks[$seachHeader] = $test;

        // if (
        //    !array_key_exists('x-requested-with', $test['headers'])
        //    && !array_key_exists('http-x-requested-with', $test['headers'])
        // ) {
        //    ++$skipped;
        //
        //    return;
        // }
        //
        // if (
        //    !array_key_exists('sec-ch-ua-platform', $test['headers'])
        // ) {
        //    ++$skipped;
        //
        //    return;
        // }
        //
        // if (
        //    !array_key_exists('sec-ch-ua-model', $test['headers'])
        // ) {
        //    ++$skipped;
        //
        //    return;
        // }
        //
        // if (
        //    !array_key_exists('x-puffin-ua', $test['headers'])
        // ) {
        //    ++$skipped;
        //
        //    return;
        // }

        if (array_key_exists('sec-ch-ua-form-factors', $test['headers'])) {
            $matches = [];

            if (
                preg_match_all(
                    '~["\']([a-z]+)["\']~i',
                    $test['headers']['sec-ch-ua-form-factors'],
                    $matches,
                )
            ) {
                foreach ($matches[1] as $factor) {
                    if (!array_key_exists($factor, $txtChecksFactor)) {
                        $txtChecksFactor[$factor] = 1;
                    } else {
                        ++$txtChecksFactor[$factor];
                    }

                    $detectedFactor = FormFactor::tryFrom($factor);

                    if ($detectedFactor === null) {
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
        }

        if (
            array_key_exists('x-requested-with', $test['headers'])
            && array_key_exists('http-x-requested-with', $test['headers'])
        ) {
            $message = $loopMessage . '<error>"x-requested-with" header is available twice</error>';
            $diff    = $this->messageLength($output, $message, $messageLength);

            $output->writeln(
                messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
        }

        $xRequestHeader = null;

        if (
            array_key_exists('x-requested-with', $test['headers'])
            && $test['headers']['x-requested-with'] !== ''
        ) {
            $xRequestHeader = $test['headers']['x-requested-with'];
        } elseif (
            array_key_exists('http-x-requested-with', $test['headers'])
            && $test['headers']['http-x-requested-with'] !== ''
        ) {
            $xRequestHeader = $test['headers']['http-x-requested-with'];
        }

        $secChUaHeader = null;

        if (array_key_exists('sec-ch-ua', $test['headers']) && $test['headers']['sec-ch-ua'] !== '') {
            $secChUaHeader = $test['headers']['sec-ch-ua'];
        }

        $secChPlatformHeader = null;

        if (
            array_key_exists('sec-ch-ua-platform', $test['headers'])
            && $test['headers']['sec-ch-ua-platform'] !== ''
        ) {
            $secChPlatformHeader = $test['headers']['sec-ch-ua-platform'];
        }

        $secChModelHeader = null;

        if (
            array_key_exists('sec-ch-ua-model', $test['headers'])
            && $test['headers']['sec-ch-ua-model'] !== ''
        ) {
            $secChModelHeader = $test['headers']['sec-ch-ua-model'];
        }

        $puffinHeader = null;

        if (
            array_key_exists('x-puffin-ua', $test['headers'])
            && $test['headers']['x-puffin-ua'] !== ''
        ) {
            $puffinHeader = $test['headers']['x-puffin-ua'];
        }

        $message = $loopMessage . 'redetect';
        $diff    = $this->messageLength($output, $message, $messageLength);

        $output->write(
            messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(sprintf(' <bg=red>%d</>', $messageLength), OutputInterface::VERBOSITY_DEBUG);

        $filteredHeaders = array_filter(
            $test['headers'],
            static fn (string $v): bool => $v !== '',
        );

        $forbiddenFound = array_any(
            $filteredHeaders,
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
                    || str_contains($v, '<a ')
                    || str_contains($v, 'file_put_contents(')
                    || str_contains($v, 'file_get_contents(')
                    || str_contains($v, 'fromcharcode(');
            },
        );

        if ($forbiddenFound) {
            ++$skipped;

            return;
        }

        $startTime = microtime(true);

        $testResult = $this->handleTest(
            output: $output,
            detector: $detector,
            headers: $filteredHeaders,
            parentMessage: $message,
            messageLength: $messageLength,
        );

        $timeDetect += microtime(true) - $startTime;

        $result = $testResult->getResult();

        if ($testResult->getStatus() === TestResult::STATUS_SKIPPED) {
            ++$skipped;

            return;
        }

        if ($testResult->getStatus() === TestResult::STATUS_DUPLICATE) {
            ++$duplicates;

            return;
        }

        if ($testResult->getStatus() === TestResult::STATUS_ERROR || $result === null) {
            ++$errors;

            return;
        }

        $headers = $testResult->getHeaders();

        if (
            in_array(
                $testResult->getExit(),
                [TestResult::EXIT_DEVICE_IS_NULL, TestResult::EXIT_DEVICE_NOT_SCALAR, TestResult::EXIT_DEVICE_IS_GENERAL, TestResult::EXIT_DEVICE_IS_UNKNOW],
                true,
            )
        ) {
            if (
                !empty($result['os']['name'])
                && is_string($result['os']['name'])
                && is_scalar($result['os']['version'])
            ) {
                try {
                    $version = (new VersionBuilder())->set((string) $result['os']['version']);
                } catch (NotNumericException $e) {
                    ++$errors;

                    $exception = new Exception('An error occured while decoding a result', 0, $e);

                    $addMessage = sprintf('<error>%s</error>', (string) $exception);

                    $message = $loopMessage . $addMessage;

                    $output->writeln(
                        messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                        options: OutputInterface::VERBOSITY_NORMAL,
                    );

                    return;
                }

                if (in_array(mb_strtolower($result['os']['name']), ['android', 'ios'], true)) {
                    if ((int) $version->getMajor() < self::COMPARE_MATOMO_LOWER_VERSION) {
                        ++$skipped;

                        return;
                    }
                }
            }
        }

        $compareWithMapper = false;

        $osName  = mb_strtolower($result['os']['name'] ?? '');
        $version = null;

        if (is_scalar($result['os']['version'])) {
            try {
                $version = (new VersionBuilder())->set((string) $result['os']['version']);
            } catch (NotNumericException $e) {
                ++$errors;

                $exception = new Exception('An error occured while decoding a result', 0, $e);

                $addMessage = sprintf('<error>%s</error>', (string) $exception);

                $message = $loopMessage . $addMessage;

                $output->writeln(
                    messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );

                return;
            }

            try {
                $osVersion = $version->getVersion(VersionInterface::IGNORE_MICRO) ?? '-';
            } catch (UnexpectedValueException) {
                $osVersion = 'e';
            }
        } else {
            $osVersion = '-';
        }

        if (!isset($txtChecksOs[$osName][$osVersion])) {
            $txtChecksOs[$osName][$osVersion]['count']   = 1;
            $txtChecksOs[$osName][$osVersion]['checked'] = false;
        } else {
            ++$txtChecksOs[$osName][$osVersion]['count'];
        }

        if ($result['os']['name'] === null && $secChPlatformHeader !== null) {
            $compareWithMapper = true;
        } elseif (
            $result['client']['name'] === null
            && (
                $secChUaHeader !== null
                || (
                    $xRequestHeader !== null
                    && $xRequestHeader !== 'XMLHttpRequest'
                )
            )
        ) {
            $compareWithMapper = true;
        } elseif (
            $result['device']['deviceName'] === null
            && (
                $secChModelHeader !== null
                || $puffinHeader !== null
            )
        ) {
            $compareWithMapper = true;
        } elseif (!empty($result['os']['name']) && is_string($result['os']['name'])) {
            if (
                in_array(
                    mb_strtolower($result['os']['name']),
                    ['android', 'ios', 'ipados', 'android tv', 'cyanogenmod', 'miui os', 'iphone os', 'mocordroid', 'mre'],
                    true,
                )
            ) {
                if (
                    $version instanceof VersionInterface
                    && (int) $version->getMajor() >= self::COMPARE_MAPPER_LOWER_VERSION
                ) {
                    $compareWithMapper                           = true;
                    $txtChecksOs[$osName][$osVersion]['checked'] = true;
                }
            } elseif (
                in_array(
                    mb_strtolower($result['os']['name']),
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
                        'chinese operating system',
                        'nextstep',
                        'windows iot',
                        'ultrix',
                        'genix',
                        'news-os',
                        'turbolinux',
                        'backtrack linux',
                        'ark linux',
                        'blackpanther os',
                        'aros',
                        'zenwalk gnu linux',
                        'azure linux',
                        'wyderos',
                        'opensolaris',
                        'startos',
                        'ventana linux',
                        'joli os',
                        'debian with freebsd kernel',
                        'liberate',
                        'moblin',
                        'raspbian',
                        'rim tablet os',
                        'blackberry tablet os',
                        'morphos',
                        'mandriva linux',
                        'linux mint',
                        'inferno os',
                        'haiku',
                        'archlinux',
                        'cent os linux',
                        'orbis os',
                        'cellos',
                        'nintendo os',
                        'beos',
                        'chromeos',
                        'gentoo linux',
                        'kubuntu',
                        'slackware linux',
                        'redhat linux',
                        'solaris',
                        'syllable',
                        'suse linux',
                        'kin os',
                        'threadx',
                        'sailfishos',
                        'remix os',
                        'meego',
                        'palmos',
                        'risc os',
                        'hp-ux',
                        'pardus',
                        'danger os',
                        'lindowsos',
                        'nintendo switch os',
                        'tvos',
                        'windows 3.11',
                        'series 30',
                        'nintendo wii os',
                        'windows 2003',
                        'windows rt',
                        'tru64 unix',
                        'cp/m',
                        'cygwin',
                        'openvms',
                        'wear os',
                        'dragonfly bsd',
                        'darwin',
                        'bsd',
                        'watchos',
                        'windows 3.1',
                        'aix',
                        'macintosh',
                        'fedora linux',
                        'yun os',
                        'firefox os',
                        'windows 95',
                        'debian',
                        'irix',
                        'windows 98',
                        'openharmony',
                        'osf/1',
                        'haiku os',
                        'opensuse',
                        'linspire',
                        'android opensource project',
                        'maemo',
                        'bada',
                        'plasma mobile',
                        'series 40',
                        'nucleus os',
                        'amiga os',
                        // 'kaios',
                        // 'vizios',
                    ],
                    true,
                )
            ) {
                $compareWithMapper                           = true;
                $txtChecksOs[$osName][$osVersion]['checked'] = true;
            }
        }

        if ($compareWithMapper) {
            $this->compareDeviceWithMapper(
                output: $output,
                dd: $dd,
                result: $result,
                headers: $headers,
                loopMessage: $loopMessage,
                messageLength: $messageLength,
                counterDifferentFromMatomo: $counterDifferentFromMatomo,
            );

            ++$counterComparedWithMatomo;
        }

        $deviceManufaturer = mb_strtolower(
            str_replace([' ', 'ü'], ['-', 'ue'], $result['device']['manufacturer'] ?? ''),
        );
        $deviceManufaturer = $deviceManufaturer === '' ? 'unknown' : str_replace(
            ['.', ' '],
            ['', '-'],
            $deviceManufaturer,
        );

        $clientManufaturer = mb_strtolower(
            str_replace([' ', 'ü'], ['-', 'ue'], $result['client']['manufacturer'] ?? ''),
        );
        $clientManufaturer = $clientManufaturer === '' ? 'unknown' : str_replace(
            ['.', ' '],
            ['', '-'],
            $clientManufaturer,
        );

        $deviceType = mb_strtolower($result['device']['type'] ?? 'unknown');
        $clientType = mb_strtolower($result['client']['type'] ?? 'unknown');

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

            return;
        }

        $addMessage = sprintf('write to temporary file %s - <info>done</info>', $file);

        unset($file);

        $message = $loopMessage . $addMessage;
        $diff    = $this->messageLength($output, $message, $messageLength);

        $output->write(
            messages: "\r" . mb_str_pad(string: $message, length: $messageLength + $diff),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(sprintf(' <bg=red>%d</>', $messageLength), OutputInterface::VERBOSITY_DEBUG);

        ++$testCount;
    }

    /**
     * @param array<int|string, mixed> $result
     *
     * @throws void
     */
    private function saveResult(
        OutputInterface $output,
        array $result,
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

            $startTime = microtime(true);

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
                $timeRead += microtime(true) - $startTime;
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

        $tests[] = $result;

        $startTime = microtime(true);

        try {
            $saved = file_put_contents(
                filename: $file,
                data: json_encode(
                    $tests,
                    JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR,
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
            $timeWrite += microtime(true) - $startTime;

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
     * @param array<int|string, mixed> $result
     * @param array<string, string>    $headers
     *
     * @throws void
     */
    private function compareDeviceWithMapper(
        OutputInterface $output,
        DeviceDetector $dd,
        array $result,
        array $headers,
        string $loopMessage,
        int &$messageLength,
        int &$counterDifferentFromMatomo,
    ): void {
        $mapper = new InputMapper();

        $clientHints = ClientHints::factory($headers);

        $dd->setUserAgent($headers['user-agent'] ?? '');
        $dd->setClientHints($clientHints);
        $dd->parse();

        $ddDeviceType = $mapper->mapDeviceType($dd->getDeviceName());

        if ($dd->getDeviceName() !== '' && $ddDeviceType === Type::Unknown) {
            $output->writeln(
                messages: "\n" . mb_str_pad(
                    string: sprintf(
                        'The device type "<fg=magenta>%s (%s)</>" from Matomo for user-agent Header "%s" was not found in the Enum. Please add it.',
                        $dd->getDeviceName(),
                        $ddDeviceType->getType(),
                        $headers['user-agent'] ?? '',
                    ),
                    length: $messageLength,
                ),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            Company::fromName($result['device']['brand'] ?? null);
        } catch (UnexpectedValueException) {
            $output->writeln(
                messages: "\n" . mb_str_pad(
                    string: sprintf(
                        'The company "<fg=blue>%s</>", was not found in the Enum. Please add it',
                        $result['device']['brand'] ?? '',
                    ),
                    length: $messageLength,
                ),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            Company::fromName($result['device']['manufacturer'] ?? null);
        } catch (UnexpectedValueException) {
            $output->writeln(
                messages: "\n" . mb_str_pad(
                    string: sprintf(
                        'The company "<fg=blue>%s</>", was not found in the Enum. Please add it',
                        $result['device']['manufacturer'] ?? '',
                    ),
                    length: $messageLength,
                ),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            Company::fromName($result['os']['manufacturer'] ?? null);
        } catch (UnexpectedValueException) {
            $output->writeln(
                messages: "\n" . mb_str_pad(
                    string: sprintf(
                        'The company "<fg=blue>%s</>", was not found in the Enum. Please add it',
                        $result['os']['manufacturer'] ?? '',
                    ),
                    length: $messageLength,
                ),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            Company::fromName($result['engine']['manufacturer'] ?? null);
        } catch (UnexpectedValueException) {
            $output->writeln(
                messages: "\n" . mb_str_pad(
                    string: sprintf(
                        'The company "<fg=blue>%s</>", was not found in the Enum. Please add it',
                        $result['engine']['manufacturer'] ?? '',
                    ),
                    length: $messageLength,
                ),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
        }

        try {
            Company::fromName($result['client']['manufacturer'] ?? null);
        } catch (UnexpectedValueException) {
            $output->writeln(
                messages: "\n" . mb_str_pad(
                    string: sprintf(
                        'The company "<fg=blue>%s</>", was not found in the Enum. Please add it',
                        $result['client']['manufacturer'] ?? '',
                    ),
                    length: $messageLength,
                ),
                options: OutputInterface::VERBOSITY_NORMAL,
            );
        }

        $resultTypeName = $result['device']['type'] ?? '';

        if ($resultTypeName !== 'unknown') {
            $resultType = Type::fromName($resultTypeName);

            if ($resultType === Type::Unknown) {
                $output->writeln(
                    messages: "\n" . mb_str_pad(
                        string: sprintf(
                            'The device type "<fg=magenta>%s</>" for user-agent Header "%s" was not found in the Enum. Please add it.',
                            $result['device']['type'] ?? '',
                            $headers['user-agent'] ?? '',
                        ),
                        length: $messageLength,
                    ),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );
            }

            if ($resultType->hasTouch() && ($result['device']['display']['touch'] ?? null) !== true) {
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

            if (!$resultType->hasTouch() && ($result['device']['display']['touch'] ?? null) === true) {
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

        $ddModel = $mapper->mapDeviceMarketingName($dd->getModel());
        $ddBrand = $mapper->mapDeviceBrandName($dd->getBrandName());

        $isBot      = $dd->isBot();
        $osInfo     = $dd->getOs();
        $clientInfo = $dd->getClient();
        $botInfo    = $dd->getBot();

        $ddOsName = $mapper->mapOsName($osInfo['name'] ?? null);

        try {
            $ddOsVersion = $mapper->mapOsVersion($osInfo['version'] ?? null, $ddOsName);
        } catch (NotNumericException $e) {
            $output->writeln(sprintf('<error>%s</error>', (string) $e));

            return;
        }

        $ddEngineName = $mapper->mapEngineName($isBot ? null : ($clientInfo['engine'] ?? null));

        try {
            $ddEngineVersion = $mapper->mapEngineVersion(
                $isBot ? null : ($clientInfo['engine_version'] ?? null),
            );
        } catch (NotNumericException $e) {
            $output->writeln(sprintf('<error>%s</error>', (string) $e));

            return;
        }

        $ddClientName = $mapper->mapBrowserName(
            $isBot ? ($botInfo['name'] ?? null) : ($clientInfo['name'] ?? null),
        );

        try {
            $ddClientVersion = $mapper->mapBrowserVersion(
                $isBot ? null : ($clientInfo['version'] ?? null),
            );
        } catch (NotNumericException $e) {
            $output->writeln(sprintf('<error>%s</error>', (string) $e));

            return;
        }

        $ddClientType = $mapper->mapBrowserType(
            $isBot ? ($botInfo['category'] ?? null) : ($clientInfo['type'] ?? null),
        );

        $brModel      = $mapper->mapDeviceName($result['device']['deviceName'] ?? null);
        $brModel2     = $mapper->mapDeviceMarketingName($result['device']['marketingName'] ?? null);
        $brBrand      = $mapper->mapDeviceBrandName($result['device']['brand'] ?? null);
        $brDeviceType = $mapper->mapDeviceType($result['device']['type'] ?? null);
        $brOsName     = $mapper->mapOsName($result['os']['name'] ?? null);

        try {
            $brOsVersion = $mapper->mapOsVersion($result['os']['version'] ?? null, $brOsName);
        } catch (NotNumericException $e) {
            $output->writeln(sprintf('<error>%s</error>', (string) $e));

            return;
        }

        $brEngineName = $mapper->mapEngineName($result['engine']['name'] ?? null);

        try {
            $brEngineVersion = $mapper->mapEngineVersion($result['engine']['version'] ?? null);
        } catch (NotNumericException $e) {
            $output->writeln(sprintf('<error>%s</error>', (string) $e));

            return;
        }

        $brClientName = $mapper->mapBrowserName($result['client']['name'] ?? null);

        try {
            $brClientVersion = $mapper->mapBrowserVersion($result['client']['version'] ?? null);
        } catch (NotNumericException $e) {
            $output->writeln(sprintf('<error>%s</error>', (string) $e));

            return;
        }

        $brClientType = $mapper->mapBrowserType($result['client']['type'] ?? null);

        try {
            $checks = [
                '($ddBrand === $brBrand || $ddBrand === null)' => ($ddBrand === $brBrand || $ddBrand === null),
                '($ddModel === $brModel || $ddModel === $brModel2 || $ddModel === null || $ddModel === \'K\')' => ($ddModel === $brModel || $ddModel === $brModel2 || $ddModel === null || $ddModel === 'K'),
                '$ddDeviceType === $brDeviceType || $ddDeviceType === Type::Unknown' => $ddDeviceType === $brDeviceType || $ddDeviceType === Type::Unknown,
                '($ddOsName === $brOsName || $ddOsName === null)' => ($ddOsName === $brOsName || $ddOsName === null),
                '($ddOsVersion->getVersion(VersionInterface::IGNORE_MICRO) === $brOsVersion->getVersion(VersionInterface::IGNORE_MICRO) || $ddOsVersion->getVersion(VersionInterface::IGNORE_MICRO) === null)' => ($ddOsVersion->getVersion(
                    VersionInterface::IGNORE_MICRO,
                ) === $brOsVersion->getVersion(
                    VersionInterface::IGNORE_MICRO,
                ) || $ddOsVersion->getVersion(VersionInterface::IGNORE_MICRO) === null),
                '($ddEngineName === $brEngineName || $ddEngineName === null)' => ($ddEngineName === $brEngineName || $ddEngineName === null),
                '($ddEngineVersion->getVersion(VersionInterface::IGNORE_MICRO) === $brEngineVersion->getVersion(VersionInterface::IGNORE_MICRO) || $ddEngineVersion->getVersion(VersionInterface::IGNORE_MICRO) === null)' => ($ddEngineVersion->getVersion(
                    VersionInterface::IGNORE_MICRO,
                ) === $brEngineVersion->getVersion(
                    VersionInterface::IGNORE_MICRO,
                ) || $ddEngineVersion->getVersion(VersionInterface::IGNORE_MICRO) === null),
                '($ddClientName === $brClientName || $ddClientName === null)' => ($ddClientName === $brClientName || $ddClientName === null),
                '($ddClientVersion->getVersion(VersionInterface::IGNORE_MINOR) === $brClientVersion->getVersion(VersionInterface::IGNORE_MINOR) || $ddClientVersion->getVersion(VersionInterface::IGNORE_MINOR) === null)' => ($ddClientVersion->getVersion(
                    VersionInterface::IGNORE_MINOR,
                ) === $brClientVersion->getVersion(
                    VersionInterface::IGNORE_MINOR,
                ) || $ddClientVersion->getVersion(VersionInterface::IGNORE_MINOR) === null),
                '($ddClientType === $brClientType || $ddClientType === \UaBrowserType\Type::Unknown)' => ($ddClientType === $brClientType || $ddClientType === \UaBrowserType\Type::Unknown),
            ];
        } catch (Throwable $e) {
            $output->writeln(sprintf('<error>%s</error>', (string) $e));

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

        $getMessage = function (DeviceDetector $dd) use ($output, $checks, $loopMessage, $result, $headers, $ddModel, $ddBrand, $ddDeviceType, $isBot, $osInfo, $clientInfo, $botInfo, $ddOsName, $ddOsVersion, $ddEngineName, $ddEngineVersion, $ddClientName, $ddClientVersion, $ddClientType, $brModel, $brModel2, $brBrand, $brDeviceType, $brOsName, $brOsVersion, $brEngineName, $brEngineVersion, $brClientName, $brClientVersion, $brClientType): string {
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

            if ($ddBrand !== $brBrand && $ddBrand !== null) {
                $format1b       = '<fg=green>';
                $format1d       = '<fg=red>';
                $someDifference = true;
            }

            if ($ddModel !== $brModel && $ddModel !== $brModel2 && $ddModel !== null) {
                $format2b       = '<fg=green>';
                $format2d       = '<fg=red>';
                $someDifference = true;
            }

            if (
                $ddDeviceType->getType() !== $brDeviceType->getType()
                && $ddDeviceType !== Type::Unknown
            ) {
                $format3b       = '<fg=green>';
                $format3d       = '<fg=red>';
                $someDifference = true;
            }

            if ($ddOsName !== $brOsName && $ddOsName !== null) {
                $format4b       = '<fg=green>';
                $format4d       = '<fg=red>';
                $someDifference = true;
            }

            if (
                $ddOsVersion->getVersion(VersionInterface::IGNORE_MICRO)
                !== $brOsVersion->getVersion(VersionInterface::IGNORE_MICRO)
                && $ddOsVersion->getVersion(VersionInterface::IGNORE_MICRO) !== null
            ) {
                $format5b       = '<fg=green>';
                $format5d       = '<fg=red>';
                $someDifference = true;
            }

            if ($ddEngineName !== $brEngineName && $ddEngineName !== null) {
                $format6b       = '<fg=green>';
                $format6d       = '<fg=red>';
                $someDifference = true;
            }

            if (
                $ddEngineVersion->getVersion(VersionInterface::IGNORE_MICRO)
                !== $brEngineVersion->getVersion(VersionInterface::IGNORE_MICRO)
                && $ddEngineVersion->getVersion(VersionInterface::IGNORE_MICRO) !== null
            ) {
                $format7b       = '<fg=green>';
                $format7d       = '<fg=red>';
                $someDifference = true;
            }

            if ($ddClientName !== $brClientName && $ddClientName !== null) {
                $format8b       = '<fg=green>';
                $format8d       = '<fg=red>';
                $someDifference = true;
            }

            if (
                $ddClientVersion->getVersion(VersionInterface::IGNORE_MINOR)
                !== $brClientVersion->getVersion(VersionInterface::IGNORE_MINOR)
                && $ddClientVersion->getVersion(VersionInterface::IGNORE_MINOR) !== null
            ) {
                $format9b       = '<fg=green>';
                $format9d       = '<fg=red>';
                $someDifference = true;
            }

            if ($ddClientType !== $brClientType && $ddClientType !== \UaBrowserType\Type::Unknown) {
                $format10b      = '<fg=green>';
                $format10d      = '<fg=red>';
                $someDifference = true;
            }

            if (!$someDifference) {
                $output->writeln('');
                $output->writeln(sprintf('<error>%s</error>', var_export($checks, true)));
                $output->writeln('');
            }

            $headerList = $this->getHeaderList($headers);

            return $message . PHP_EOL . sprintf(
                '    For the Headers' . PHP_EOL . '%s' . PHP_EOL
                . '    the device was detected as    "%s%s -> %s</>" "%s%s/%s -> %s/%s</>" (%s%s -> %s</>), ' . PHP_EOL
                . '        but Matomo detected it as "%s%s -> %s</>" "%s%s -> %s</>" (%s%s -> %s</>)' . PHP_EOL
                . '    the platform was detected as  "%s%s -> %s</>" "%s%s -> %s</>", ' . PHP_EOL
                . '        but Matomo detected it as "%s%s -> %s</>" "%s%s -> %s</>"' . PHP_EOL
                . '    the engine was detected as    "%s%s -> %s</>" "%s%s -> %s</>", ' . PHP_EOL
                . '        but Matomo detected it as "%s%s -> %s</>" "%s%s -> %s</>"' . PHP_EOL
                . '    the client was detected as    "%s%s -> %s</>" "%s%s -> %s</>" (%s%s -> %s</>), ' . PHP_EOL
                . '        but Matomo detected it as "%s%s -> %s</>" "%s%s -> %s</>" (%s%s -> %s</>)',
                implode(PHP_EOL, $headerList),
                $format1b,
                $result['device']['brand'] ?? '<n/a>',
                $brBrand,
                $format2b,
                $result['device']['deviceName'] ?? '<n/a>',
                $result['device']['marketingName'] ?? '<n/a>',
                $brModel,
                $brModel2,
                $format3b,
                $result['device']['type'] ?? '<n/a>',
                $brDeviceType->getType(),
                $format1d,
                $dd->getBrandName(),
                $ddBrand,
                $format2d,
                $dd->getModel(),
                $ddModel,
                $format3d,
                $dd->getDeviceName(),
                $ddDeviceType->getType(),
                $format4b,
                $result['os']['name'] ?? '<n/a>',
                $brOsName,
                $format5b,
                $result['os']['version'] ?? '<n/a>',
                $brOsVersion->getVersion(),
                $format4d,
                $osInfo['name'] ?? '',
                $ddOsName,
                $format5d,
                $osInfo['version'] ?? '',
                $ddOsVersion->getVersion(),
                $format6b,
                $result['engine']['name'] ?? '',
                $brEngineName,
                $format7b,
                $result['engine']['version'] ?? '',
                $brEngineVersion->getVersion(),
                $format6d,
                $isBot ? '' : ($clientInfo['engine'] ?? ''),
                $ddEngineName,
                $format7d,
                $isBot ? '' : ($clientInfo['engine_version'] ?? ''),
                $ddEngineVersion->getVersion(),
                $format8b,
                $result['client']['name'] ?? '',
                $brClientName,
                $format9b,
                $result['client']['version'] ?? '',
                $brClientVersion->getVersion(),
                $format10b,
                $result['client']['type'] ?? '',
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
            $message = $getMessage($dd);
        } catch (UnexpectedValueException $e) {
            $output->writeln(sprintf('<error>%s</error>', (string) $e));

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
        $headerList = ['        "user-agent" => "' . $headers['user-agent'] . '"'];

        foreach ($headers as $name => $value) {
            if ($name === 'user-agent') {
                continue;
            }

            $headerList[] = '        "' . $name . '" => "' . $value . '"';
        }

        return $headerList;
    }
}
