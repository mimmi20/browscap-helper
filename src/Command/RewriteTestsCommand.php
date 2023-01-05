<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2022, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Command;

use ArithmeticError;
use BrowscapHelper\Helper\ExistingTestsLoader;
use BrowscapHelper\Helper\ExistingTestsRemover;
use BrowscapHelper\Helper\JsonNormalizer;
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
use BrowserDetector\Detector;
use BrowserDetector\DetectorFactory;
use BrowserDetector\Version\NotNumericException;
use BrowserDetector\Version\VersionInterface;
use DateInterval;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentSize;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentStyle;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptions;
use Ergebnis\Json\Normalizer\Exception\InvalidNewLineString;
use Exception;
use InvalidArgumentException;
use JsonException;
use Psr\SimpleCache\CacheInterface;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\LogicException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Exception\DirectoryNotFoundException;
use Symfony\Component\Finder\Finder;
use UaResult\Result\ResultInterface;
use UnexpectedValueException;

use function array_chunk;
use function array_key_exists;
use function file_exists;
use function file_get_contents;
use function file_put_contents;
use function implode;
use function in_array;
use function json_decode;
use function json_encode;
use function mb_strlen;
use function mb_strpos;
use function mb_strtolower;
use function mkdir;
use function sprintf;
use function str_pad;
use function str_replace;
use function utf8_decode;

