<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2024, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Command;

use BrowscapHelper\Helper\ExistingTestsLoader;
use BrowscapHelper\Helper\ExistingTestsRemover;
use BrowscapHelper\Helper\JsonNormalizer;
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
use BrowserDetector\Detector;
use BrowserDetector\DetectorFactory;
use BrowserDetector\Version\Exception\NotNumericException;
use DateInterval;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentSize;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentStyle;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptions;
use Ergebnis\Json\Normalizer\Exception\InvalidNewLineString;
use Exception;
use InvalidArgumentException;
use JsonException;
use Override;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use Throwable;
use UaDeviceType\Exception\NotFoundException;
use UaDeviceType\TypeLoader;
use UaDeviceType\Unknown;
use UConverter;
use UnexpectedValueException;

use function array_chunk;
use function array_filter;
use function array_key_exists;
use function array_map;
use function assert;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function in_array;
use function is_array;
use function is_scalar;
use function json_decode;
use function json_encode;
use function mb_str_pad;
use function mb_strlen;
use function mb_strpos;
use function mb_strtolower;
use function microtime;
use function mkdir;
use function number_format;
use function preg_match;
use function sprintf;
use function str_replace;
use function trim;

use const JSON_PRETTY_PRINT;
use const JSON_THROW_ON_ERROR;
use const STR_PAD_LEFT;

final class RewriteTestsCommand extends Command
{
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
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws UnexpectedValueException
     * @throws RuntimeException
     * @throws \LogicException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(messages: 'init Detector ...', options: OutputInterface::VERBOSITY_NORMAL);

        $cache = new class () implements CacheInterface {
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
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
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
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
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
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
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
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
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
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
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
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
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
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            #[Override]
            public function has(string $key): bool
            {
                return false;
            }
        };

        $logger   = new ConsoleLogger($output);
        $factory  = new DetectorFactory($cache, $logger);
        $detector = $factory();

        $basePath                = 'vendor/mimmi20/browser-detector/';
        $detectorTargetDirectory = $basePath . 'tests/data/';
        $testSource              = 'tests';

        $output->writeln(
            messages: 'removing old existing files from vendor ...',
            options: OutputInterface::VERBOSITY_NORMAL,
        );

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

        $txtChecks     = [];
        $headerChecks1 = [];
        $headerChecks2 = [];
        $headerChecks3 = [];
        $headerChecks4 = [];
        $messageLength = 0;
        $counter       = 0;
        $duplicates    = 0;
        $errors        = 0;
        $skipped       = 0;
        $testCount     = 0;
        $baseMessage   = 'checking Header ';
        $timeCheck     = 0.0;
        $timeDetect    = 0.0;
        $timeRead      = 0.0;
        $timeWrite     = 0.0;

        $clonedOutput = clone $output;
        $clonedOutput->setVerbosity(OutputInterface::VERBOSITY_QUIET);

