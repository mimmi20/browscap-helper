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

use ArithmeticError;
use BrowscapHelper\Command\Helper\JsonNormalizer;
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
use BrowserDetector\Detector;
use BrowserDetector\DetectorFactory;
use BrowserDetector\Version\NotNumericException;
use BrowserDetector\Version\VersionInterface;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentSizeException;
use Ergebnis\Json\Normalizer\Exception\InvalidIndentStyleException;
use Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException;
use Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException;
use Exception;
use InvalidArgumentException;
use JsonException;
use RuntimeException;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
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
use function assert;
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
     * @throws InvalidNewLineStringException
     * @throws InvalidIndentStyleException
     * @throws InvalidIndentSizeException
     * @throws InvalidJsonEncodeOptionsException
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

        $cache    = new Psr16Cache(new NullAdapter());
        $factory  = new DetectorFactory($cache, new ConsoleLogger($output));
        $detector = $factory();

        $basePath                = 'vendor/mimmi20/browser-detector/';
        $detectorTargetDirectory = $basePath . 'tests/data/';
        $testSource              = 'tests';

        $this->getHelper('existing-tests-remover')->remove($output, $detectorTargetDirectory);

        $sources = [new JsonFileSource($testSource)];

        $output->writeln('reading already existing tests ...', OutputInterface::VERBOSITY_NORMAL);

        $this->getHelper('existing-tests-remover')->remove($output, '.build');
        $this->getHelper('existing-tests-remover')->remove($output, '.build', true);

        $txtChecks     = [];
        $messageLength = 0;
        $counter       = 0;
        $duplicates    = 0;
        $errors        = 0;
        $testCount     = 0;
        $baseMessage   = 'checking Header ';

        $clonedOutput = clone $output;
        $clonedOutput->setVerbosity(OutputInterface::VERBOSITY_QUIET);

        foreach ($this->getHelper('existing-tests-loader')->getHeaders($clonedOutput, $sources) as $header) {
            $seachHeader = (string) UserAgent::fromHeaderArray($header);

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

            $txtChecks[$seachHeader] = 1;

            try {
                $result = $this->handleTest($output, $detector, $header, $message, $messageLength);
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
            } catch (JsonException $e) {
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

        $jsonNormalizer = $this->getHelperSet()->get('json-normalizer');
        assert($jsonNormalizer instanceof JsonNormalizer);
        $jsonNormalizer->init($output);

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
                        $normalized = $jsonNormalizer->normalize($output, $parts, $message, $messageLength);
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
                        $normalized
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
    private function handleTest(OutputInterface $output, Detector $detector, array $headers, string $parentMessage, int &$messageLength = 0): ?ResultInterface
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
