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

use BrowscapHelper\Factory\Regex\GeneralBlackberryException;
use BrowscapHelper\Factory\Regex\GeneralDeviceException;
use BrowscapHelper\Factory\Regex\GeneralPhilipsTvException;
use BrowscapHelper\Factory\Regex\GeneralPhoneException;
use BrowscapHelper\Factory\Regex\GeneralTabletException;
use BrowscapHelper\Factory\Regex\GeneralTvException;
use BrowscapHelper\Factory\Regex\NoMatchException;
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
use BrowserDetector\Detector;
use BrowserDetector\DetectorFactory;
use BrowserDetector\Loader\CompanyLoaderFactory;
use BrowserDetector\Loader\DeviceLoaderFactory;
use BrowserDetector\Loader\Helper\Filter;
use BrowserDetector\Loader\NotFoundException;
use BrowserDetector\Parser\PlatformParserFactory;
use BrowserDetector\Version\VersionInterface;
use JsonClass\Json;
use Localheinz\Json\Normalizer\FixedFormatNormalizer;
use Localheinz\Json\Normalizer\SchemaNormalizer;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Component\Cache\Psr16Cache;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use UaDeviceType\Unknown;
use UaRequest\GenericRequestFactory;
use UaResult\Company\Company;
use UaResult\Device\Device;
use UaResult\Device\Display;
use UaResult\Result\Result;
use UaResult\Result\ResultInterface;
use Localheinz\Json\Normalizer;

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
     * @return int|null null or 0 if everything went fine, or an error code
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException*@throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Symfony\Component\Console\Exception\LogicException           When this abstract method is not implemented
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);

        $cache    = new Psr16Cache(new NullAdapter());
        $factory  = new DetectorFactory($cache, $consoleLogger);
        $detector = $factory();

        $basePath                = 'vendor/mimmi20/browser-detector/';
        $detectorTargetDirectory = $basePath . 'tests/data/';
        $testSource              = 'tests';

        $output->writeln('remove old test files ...');

        $this->getHelper('existing-tests-remover')->remove($detectorTargetDirectory);

        $output->writeln('selecting tests ...');
        $testResults = [];
        $txtChecks   = [];
        $testCount   = 0;
        $duplicates  = 0;
        $messageLength = 0;
        $errors = 0;
        $counter = 0;

        foreach ($this->getHelper('existing-tests-reader')->getHeaders($consoleLogger, [new JsonFileSource($consoleLogger, $testSource)]) as $seachHeader) {
            ++$counter;
            $message     = sprintf('checking Header ... [%7d]', $counter);

            if (strlen($message) > $messageLength) {
                $messageLength = strlen($message);
            }

            $output->write("\r" . str_pad($message, $messageLength, ' '));

            if (array_key_exists($seachHeader, $txtChecks)) {
                ++$duplicates;

                continue;
            }

            $txtChecks[$seachHeader] = 1;

            $headers = UserAgent::fromString($seachHeader)->getHeader();

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

            $c = strtolower(utf8_decode($result->getDevice()->getManufacturer()->getType()));

            if (!$c) {
                $c = 'unknown';
            } else {
                $c = str_replace(['.', ' '], ['', '-'], $c);
            }

            $t = strtolower($result->getDevice()->getType()->getType());

            try {
                $testResults[$c][$t][] = $result->toArray();
            } catch (\UnexpectedValueException $e) {
                ++$errors;
                $output->writeln('');
                $consoleLogger->error(new \Exception('An error occured while converting a result to an array', 0, $e));

                continue;
            }

            ++$testCount;
            //break;
        }

        $output->writeln('');

        $jsonParser = new Json();
        $testSchemaUri = 'file://' . realpath($basePath . 'schema/tests.json');
        $format = new Normalizer\Format\Format(
            Normalizer\Format\JsonEncodeOptions::fromInt(JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT),
            Normalizer\Format\Indent::fromSizeAndStyle(2, 'space'),
            Normalizer\Format\NewLine::fromString("\n"),
            true
        );

        $output->writeln(sprintf('check result: %7d test(s), %7d duplicate(s), %7d error(s)', $testCount, $duplicates, $errors));
        $output->writeln('rewrite tests ...');
        $messageLength = 0;

        foreach ($testResults as $c => $x) {
            $message = sprintf('re-write test files in directory tests/data/%s/', $c);

            if (strlen($message) > $messageLength) {
                $messageLength = strlen($message);
            }

            $output->write("\r" . str_pad($message, $messageLength, ' '));

            if (!file_exists(sprintf($basePath . 'tests/data/%s', $c))) {
                mkdir(sprintf($basePath . 'tests/data/%s', $c));
            }

            foreach ($x as $t => $data) {
                if (!file_exists(sprintf($basePath . 'tests/data/%s/%s', $c, $t))) {
                    mkdir(sprintf($basePath . 'tests/data/%s/%s', $c, $t));
                }

                foreach (array_chunk($data, 100) as $number => $parts) {
                    $path = sprintf($basePath . 'tests/data/%s/%s/%07d.json', $c, $t, $number);

                    try {
                        $normalized = (new FixedFormatNormalizer(new SchemaNormalizer($testSchemaUri), $format))->normalize(Normalizer\Json::fromEncoded($jsonParser->encode($parts)));
                    } catch (\Throwable $e) {
                        $consoleLogger->error(new \Exception(sprintf('file "%s" contains invalid json', $path), 0, $e));
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
     * @return \UaResult\Result\ResultInterface|null
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \UnexpectedValueException
     */
    private function handleTest(LoggerInterface $consoleLogger, Detector $detector, array $headers): ?ResultInterface
    {
        $consoleLogger->debug('        detect for new result');

        $newResult = $detector($headers);

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
