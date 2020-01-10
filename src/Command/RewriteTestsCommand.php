<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2019, Thomas Mueller <mimmi20@live.de>
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
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
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
     * @throws \Symfony\Component\Console\Exception\LogicException                   When this abstract method is not implemented
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidNewLineStringException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentStyleException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidIndentSizeException
     * @throws \Ergebnis\Json\Normalizer\Exception\InvalidJsonEncodeOptionsException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @return int 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $consoleLogger = new ConsoleLogger($output);

        $cache    = new Psr16Cache(new NullAdapter());
        $factory  = new DetectorFactory($cache, $consoleLogger);
        $detector = $factory();

        $basePath                = 'vendor/mimmi20/browser-detector/';
        $detectorTargetDirectory = $basePath . 'tests/data/';
        $testSource              = 'tests';

        $this->getHelper('existing-tests-remover')->remove($output, $detectorTargetDirectory);

        $testResults   = [];
        $txtChecks     = [];
        $testCount     = 0;
        $duplicates    = 0;
        $messageLength = 0;
        $errors        = 0;
        $counter       = 0;

        foreach ($this->getHelper('existing-tests-loader')->getHeaders($consoleLogger, [new JsonFileSource($consoleLogger, $testSource)]) as $header) {
            $seachHeader = (string) UserAgent::fromHeaderArray($header);

            ++$counter;
            $message = sprintf('selecting tests, checking Header ... [%7d]', $counter);

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $output->write("\r" . str_pad($message, $messageLength, ' '));

            if (array_key_exists($seachHeader, $txtChecks)) {
                ++$duplicates;

                continue;
            }

            $txtChecks[$seachHeader] = 1;

            $headers = UserAgent::fromString($seachHeader)->getHeaders();

            try {
                $result = $this->handleTest($consoleLogger, $detector, $headers);
            } catch (\UnexpectedValueException $e) {
                ++$errors;
                $output->writeln('');
                $consoleLogger->error(new \Exception(sprintf('An error occured while checking Headers "%s"', $seachHeader), 0, $e));

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

            try {
                $testResults[$c][$t][] = $result->toArray();
            } catch (\UnexpectedValueException $e) {
                ++$errors;
                $output->writeln('');
                $consoleLogger->error(new \Exception('An error occured while converting a result to an array', 0, $e));

                continue;
            }

            ++$testCount;
        }

        $output->writeln('');

        $testSchemaUri = 'file://' . realpath($basePath . 'schema/tests.json');

        /** @var JsonNormalizer $jsonNormalizer */
        $jsonNormalizer = $this->getHelperSet()->get('json-normalizer');

        $output->writeln(sprintf('check result: %7d test(s), %7d duplicate(s), %7d error(s)', $testCount, $duplicates, $errors));
        $output->writeln('rewrite tests ...');
        $messageLength = 0;

        ksort($testResults, SORT_STRING | SORT_ASC);

        foreach ($testResults as $c => $x) {
            $message = sprintf('re-write test files in directory tests/data/%s/', $c);

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $output->write("\r" . str_pad($message, $messageLength, ' '));

            if (!file_exists(sprintf($basePath . 'tests/data/%s', $c))) {
                mkdir(sprintf($basePath . 'tests/data/%s', $c));
            }

            ksort($x, SORT_STRING | SORT_ASC);

            foreach ($x as $t => $data) {
                if (!file_exists(sprintf($basePath . 'tests/data/%s/%s', $c, $t))) {
                    mkdir(sprintf($basePath . 'tests/data/%s/%s', $c, $t));
                }

                foreach (array_chunk($data, 100) as $number => $parts) {
                    $path       = sprintf($basePath . 'tests/data/%s/%s/%07d.json', $c, $t, $number);
                    $normalized = $jsonNormalizer->normalize($consoleLogger, $parts, $testSchemaUri);

                    if (null === $normalized) {
                        $consoleLogger->error(new \Exception(sprintf('file "%s" contains invalid json', $path)));

                        return 1;
                    }

                    file_put_contents(
                        $path,
                        $normalized
                    );
                }
            }
        }

        $output->writeln('');
        $output->writeln('done');

        return 0;
    }

    /**
     * @param \Psr\Log\LoggerInterface $consoleLogger
     * @param Detector                 $detector
     * @param array                    $headers
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \UnexpectedValueException
     *
     * @return \UaResult\Result\ResultInterface|null
     */
    private function handleTest(LoggerInterface $consoleLogger, Detector $detector, array $headers): ?ResultInterface
    {
        $consoleLogger->debug('        detect for new result');

        $newResult = $detector->__invoke($headers);

        $consoleLogger->debug('        analyze new result');

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
