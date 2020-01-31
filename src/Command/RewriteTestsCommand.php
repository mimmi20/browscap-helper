<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2020, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Command;

use BrowscapHelper\Command\Helper\JsonNormalizer;
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
use BrowserDetector\Detector;
use BrowserDetector\DetectorFactory;
use BrowserDetector\Version\VersionInterface;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use UaResult\Result\ResultInterface;

final class RewriteTestsCommand extends Command
{
    /**
     * @var array
     */
    private $tests = [];

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
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException
     * @throws \Symfony\Component\Finder\Exception\DirectoryNotFoundException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentStyleException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentSizeException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \ArithmeticError
     * @throws \UnexpectedValueException
     * @throws \RuntimeException
     * @throws \LogicException
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @see    setCode()
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

            $addMessage = sprintf(
                '[%7d] - redetect - [%7d tests] - [%7d duplicates] - [%7d errors] <bg=green;fg=white;>%s</><bg=yellow;fg=black;>%s</><bg=red;fg=white;>%s</> <bg=red;fg=white;>%12d byte</>',
                $counter,
                $testCount,
                $duplicates,
                $errors,
                str_pad('', (int) ($testCount / $counter * 50)),
                str_pad('', (int) ($duplicates / $counter * 50)),
                str_pad('', (int) ($errors / $counter * 50)),
                memory_get_usage(true)
            );
            $message = $baseMessage . $addMessage;

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
            } catch (\UnexpectedValueException $e) {
                ++$errors;
                $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                $output->writeln('<error>' . (new \Exception(sprintf('An error occured while checking Headers "%s"', $seachHeader), 0, $e)) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            if (null === $result) {
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
                $tests = json_decode(file_get_contents($file));
            }

            try {
                $tests[] = $result->toArray();
            } catch (\UnexpectedValueException $e) {
                unset($tests);

                ++$errors;
                $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                $output->writeln('<error>' . (new \Exception('An error occured while converting a result to an array', 0, $e)) . '</error>', OutputInterface::VERBOSITY_NORMAL);

                continue;
            }

            $saved = file_put_contents($file, json_encode($tests));

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

        $testSchemaUri = 'file://' . realpath($basePath . 'schema/tests.json');

        /** @var JsonNormalizer $jsonNormalizer */
        $jsonNormalizer = $this->getHelperSet()->get('json-normalizer');
        $jsonNormalizer->init($output, $testSchemaUri);

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

                $data = json_decode($file->getContents());

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
                    } catch (\InvalidArgumentException | \RuntimeException $e) {
                        $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
                        $output->writeln('<error>' . $e . '</error>', OutputInterface::VERBOSITY_NORMAL);

                        continue;
                    }

                    if (null === $normalized) {
                        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                        $output->writeln('<error>' . (new \Exception(sprintf('file "%s" contains invalid json', $path))) . '</error>', OutputInterface::VERBOSITY_NORMAL);

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

                    if (false === $success) {
                        ++$errors;
                        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
                        $output->writeln('<error>' . sprintf('An error occured while writing file %s', $path) . '</error>', OutputInterface::VERBOSITY_NORMAL);
                    }
                }
            }
        }

        $output->writeln('', OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(sprintf('tests written: %7d', $testCount), OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(sprintf('errors:        %7d', $errors), OutputInterface::VERBOSITY_NORMAL);
        $output->writeln(sprintf('duplicates:    %7d', $duplicates), OutputInterface::VERBOSITY_NORMAL);

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param Detector        $detector
     * @param array           $headers
     * @param string          $parentMessage
     * @param int             $messageLength
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \UnexpectedValueException
     *
     * @return \UaResult\Result\ResultInterface|null
     */
    private function handleTest(OutputInterface $output, Detector $detector, array $headers, string $parentMessage, int &$messageLength = 0): ?ResultInterface
    {
        $message = $parentMessage . ' - detect for new result ...';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $output->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

        $newResult = $detector->__invoke($headers);

        $message = $parentMessage . ' - analyze new result ...';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $output->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERY_VERBOSE);

        if (in_array($newResult->getDevice()->getDeviceName(), ['general Desktop', 'general Apple Device', 'general Philips TV'], true)
            || (
                !$newResult->getDevice()->getType()->isMobile()
                && !$newResult->getDevice()->getType()->isTablet()
                && !$newResult->getDevice()->getType()->isTv()
            )
        ) {
            $keys = [
                (string) $newResult->getBrowser()->getName(),
                $newResult->getBrowser()->getVersion()->getVersion(VersionInterface::IGNORE_MICRO),
                (string) $newResult->getEngine()->getName(),
                $newResult->getEngine()->getVersion()->getVersion(VersionInterface::IGNORE_MICRO),
                (string) $newResult->getOs()->getName(),
                $newResult->getOs()->getVersion()->getVersion(VersionInterface::IGNORE_MICRO),
                (string) $newResult->getDevice()->getDeviceName(),
                (string) $newResult->getDevice()->getMarketingName(),
                (string) $newResult->getDevice()->getManufacturer()->getName(),
            ];

            $key = implode('-', $keys);

            if (array_key_exists($key, $this->tests)) {
                return null;
            }

            $this->tests[$key] = 1;
        } elseif (($newResult->getDevice()->getType()->isMobile() || $newResult->getDevice()->getType()->isTablet() || $newResult->getDevice()->getType()->isTv())
            && false === mb_strpos((string) $newResult->getBrowser()->getName(), 'general')
            && !in_array($newResult->getBrowser()->getName(), [null, 'unknown'], true)
            && false === mb_strpos((string) $newResult->getDevice()->getDeviceName(), 'general')
            && !in_array($newResult->getDevice()->getDeviceName(), [null, 'unknown'], true)
        ) {
            $keys = [
                $newResult->getBrowser()->getName(),
                $newResult->getBrowser()->getVersion()->getVersion(VersionInterface::IGNORE_MICRO),
                (string) $newResult->getEngine()->getName(),
                $newResult->getEngine()->getVersion()->getVersion(VersionInterface::IGNORE_MICRO),
                (string) $newResult->getOs()->getName(),
                $newResult->getOs()->getVersion()->getVersion(VersionInterface::IGNORE_MICRO),
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
