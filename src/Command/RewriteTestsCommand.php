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
use Psr\Log\LoggerInterface;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\Cache\Simple\NullCache;
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

class RewriteTestsCommand extends Command
{
    /**
     * @var array
     */
    private $tests = [];

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this
            ->setName('rewrite-tests')
            ->setDescription('Rewrites existing tests');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);

        $cache    = new NullCache();
        $factory  = new DetectorFactory($cache, $consoleLogger);
        $detector = $factory();

        $basePath                = 'vendor/mimmi20/browser-detector-tests/';
        $detectorTargetDirectory = $basePath . 'tests/issues/';
        $testSource              = 'tests';

        $output->writeln('remove old test files ...');

        $this->getHelper('existing-tests-remover')->remove($detectorTargetDirectory);
        $this->getHelper('existing-tests-remover')->remove($basePath . 'tests/UserAgentsTest');

        $output->writeln('selecting tests ...');
        $testResults = [];
        $txtChecks   = [];

        foreach ($this->getHelper('existing-tests-reader')->getHeaders($consoleLogger, [new JsonFileSource($consoleLogger, $testSource)]) as $seachHeader) {
            if (array_key_exists($seachHeader, $txtChecks)) {
                $consoleLogger->debug('    Header "' . $seachHeader . '" added more than once --> skipped');

                continue;
            }

            $txtChecks[$seachHeader] = 1;

            $headers = UserAgent::fromString($seachHeader)->getHeader();

            $consoleLogger->debug('    Header "' . $seachHeader . '" checking ...');

            try {
                $result = $this->handleTest($consoleLogger, $detector, $headers);
            } catch (InvalidArgumentException $e) {
                $consoleLogger->error(new \Exception(sprintf('An error occured while checking Headers "%s"', $seachHeader), 0, $e));

                continue;
            } catch (\Throwable $e) {
                $consoleLogger->warning(new \Exception(sprintf('An error occured while checking Headers "%s"', $seachHeader), 0, $e));

                continue;
            }

            if (null === $result) {
                $consoleLogger->debug('    Header "' . $seachHeader . '" was skipped because a similar UA was already added');

                continue;
            }

            $consoleLogger->debug('    Header "' . $seachHeader . '" added to list');

            $testResults[] = $result->toArray();
        }

        $output->writeln(sprintf('%d tests selected', count($testResults)));

        $output->writeln('rewrite tests and circleci ...');
        $folderChunks    = array_chunk($testResults, 1000);
        $circleFile      = $basePath . '.circleci/config.yml';
        $circleciContent = '';

        $consoleLogger->info(sprintf('will generate %d directories for the tests', count($folderChunks)));

        foreach ($folderChunks as $folderId => $folderChunk) {
            $targetDirectory = $detectorTargetDirectory . sprintf('%1$07d', $folderId);

            if (!file_exists($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
            }

            $consoleLogger->info(sprintf('    now genearting files in directory "%s"', $targetDirectory));

            $fileChunks = array_chunk($folderChunk, 100);
            $consoleLogger->info(sprintf('    will generate %d test files in directory "%s"', count($fileChunks), $targetDirectory));

            $issueCounter = 0;

            foreach ($fileChunks as $fileId => $fileChunk) {
                $tests = [];

                foreach ($fileChunk as $resultArray) {
                    $formatedIssue   = sprintf('%1$07d', $folderId);
                    $formatedCounter = sprintf('%1$05d', $issueCounter);

                    $tests['test-' . $formatedIssue . '-' . $formatedCounter] = $resultArray;
                    ++$issueCounter;
                }

                $this->getHelper('detector-test-writer')->write($consoleLogger, $tests, $targetDirectory, $folderId, $fileId);
            }

            $count = count($folderChunk);
            $group = sprintf('%1$07d', $folderId);

            $tests = str_pad((string) $count, 4, ' ', STR_PAD_LEFT) . ' test' . (1 !== $count ? 's' : '');

            $testContent = [
                '        \'tests/issues/' . $group . '/\',',
            ];

            $testFile = $basePath . 'tests/UserAgentsTest/T' . $group . 'Test.php';
            file_put_contents(
                $testFile,
                str_replace(
                    ['//### tests ###', '### group ###', '### count ###'],
                    [implode(PHP_EOL, $testContent), $group, $count],
                    (string) file_get_contents('templates/test.php.txt')
                )
            );

            $columns = 111 + 2 * mb_strlen((string) $count);

            $circleciContent .= PHP_EOL;
            $circleciContent .= '    #' . $tests;
            $circleciContent .= PHP_EOL;
            $circleciContent .= '      - run: php -n -d memory_limit=768M vendor/bin/phpunit --colors --no-coverage --columns ' . $columns . ' tests/UserAgentsTest/T' . $group . 'Test.php -- ' . $tests;
            $circleciContent .= PHP_EOL;
        }

        $output->writeln('writing ' . $circleFile . ' ...');
        file_put_contents(
            $circleFile,
            str_replace('### tests ###', $circleciContent, (string) file_get_contents('templates/config.yml.txt'))
        );

        $output->writeln('done');

        return 0;
    }

    /**
     * @param \Psr\Log\LoggerInterface $consoleLogger
     * @param Detector                 $detector
     * @param array                    $headers
     *
     * @throws InvalidArgumentException
     *
     * @return \UaResult\Result\ResultInterface|null
     */
    private function handleTest(LoggerInterface $consoleLogger, Detector $detector, array $headers): ?ResultInterface
    {
        $consoleLogger->debug('        detect for new result');

        $newResult = $detector($headers);

        $consoleLogger->debug('        analyze new result');

        if (!$newResult->getDevice()->getType()->isMobile()
            && !$newResult->getDevice()->getType()->isTablet()
            && !$newResult->getDevice()->getType()->isTv()
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
        } elseif (($newResult->getDevice()->getType()->isMobile() || $newResult->getDevice()->getType()->isTablet())
            && false === mb_strpos((string) $newResult->getBrowser()->getName(), 'general')
            && !in_array($newResult->getBrowser()->getName(), [null, 'unknown'])
            && false === mb_strpos((string) $newResult->getDevice()->getDeviceName(), 'general')
            && !in_array($newResult->getDevice()->getDeviceName(), [null, 'unknown'])
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
        }

        $consoleLogger->debug('        clone browser');

        /** @var \UaResult\Browser\BrowserInterface $browser */
        $browser = clone $newResult->getBrowser();

        $consoleLogger->debug('        clone platform');

        /** @var \UaResult\Os\OsInterface $platform */
        $platform = clone $newResult->getOs();
        $request  = (new GenericRequestFactory())->createRequestFromArray($headers);

        $consoleLogger->debug('        clone device');

        /** @var \UaResult\Device\DeviceInterface $device */
        $device   = clone $newResult->getDevice();
        $replaced = false;

        $defaultDevice = new Device(
            null,
            null,
            new Company('Unknown', null, null),
            new Company('Unknown', null, null),
            new Unknown(),
            new Display(null, new \UaDisplaySize\Unknown(), null)
        );

        if (in_array($device->getDeviceName(), [null, 'unknown'])) {
            $consoleLogger->debug('        cloned device resetted - unknown device name');

            $device   = clone $defaultDevice;
            $replaced = true;
        }

        $jsonParser           = new Json();
        $companyLoaderFactory = new CompanyLoaderFactory($jsonParser, new Filter());

        /** @var \BrowserDetector\Loader\CompanyLoader $companyLoader */
        $companyLoader = $companyLoaderFactory();

        $platformParserFactory = new PlatformParserFactory($consoleLogger, $jsonParser, $companyLoader);
        $platformParser        = $platformParserFactory();

        $deviceLoaderFactory = new DeviceLoaderFactory($consoleLogger, $jsonParser, $companyLoader, $platformParser, new Filter());

        if (!$replaced
            && $device->getType()->isMobile()
            && !in_array(mb_strtolower($device->getDeviceName()), ['general apple device'])
            && false !== mb_stripos($device->getDeviceName(), 'general')
        ) {
            $consoleLogger->debug('        cloned device resetted - checking with regexes');

            try {
                /** @var \BrowscapHelper\Command\Helper\RegexFactory $regexFactory */
                $regexFactory = $this->getHelper('regex-factory');
                $regexFactory->detect($request->getDeviceUserAgent());
                [$device] = $regexFactory->getDevice($consoleLogger);
                $replaced = false;

                if (null === $device || in_array($device->getDeviceName(), [null, 'unknown'])) {
                    $device   = clone $defaultDevice;
                    $replaced = true;
                }

                if (!$replaced
                    && !in_array($device->getDeviceName(), ['general Desktop', 'general Apple Device', 'general Philips TV'])
                    && false !== mb_stripos($device->getDeviceName(), 'general')
                ) {
                    $device = clone $defaultDevice;
                }
            } catch (\InvalidArgumentException $e) {
                $consoleLogger->error($e);

                $device = clone $defaultDevice;
            } catch (NotFoundException $e) {
                $consoleLogger->info($e);

                $device = clone $defaultDevice;
            } catch (GeneralBlackberryException $e) {
                $deviceLoader = $deviceLoaderFactory('rim');

                try {
                    [$device] = $deviceLoader->load('general blackberry device', $request->getDeviceUserAgent());
                } catch (\Throwable $e) {
                    $consoleLogger->critical($e);

                    $device = clone $defaultDevice;
                }
            } catch (GeneralPhilipsTvException $e) {
                $deviceLoader = $deviceLoaderFactory('philips');

                try {
                    [$device] = $deviceLoader->load('general philips tv', $request->getDeviceUserAgent());
                } catch (\Throwable $e) {
                    $consoleLogger->critical($e);

                    $device = clone $defaultDevice;
                }
            } catch (GeneralTabletException $e) {
                $deviceLoader = $deviceLoaderFactory('unknown');

                try {
                    [$device] = $deviceLoader->load('general tablet', $request->getDeviceUserAgent());
                } catch (\Throwable $e) {
                    $consoleLogger->critical($e);

                    $device = clone $defaultDevice;
                }
            } catch (GeneralPhoneException $e) {
                $deviceLoader = $deviceLoaderFactory('unknown');

                try {
                    [$device] = $deviceLoader->load('general mobile phone', $request->getDeviceUserAgent());
                } catch (\Throwable $e) {
                    $consoleLogger->critical($e);

                    $device = clone $defaultDevice;
                }
            } catch (GeneralDeviceException $e) {
                $deviceLoader = $deviceLoaderFactory('unknown');

                try {
                    [$device] = $deviceLoader->load('general mobile device', $request->getDeviceUserAgent());
                } catch (\Throwable $e) {
                    $consoleLogger->critical($e);

                    $device = clone $defaultDevice;
                }
            } catch (GeneralTvException $e) {
                $deviceLoader = $deviceLoaderFactory('unknown');

                try {
                    [$device] = $deviceLoader->load('general tv device', $request->getDeviceUserAgent());
                } catch (\Throwable $e) {
                    $consoleLogger->critical($e);

                    $device = clone $defaultDevice;
                }
            } catch (NoMatchException $e) {
                $consoleLogger->info($e);

                $device = clone $defaultDevice;
            } catch (\Throwable $e) {
                $consoleLogger->error($e);

                $device = clone $defaultDevice;
            }
        }

        $consoleLogger->debug('        clone engine');

        /** @var \UaResult\Engine\EngineInterface $engine */
        $engine = clone $newResult->getEngine();

        $consoleLogger->debug('        generating result');

        return new Result($request->getHeaders(), $device, $platform, $browser, $engine);
    }
}