use const JSON_THROW_ON_ERROR;
use const STR_PAD_RIGHT;

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
     * @throws ArithmeticError
     * @throws UnexpectedValueException
     * @throws RuntimeException
     * @throws \LogicException
     *
     * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln('init Detector ...', OutputInterface::VERBOSITY_NORMAL);

        $cache = new class () implements CacheInterface {
            /**
             * Fetches a value from the cache.
             *
             * @param string $key     the unique key of this item in the cache
             * @param mixed  $default default value to return if the key does not exist
             *
             * @return mixed the value of the item from the cache, or $default in case of cache miss
             *
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
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
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
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
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            public function delete(string $key): bool
            {
                return false;
            }

            /**
             * Wipes clean the entire cache's keys.
             *
             * @return bool true on success and false on failure
             */
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
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
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
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
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
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
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
             * @phpcsSuppress SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
             */
            public function has(string $key): bool
            {
                return false;
            }
        };

        $factory  = new DetectorFactory($cache, new ConsoleLogger($output));
        $detector = $factory();

        $basePath                = 'vendor/mimmi20/browser-detector/';
        $detectorTargetDirectory = $basePath . 'tests/data/';
        $testSource              = 'tests';

        $this->testsRemover->remove($output, $detectorTargetDirectory);

        $sources = [new JsonFileSource($testSource)];

        $output->writeln('reading already existing tests ...', OutputInterface::VERBOSITY_NORMAL);

        $this->testsRemover->remove($output, '.build');
        $this->testsRemover->remove($output, '.build', true);

        $txtChecks     = [];
        $messageLength = 0;
        $counter       = 0;
        $duplicates    = 0;
        $errors        = 0;
        $testCount     = 0;
        $baseMessage   = 'checking Header ';

        $clonedOutput = clone $output;
        $clonedOutput->setVerbosity(OutputInterface::VERBOSITY_QUIET);

        foreach ($this->testsLoader->getProperties($clonedOutput, $sources) as $test) {
            $seachHeader = (string) UserAgent::fromHeaderArray($test['headers']);

            ++$counter;

            $addMessage = sprintf('[%8d] - redetect', $counter);
            $message    = $baseMessage . $addMessage;

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $output->write("\r" . str_pad($message, $messageLength, ' '), false, OutputInterface::VERBOSITY_NORMAL);

            if (array_key_exists($seachHeader, $txtChecks)) {
                ++$errors;

                $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                $output->writeln('<error>' . sprintf('Header "%s" added more than once --> skipped', $seachHeader) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            $txtChecks[$seachHeader] = $test;

            try {
                $result = $this->handleTest($output, $detector, $test['headers'], $message, $messageLength);
            } catch (UnexpectedValueException $e) {
                ++$errors;
                $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                $output->writeln('<error>' . (new Exception(sprintf('An error occured while checking Headers "%s"', $seachHeader), 0, $e)) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            if (!$result instanceof ResultInterface) {
                ++$duplicates;

                continue;
            }

            $c = mb_strtolower(utf8_decode($result->getDevice()->getManufacturer()->getType()));

            if ('' === $c) {
                $c = 'unknown';
            } else {
                $c = str_replace(['.', ' '], ['', '-'], $c);
            }

            $t = mb_strtolower($result->getDevice()->getType()->getType());

            if (!file_exists(sprintf('.build/%s', $c))) {
                mkdir(sprintf('.build/%s', $c));
            }

            $file = sprintf('.build/%s/%s.json', $c, $t);

            if (!file_exists($file)) {
                $tests = [];
            } else {
                try {
                    $tests = json_decode(file_get_contents($file), false, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    ++$errors;
                    $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                    $output->writeln('<error>' . (new Exception('An error occured while decoding a result', 0, $e)) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                    continue;
                }
            }

            try {
                $tests[] = $result->toArray();
            } catch (UnexpectedValueException $e) {
                unset($tests);

                ++$errors;
                $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                $output->writeln('<error>' . (new Exception('An error occured while converting a result to an array', 0, $e)) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            try {
                $saved = file_put_contents($file, json_encode($tests, JSON_THROW_ON_ERROR));
            } catch (JsonException) {
                ++$errors;
                $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                $output->writeln('<error>' . sprintf('An error occured while encoding file %s', $file) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            unset($tests);

            if (false === $saved) {
                ++$errors;
                $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                $output->writeln('<error>' . sprintf('An error occured while saving file %s', $file) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            unset($file);

            ++$testCount;
        }

        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);

        $this->jsonNormalizer->init($output);

        $output->writeln(sprintf('check result: %7d test(s), %7d duplicate(s), %7d error(s)', $testCount, $duplicates, $errors), OutputInterface::VERBOSITY_NORMAL);
        $output->writeln('rewrite tests ...', OutputInterface::VERBOSITY_NORMAL);

        $messageLength = 0;
        $baseMessage   = 're-write test files in directory ';

        $baseFinder = new Finder();
        $baseFinder->notName('*.gitkeep');
        $baseFinder->ignoreDotFiles(true);
        $baseFinder->ignoreVCS(true);
        $baseFinder->sortByName();
        $baseFinder->ignoreUnreadableDirs();

        $dirFinder = clone $baseFinder;
        $dirFinder->directories();
        $dirFinder->in('.build');

        foreach ($dirFinder as $dir) {
            $fileFinder = clone $baseFinder;
            $fileFinder->files();
            $fileFinder->in($dir->getPathname());

            $c = $dir->getBasename();

            foreach ($fileFinder as $file) {
                $t = $file->getBasename('.' . $file->getExtension());

                try {
                    $data = json_decode($file->getContents(), false, 512, JSON_THROW_ON_ERROR);
                } catch (JsonException $e) {
                    ++$errors;
                    $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                    $output->writeln('<error>' . (new Exception('An error occured while encoding a resultset', 0, $e)) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                    continue;
                }

                foreach (array_chunk($data, 100) as $number => $parts) {
                    $path = $basePath . sprintf('tests/data/%s/%s/%07d.json', $c, $t, $number);

                    $p1 = $basePath . sprintf('tests/data/%s', $c);
                    if (!file_exists($p1)) {
                        mkdir($p1);
                    }

                    $p2 = $basePath . sprintf('tests/data/%s/%s', $c, $t);
                    if (!file_exists($p2)) {
                        mkdir($p2);
                    }

                    $message = $baseMessage . sprintf('tests/data/%s/%s/%07d.json', $c, $t, $number) . ' - normalizing';

                    if (mb_strlen($message) > $messageLength) {
                        $messageLength = mb_strlen($message);
                    }

                    $output->write("\r" . str_pad($message, $messageLength, ' '), false, OutputInterface::VERBOSITY_VERY_VERBOSE);

                    try {
                        $normalized = $this->jsonNormalizer->normalize($output, $parts, $message, $messageLength);
                    } catch (InvalidArgumentException | RuntimeException $e) {
                        $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                        $output->writeln('<error>' . $e . '</error>', OutputInterface::VERBOSITY_NORMAL);

                        continue;
                    }

                    if (null === $normalized) {
                        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                        $output->writeln('<error>' . (new Exception(sprintf('file "%s" contains invalid json', $path))) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                        return 1;
                    }

                    $message = $baseMessage . sprintf('tests/data/%s/%s/%07d.json', $c, $t, $number) . ' - writing';

                    if (mb_strlen($message) > $messageLength) {
                        $messageLength = mb_strlen($message);
                    }

                    $output->write("\r" . str_pad($message, $messageLength, ' '), false, OutputInterface::VERBOSITY_VERY_VERBOSE);

                    $success = @file_put_contents(
                        $path,
                        $normalized,
                    );

                    if (false !== $success) {
                        continue;
                    }

                    ++$errors;
                    $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                    $output->writeln('<error>' . sprintf('An error occured while writing file %s', $path) . '</error>', OutputInterface::VERBOSITY_NORMAL);
                }
            }
        }

        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(sprintf('tests written: %7d', $testCount), OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(sprintf('errors:        %7d', $errors), OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(sprintf('duplicates:    %7d', $duplicates), OutputInterface::VERBOSITY_NORMAL);

        return self::SUCCESS;
    }

    /**
     * @param array<string, string> $headers
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws UnexpectedValueException
     */
    private function handleTest(OutputInterface $output, Detector $detector, array $headers, string $parentMessage, int &$messageLength = 0): ResultInterface | null
    {
        $message = $parentMessage . ' - detect for new result ...';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $output->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

        try {
            $newResult = $detector->__invoke($headers);
        } catch (\Psr\SimpleCache\InvalidArgumentException | NotNumericException $e) {
            $output->writeln((string) $e, OutputInterface::VERBOSITY_NORMAL);

            return null;
        }

        $message = $parentMessage . ' - analyze new result ...';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $output->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

        if (
            in_array($newResult->getDevice()->getDeviceName(), ['general Desktop', 'general Apple Device', 'general Philips TV'], true)
            || (
                !$newResult->getDevice()->getType()->isMobile()
                && !$newResult->getDevice()->getType()->isTablet()
                && !$newResult->getDevice()->getType()->isTv()
            )
        ) {
            $keys = [
                (string) $newResult->getBrowser()->getName(),
                $newResult->getBrowser()->getVersion()->getVersion(VersionInterface::IGNORE_MINOR),
                (string) $newResult->getEngine()->getName(),
                $newResult->getEngine()->getVersion()->getVersion(VersionInterface::IGNORE_MINOR),
                (string) $newResult->getOs()->getName(),
                $newResult->getOs()->getVersion()->getVersion(VersionInterface::IGNORE_MINOR),
                (string) $newResult->getDevice()->getDeviceName(),
                (string) $newResult->getDevice()->getMarketingName(),
                (string) $newResult->getDevice()->getManufacturer()->getName(),
            ];

            $key = implode('-', $keys);

            if (array_key_exists($key, $this->tests)) {
                return null;
            }

            $this->tests[$key] = 1;
        } elseif (
            ($newResult->getDevice()->getType()->isMobile() || $newResult->getDevice()->getType()->isTablet() || $newResult->getDevice()->getType()->isTv())
            && false === mb_strpos((string) $newResult->getBrowser()->getName(), 'general')
            && !in_array($newResult->getBrowser()->getName(), [null, 'unknown'], true)
            && false === mb_strpos((string) $newResult->getDevice()->getDeviceName(), 'general')
            && !in_array($newResult->getDevice()->getDeviceName(), [null, 'unknown'], true)
        ) {
            $keys = [
                $newResult->getBrowser()->getName(),
                $newResult->getBrowser()->getVersion()->getVersion(VersionInterface::IGNORE_MINOR),
                (string) $newResult->getEngine()->getName(),
                $newResult->getEngine()->getVersion()->getVersion(VersionInterface::IGNORE_MINOR),
                (string) $newResult->getOs()->getName(),
                $newResult->getOs()->getVersion()->getVersion(VersionInterface::IGNORE_MINOR),
                $newResult->getDevice()->getDeviceName(),
                (string) $newResult->getDevice()->getMarketingName(),
                (string) $newResult->getDevice()->getManufacturer()->getName(),
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