        foreach ($this->testsLoader->getProperties($clonedOutput, $sources) as $test) {
            $test['headers'] = array_map(
                static fn (string $header) => trim($header),
                $test['headers'],
            );

            $test['headers'] = array_filter(
                $test['headers'],
                static fn (string $header): bool => $header !== '',
            );

            $startTime = microtime(true);

            $seachHeader = (string) UserAgent::fromHeaderArray($test['headers']);

            ++$counter;

            $loopMessage = sprintf(
                '%s[%s] - ',
                $baseMessage,
                mb_str_pad(
                    string: number_format(num: $counter, thousands_separator: '.'),
                    length: 14,
                    pad_type: STR_PAD_LEFT,
                ),
            );

            $message = $loopMessage . 'check';

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $output->write(
                messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                options: OutputInterface::VERBOSITY_NORMAL,
            );

            $timeCheck += microtime(true) - $startTime;

            if (array_key_exists($seachHeader, $txtChecks)) {
                ++$skipped;

                continue;
            }

            $txtChecks[$seachHeader] = $test;

//            if (
//                !array_key_exists('x-requested-with', $test['headers'])
//                && !array_key_exists('http-x-requested-with', $test['headers'])
//            ) {
//                ++$skipped;
//
//                continue;
//            }

            if (
                array_key_exists('x-requested-with', $test['headers'])
                && array_key_exists('http-x-requested-with', $test['headers'])
            ) {
                $message = $loopMessage . '<error>"x-requested-with" header is available twice</error>';

                if (mb_strlen($message) > $messageLength) {
                    $messageLength = mb_strlen($message);
                }

                $output->writeln(
                    messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );
            }

            $xRequestHeader = null;

            if (array_key_exists('x-requested-with', $test['headers'])) {
                $xRequestHeader = $test['headers']['x-requested-with'];
            } elseif (array_key_exists('http-x-requested-with', $test['headers'])) {
                $xRequestHeader = $test['headers']['http-x-requested-with'];
            }

            $secChUaHeader = null;

            if (array_key_exists('sec-ch-ua', $test['headers'])) {
                $secChUaHeader = $test['headers']['sec-ch-ua'];
            }

            $secChPlatformHeader = null;

            if (array_key_exists('sec-ch-ua-platform', $test['headers'])) {
                $secChPlatformHeader = $test['headers']['sec-ch-ua-platform'];
            }

            $secChModelHeader = null;

            if (array_key_exists('sec-ch-ua-model', $test['headers'])) {
                $secChModelHeader = $test['headers']['sec-ch-ua-model'];
            }

            $message = $loopMessage . 'redetect';

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $output->write(
                messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                options: OutputInterface::VERBOSITY_NORMAL,
            );

            $startTime = microtime(true);

            $result = $this->handleTest(
                output: $output,
                detector: $detector,
                headers: $test['headers'],
                parentMessage: $message,
                messageLength: $messageLength,
            );

            $timeDetect += microtime(true) - $startTime;

            if (!is_array($result)) {
                ++$duplicates;

                continue;
            }

            if ($result['client']['name'] === null) {
                if ($xRequestHeader !== null && $xRequestHeader !== 'XMLHttpRequest') {
                    if (!array_key_exists($xRequestHeader, $headerChecks1)) {
                        $addMessage = sprintf(
                            'Could not detect the Client for the x-requested-with Header "%s"',
                            $xRequestHeader,
                        );
                        $message    = $loopMessage . $addMessage;

                        if (mb_strlen($message) > $messageLength) {
                            $messageLength = mb_strlen($message);
                        }

                        $output->writeln(
                            messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                            options: OutputInterface::VERBOSITY_NORMAL,
                        );

                        $headerChecks1[$xRequestHeader] = true;
                    }
                }

                if ($secChUaHeader !== null) {
                    if (!array_key_exists($secChUaHeader, $headerChecks2)) {
                        $addMessage = sprintf(
                            'Could not detect the Client for the sec-ch-ua Header "%s"',
                            $secChUaHeader,
                        );
                        $message    = $loopMessage . $addMessage;

                        if (mb_strlen($message) > $messageLength) {
                            $messageLength = mb_strlen($message);
                        }

                        $output->writeln(
                            messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                            options: OutputInterface::VERBOSITY_NORMAL,
                        );

                        $headerChecks2[$secChUaHeader] = true;
                    }
                }
            }

            if ($result['os']['name'] === null) {
                if ($secChPlatformHeader !== null) {
                    if (!array_key_exists($secChPlatformHeader, $headerChecks3)) {
                        $addMessage = sprintf(
                            'Could not detect the OS for the sec-ch-ua-platform Header "%s"',
                            $secChPlatformHeader,
                        );
                        $message    = $loopMessage . $addMessage;

                        if (mb_strlen($message) > $messageLength) {
                            $messageLength = mb_strlen($message);
                        }

                        $output->writeln(
                            messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                            options: OutputInterface::VERBOSITY_NORMAL,
                        );

                        $headerChecks3[$secChPlatformHeader] = true;
                    }
                }
            }

            if ($result['device']['deviceName'] === null) {
                if ($secChModelHeader !== null) {
                    if (!array_key_exists($secChModelHeader, $headerChecks4)) {
                        $addMessage = sprintf(
                            'Could not detect the Device for the sec-ch-ua-model Header "%s"',
                            $secChModelHeader,
                        );
                        $message    = $loopMessage . $addMessage;

                        if (mb_strlen($message) > $messageLength) {
                            $messageLength = mb_strlen($message);
                        }

                        $output->writeln(
                            messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                            options: OutputInterface::VERBOSITY_NORMAL,
                        );

                        $headerChecks4[$secChModelHeader] = true;
                    }
                }
            }

            $deviceManufaturer = mb_strtolower(
                UConverter::transcode($result['device']['manufacturer'] ?? '', 'ISO-8859-1', 'UTF8'),
            );
            $deviceManufaturer = $deviceManufaturer === '' ? 'unknown' : str_replace(
                ['.', ' '],
                ['', '-'],
                $deviceManufaturer,
            );

            $clientManufaturer = mb_strtolower(
                UConverter::transcode($result['client']['manufacturer'] ?? '', 'ISO-8859-1', 'UTF8'),
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

            $tests = [];

            if (file_exists($file)) {
                $addMessage = sprintf('read temporary file %s', $file);
                $message    = $loopMessage . $addMessage;

                if (mb_strlen($message) > $messageLength) {
                    $messageLength = mb_strlen($message);
                }

                $output->write(
                    messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );

                try {
                    $startTime = microtime(true);

                    $tests = json_decode(file_get_contents($file), false, 512, JSON_THROW_ON_ERROR);

                    $addMessage = sprintf('read temporary file %s - done', $file);
                    $message    = $loopMessage . $addMessage;

                    if (mb_strlen($message) > $messageLength) {
                        $messageLength = mb_strlen($message);
                    }

                    $output->write(
                        messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                        options: OutputInterface::VERBOSITY_NORMAL,
                    );
                } catch (JsonException $e) {
                    ++$errors;

                    $exception = new Exception('An error occured while decoding a result', 0, $e);

                    $addMessage = sprintf('<error>%s</error>', (string) $exception);

                    $message = $loopMessage . $addMessage;

                    $output->writeln(
                        messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                        options: OutputInterface::VERBOSITY_NORMAL,
                    );

                    continue;
                } finally {
                    $timeRead += microtime(true) - $startTime;
                }
            } else {
                $addMessage = sprintf('<error>temporary file %s not found</error>', $file);
                $message    = $loopMessage . $addMessage;

                if (mb_strlen($message) > $messageLength) {
                    $messageLength = mb_strlen($message);
                }

                $output->writeln(
                    messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );
            }

            $addMessage = sprintf('write to temporary file %s', $file);
            $message    = $loopMessage . $addMessage;

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $output->write(
                messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                options: OutputInterface::VERBOSITY_NORMAL,
            );

            $tests[] = $result;

            try {
                $startTime = microtime(true);

                $saved = file_put_contents(
                    filename: $file,
                    data: json_encode($tests, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT),
                );
            } catch (JsonException) {
                ++$errors;

                $addMessage = sprintf('<error>An error occured while encoding file %s</error>', $file);
                $message    = $loopMessage . $addMessage;

                if (mb_strlen($message) > $messageLength) {
                    $messageLength = mb_strlen($message);
                }

                $output->writeln(
                    messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );

                continue;
            } finally {
                $timeWrite += microtime(true) - $startTime;
            }

            unset($tests);

            if ($saved === false) {
                ++$errors;

                $addMessage = sprintf('<error>An error occured while saving file %s</error>', $file);
                $message    = $loopMessage . $addMessage;

                if (mb_strlen($message) > $messageLength) {
                    $messageLength = mb_strlen($message);
                }

                $output->writeln(
                    messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                    options: OutputInterface::VERBOSITY_NORMAL,
                );

                continue;
            }

            $addMessage = sprintf(' write to temporary file %s - done', $file);

            unset($file);

            $message = $loopMessage . $addMessage;

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $output->write(
                messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
                options: OutputInterface::VERBOSITY_NORMAL,
            );

            ++$testCount;
        }

        $output->writeln(messages: '', options: OutputInterface::VERBOSITY_NORMAL);

        $this->jsonNormalizer->init($output);

        $output->writeln(
            messages: sprintf(
                'check result:       %7d test(s), %7d duplicate(s), %7d error(s)',
                $testCount,
                $duplicates,
                $errors,
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(
            messages: sprintf(
                'time checking:      %f sec',
                $timeCheck,
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(
            messages: sprintf(
                'time detecting:     %f sec',
                $timeDetect,
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(
            messages: sprintf(
                'time reading cache: %f sec',
                $timeRead,
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
        $output->writeln(
            messages: sprintf(
                'time writing cache: %f sec',
                $timeWrite,
            ),
            options: OutputInterface::VERBOSITY_NORMAL,
        );
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

                continue;
            }

            try {
                $data = json_decode($file->getContents(), false, 512, JSON_THROW_ON_ERROR);
            } catch (JsonException $e) {
                ++$errors;

                $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                $output->writeln(
                    '<error>' . (new Exception(
                        'An error occured while encoding a resultset',
                        0,
                        $e,
                    )) . '</error>',
                    OutputInterface::VERBOSITY_NORMAL,
                );

                continue;
            }

            foreach (array_chunk($data, 100) as $number => $parts) {
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

                $p2 = sprintf(
                    'tests/data/%s/%s',
                    $matches['deviceManufaturer'],
                    $matches['deviceType'],
                );

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

                if (mb_strlen($message) > $messageLength) {
                    $messageLength = mb_strlen($message);
                }

                $output->write(
                    "\r" . mb_str_pad(string: $message, length: $messageLength),
                    false,
                    OutputInterface::VERBOSITY_VERY_VERBOSE,
                );

                try {
                    $normalized = $this->jsonNormalizer->normalize(
                        $output,
                        $parts,
                        $message,
                        $messageLength,
                    );
                } catch (InvalidArgumentException | RuntimeException $e) {
                    $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                    $output->writeln('<error>' . $e . '</error>', OutputInterface::VERBOSITY_NORMAL);

                    continue;
                }

                if ($normalized === null) {
                    $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                    $output->writeln(
                        '<error>' . (new Exception(
                            sprintf('file "%s" contains invalid json', $path),
                        )) . '</error>',
                        OutputInterface::VERBOSITY_NORMAL,
                    );

                    return 1;
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

                if (mb_strlen($message) > $messageLength) {
                    $messageLength = mb_strlen($message);
                }

                $output->write(
                    "\r" . mb_str_pad(string: $message, length: $messageLength),
                    false,
                    OutputInterface::VERBOSITY_VERY_VERBOSE,
                );

                $success = @file_put_contents($path, $normalized);

                if ($success !== false) {
                    continue;
                }

                ++$errors;
                $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                $output->writeln(
                    '<error>' . sprintf(
                        'An error occured while writing file %s',
                        $path,
                    ) . '</error>',
                    OutputInterface::VERBOSITY_NORMAL,
                );
            }
        }

        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
        $dataToOutput = [
            'useragents processed' => $counter,
            'tests written' => $testCount,
            'skipped' => $skipped,
            'errors' => $errors,
            'duplicates' => $duplicates,
        ];

        foreach ($dataToOutput as $title => $number) {
            $output->writeln(
                sprintf(
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
                OutputInterface::VERBOSITY_NORMAL,
            );
        }

        return self::SUCCESS;
    }

    /**
     * @param array<non-empty-string, non-empty-string> $headers
     *
     * @return array<mixed>
     *
     * @throws void
     */
    private function handleTest(
        OutputInterface $output,
        Detector $detector,
        array $headers,
        string $parentMessage,
        int &$messageLength = 0,
    ): array | null {
        $message = $parentMessage . ' - <info>detect for new result ...</info>';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $output->write(
            messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
            options: OutputInterface::VERBOSITY_VERY_VERBOSE,
        );

        try {
            $newResult = $detector->getBrowser($headers);
        } catch (\Psr\SimpleCache\InvalidArgumentException | NotNumericException | UnexpectedValueException | Throwable $e) {
            $output->writeln(
                messages: sprintf('<error>%s</error>', $e),
                options: OutputInterface::VERBOSITY_NORMAL,
            );

            return null;
        }

        $message = $parentMessage . ' - <info>analyze new result ...</info>';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $output->write(
            messages: "\r" . mb_str_pad(string: $message, length: $messageLength),
            options: OutputInterface::VERBOSITY_VERY_VERBOSE,
        );

        $deviceTypeLoader = new TypeLoader();

        try {
            $deviceType = $deviceTypeLoader->load($newResult['device']['type'] ?? 'unknown');
        } catch (NotFoundException) {
            $deviceType = new Unknown();
        }

        if (
            in_array(
                $newResult['device']['deviceName'],
                ['general Desktop', 'general Apple Device', 'general Philips TV', 'PC', 'Macintosh', 'Linux Desktop', 'Windows Desktop'],
                true,
            )
            || in_array(
                $newResult['client']['type'],
                ['bot'],
                true,
            )
            || (
                !$deviceType->isMobile()
                && !$deviceType->isTablet()
                && !$deviceType->isTv()
            )
        ) {
            assert(is_scalar($newResult['client']['name']));
            assert(is_scalar($newResult['engine']['name']));
            assert(is_scalar($newResult['os']['name']));
            assert(is_scalar($newResult['device']['deviceName']));
            assert(is_scalar($newResult['device']['marketingName']));
            assert(is_scalar($newResult['device']['manufacturer']));

            $keys = [
                (string) $newResult['client']['name'],
                (string) $newResult['engine']['name'],
                (string) $newResult['os']['name'],
                (string) $newResult['device']['deviceName'],
                (string) $newResult['device']['marketingName'],
                (string) $newResult['device']['manufacturer'],
            ];

            $key = implode('-', $keys);

            if (array_key_exists($key, $this->tests)) {
                return null;
            }

            $this->tests[$key] = 1;
        } elseif (
            ($deviceType->isMobile() || $deviceType->isTablet() || $deviceType->isTv())
            && is_scalar($newResult['client']['name'])
            && mb_strpos((string) $newResult['client']['name'], 'general') === false
            && !in_array($newResult['client']['name'], [null, 'unknown'], true)
            && is_scalar($newResult['device']['deviceName'])
            && mb_strpos((string) $newResult['device']['deviceName'], 'general') === false
            && !in_array($newResult['device']['deviceName'], [null, 'unknown'], true)
        ) {
            assert(is_scalar($newResult['engine']['name']));
            assert(is_scalar($newResult['os']['name']));
            assert(is_scalar($newResult['device']['marketingName']));
            assert(is_scalar($newResult['device']['manufacturer']));

            $keys = [
                (string) $newResult['client']['name'],
                (string) $newResult['engine']['name'],
                (string) $newResult['os']['name'],
                (string) $newResult['device']['deviceName'],
                (string) $newResult['device']['marketingName'],
                (string) $newResult['device']['manufacturer'],
            ];

            $key = implode('-', $keys);

            if (array_key_exists($key, $this->tests)) {
                return null;
            }

            $this->tests[$key] = 1;
        }

        return $newResult;
    }
}
