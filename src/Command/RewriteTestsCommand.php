<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2018, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Command;

use BrowscapHelper\Factory\Regex\GeneralBlackberryException;
use BrowscapHelper\Factory\Regex\GeneralDeviceException;
use BrowscapHelper\Factory\Regex\NoMatchException;
use BrowscapHelper\Source\JsonFileSource;
use BrowscapHelper\Source\Ua\UserAgent;
use BrowserDetector\Cache\Cache;
use BrowserDetector\Detector;
use BrowserDetector\Loader\DeviceLoaderFactory;
use BrowserDetector\Loader\NotFoundException;
use BrowserDetector\Version\VersionInterface;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Psr\SimpleCache\CacheInterface as PsrCacheInterface;
use Seld\JsonLint\JsonParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use UaNormalizer\NormalizerFactory;
use UaRequest\GenericRequestFactory;
use UaResult\Device\Device;
use UaResult\Result\Result;
use UaResult\Result\ResultInterface;

/**
 * Class RewriteTestsCommand
 *
 * @category   Browscap Helper
 */
class RewriteTestsCommand extends Command
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var \Psr\SimpleCache\CacheInterface
     */
    private $cache;

    /**
     * @var \BrowserDetector\Detector
     */
    private $detector;

    /**
     * @var \Seld\JsonLint\JsonParser
     */
    private $jsonParser;

    /**
     * @var array
     */
    private $tests = [];

    /**
     * @param \Monolog\Logger                 $logger
     * @param \Psr\SimpleCache\CacheInterface $cache
     * @param \BrowserDetector\Detector       $detector
     */
    public function __construct(Logger $logger, PsrCacheInterface $cache, Detector $detector)
    {
        $this->logger   = $logger;
        $this->cache    = $cache;
        $this->detector = $detector;

        $this->jsonParser = new JsonParser();

        parent::__construct();
    }

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
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $this->logger->pushHandler(new PsrHandler($consoleLogger));

        $basePath                = 'vendor/mimmi20/browser-detector-tests/';
        $detectorTargetDirectory = $basePath . 'tests/issues/';
        $testSource              = 'tests';

        $output->writeln('remove old test files ...');

        $finder = new Finder();
        $finder->files();
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->ignoreUnreadableDirs();
        $finder->in($detectorTargetDirectory);

        foreach ($finder as $file) {
            unlink($file->getPathname());
        }

        $finder = new Finder();
        $finder->files();
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->ignoreUnreadableDirs();
        $finder->in($basePath . 'tests/UserAgentsTest');

        foreach ($finder as $file) {
            unlink($file->getPathname());
        }

        $output->writeln('selecting tests ...');
        $testResults = [];
        $txtChecks   = [];

        foreach ($this->getHelper('existing-tests-reader')->getHeaders($output, new JsonFileSource($this->logger, $testSource)) as $seachHeader) {
            if (array_key_exists($seachHeader, $txtChecks)) {
                $this->logger->info('    Header "' . $seachHeader . '" added more than once --> skipped');

                continue;
            }

            $txtChecks[$seachHeader] = 1;

            $headers = UserAgent::fromString($seachHeader)->getHeader();
            $result  = $this->handleTest($headers);

            if (null === $result) {
                $this->logger->info('Header "' . $seachHeader . '" was skipped because a similar UA was already added');

                continue;
            }

            $testResults[] = $result->toArray();
        }

        $output->writeln(sprintf('%d tests selected ...', count($testResults)));

        $output->writeln('rewrite tests and circleci ...');
        $folderChunks    = array_chunk($testResults, 1000);
        $circleFile      = $basePath . '.circleci/config.yml';
        $circleciContent = '';

        $this->logger->info(sprintf('will generate %d directories for the tests', count($folderChunks)));

        foreach ($folderChunks as $folderId => $folderChunk) {
            $targetDirectory = $detectorTargetDirectory . sprintf('%1$07d', $folderId);

            if (!file_exists($targetDirectory)) {
                mkdir($targetDirectory, 0777, true);
            }

            $this->logger->info(sprintf('    now genearting files in directory "%s"', $targetDirectory));

            $fileChunks = array_chunk($folderChunk, 100);
            $this->logger->info(sprintf('    will generate %d test files in directory "%s"', count($fileChunks), $targetDirectory));

            $issueCounter = 0;

            foreach ($fileChunks as $fileId => $fileChunk) {
                $tests = [];

                foreach ($fileChunk as $resultArray) {
                    $formatedIssue   = sprintf('%1$07d', $folderId);
                    $formatedCounter = sprintf('%1$05d', $issueCounter);

                    $tests['test-' . $formatedIssue . '-' . $formatedCounter] = $resultArray;
                    ++$issueCounter;
                }

                $this->getHelper('detector-test-writer')->write($tests, $targetDirectory, $folderId, $fileId);
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
                    file_get_contents('templates/test.php.txt')
                )
            );

            $columns = 111 + 2 * mb_strlen((string) $count);

            $circleciContent .= PHP_EOL;
            $circleciContent .= '    #' . $tests;
            $circleciContent .= PHP_EOL;
            $circleciContent .= '    #  - run: php -n -d memory_limit=768M vendor/bin/phpunit --printer \'ScriptFUSION\PHPUnitImmediateExceptionPrinter\ImmediateExceptionPrinter\' --colors --no-coverage --group ' . $group . ' -- ' . $tests;
            $circleciContent .= PHP_EOL;
            $circleciContent .= '      - run: php -n -d memory_limit=768M vendor/bin/phpunit --colors --no-coverage --columns ' . $columns . ' tests/UserAgentsTest/T' . $group . 'Test.php -- ' . $tests;
            $circleciContent .= PHP_EOL;
        }

        $output->writeln('writing ' . $circleFile . ' ...');
        file_put_contents(
            $circleFile,
            str_replace('### tests ###', $circleciContent, file_get_contents('templates/config.yml.txt'))
        );

        $output->writeln('done');

        return 0;
    }

    /**
     * @param array $headers
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @return \UaResult\Result\ResultInterface|null
     */
    private function handleTest(array $headers): ?ResultInterface
    {
        $this->logger->debug('        detect for new result');

        $detector  = $this->detector;
        $newResult = $detector($headers);

        $this->logger->debug('        analyze new result');

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

        // rewrite browsers

        /** @var \UaResult\Browser\BrowserInterface $browser */
        $browser = clone $newResult->getBrowser();

        // rewrite platforms

        $platform = clone $newResult->getOs();

        // @var $platform \UaResult\Os\OsInterface|null

        $request      = (new GenericRequestFactory())->createRequestFromArray($headers);
        $normalizedUa = (new NormalizerFactory())->build()->normalize($request->getDeviceUserAgent());

        // rewrite devices

        /** @var \UaResult\Device\DeviceInterface $device */
        $device   = clone $newResult->getDevice();
        $replaced = false;

        if (in_array($device->getDeviceName(), [null, 'unknown'])) {
            $device   = new Device(null, null);
            $replaced = true;
        }

        if (!$replaced
            && $device->getType()->isMobile()
            && !in_array($device->getDeviceName(), ['general Apple Device'])
            && false !== mb_stripos($device->getDeviceName(), 'general')
        ) {
            try {
                /** @var \BrowscapHelper\Command\Helper\RegexFactory $regexFactory */
                $regexFactory = $this->getHelper('regex-factory');
                $regexFactory->detect($normalizedUa);
                [$device] = $regexFactory->getDevice();
                $replaced = false;

                if (null === $device || in_array($device->getDeviceName(), [null, 'unknown'])) {
                    $device   = new Device(null, null);
                    $replaced = true;
                }

                if (!$replaced
                    && !in_array($device->getDeviceName(), ['general Desktop', 'general Apple Device', 'general Philips TV'])
                    && false !== mb_stripos($device->getDeviceName(), 'general')
                ) {
                    $device = new Device('not found via regexes', null);
                }
            } catch (\InvalidArgumentException $e) {
                $this->logger->error($e);

                $device = new Device(null, null);
            } catch (NotFoundException $e) {
                $this->logger->debug($e);

                $device = new Device(null, null);
            } catch (GeneralBlackberryException $e) {
                $deviceLoaderFactory = new DeviceLoaderFactory(new Cache($this->cache), $this->logger);
                $deviceLoader        = $deviceLoaderFactory('rim', 'mobile');

                try {
                    $deviceLoader->init();
                    [$device] = $deviceLoader->load('general blackberry device', $normalizedUa);
                } catch (\Throwable $e) {
                    $this->logger->crit($e);

                    $device = new Device(null, null);
                }
            } catch (GeneralDeviceException $e) {
                $deviceLoaderFactory = new DeviceLoaderFactory(new Cache($this->cache), $this->logger);
                $deviceLoader        = $deviceLoaderFactory('unknown', 'unknown');

                try {
                    $deviceLoader->init();
                    [$device] = $deviceLoader->load('general mobile device', $normalizedUa);
                } catch (\Throwable $e) {
                    $this->logger->crit($e);

                    $device = new Device(null, null);
                }
            } catch (NoMatchException $e) {
                $this->logger->debug($e);

                $device = new Device(null, null);
            } catch (\Exception $e) {
                $this->logger->error($e);

                $device = new Device(null, null);
            }
        }

        // rewrite engines

        /** @var \UaResult\Engine\EngineInterface $engine */
        $engine = clone $newResult->getEngine();

        $this->logger->debug('        generating result');

        return new Result($request->getHeaders(), $device, $platform, $browser, $engine);
    }
}
