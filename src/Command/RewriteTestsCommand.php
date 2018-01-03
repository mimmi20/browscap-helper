<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Command;

use BrowscapHelper\Factory\Regex\GeneralDeviceException;
use BrowscapHelper\Factory\Regex\NoMatchException;
use BrowscapHelper\Factory\RegexFactory;
use BrowscapHelper\Source\TxtFileSource;
use BrowscapHelper\Writer\DetectorTestWriter;
use BrowserDetector\Detector;
use BrowserDetector\Factory\NormalizerFactory;
use BrowserDetector\Helper\GenericRequestFactory;
use BrowserDetector\Loader\DeviceLoader;
use BrowserDetector\Loader\NotFoundException;
use BrowserDetector\Version\VersionInterface;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Psr\Cache\CacheItemPoolInterface;
use Seld\JsonLint\JsonParser;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use UaResult\Device\Device;
use UaResult\Result\Result;
use UaResult\Result\ResultInterface;

/**
 * Class RewriteTestsCommand
 *
 * @category   Browscap Helper
 *
 * @author     Thomas Müller <mimmi20@live.de>
 */
class RewriteTestsCommand extends Command
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
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
     * @param \Monolog\Logger                   $logger
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param \BrowserDetector\Detector         $detector
     */
    public function __construct(Logger $logger, CacheItemPoolInterface $cache, Detector $detector)
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
     * @return int|null null or 0 if everything went fine, or an error code
     * @throws \FileLoader\Exception
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Seld\JsonLint\ParsingException
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $this->logger->pushHandler(new PsrHandler($consoleLogger));

        $basePath                = 'vendor/mimmi20/browser-detector-tests/';
        $detectorTargetDirectory = $basePath . 'tests/issues/';
        $testSource              = 'tests/';

        $output->writeln('remove old test files ...');

        $finder   = new Finder();
        $finder->files();
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($detectorTargetDirectory);

        foreach ($finder as $file) {
            unlink($file->getPathname());
        }

        $finder   = new Finder();
        $finder->files();
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($basePath . 'tests/UserAgentsTest/');

        foreach ($finder as $file) {
            unlink($file->getPathname());
        }

        $output->writeln('add new tests ...');

        $detectorTestWriter   = new DetectorTestWriter($this->logger);
        $detectorNumber       = 0;
        $testCounter          = [$detectorNumber => 0];
        $detectorTotalCounter = 0;
        $detectorCounter      = 0;

        $output->writeln('next test for BrowserDetector: ' . $detectorNumber);

        $targetDirectory = $detectorTargetDirectory . sprintf('%1$07d', $detectorNumber) . '/';

        foreach ((new TxtFileSource($this->logger, $testSource))->getUserAgents() as $useragent) {
            $useragent = trim($useragent);
            $result    = $this->handleTest($useragent);

            if (null === $result) {
                $this->logger->info('UA "' . $useragent . '" was skipped because a similar UA was already added');

                continue;
            }

            if ($detectorTestWriter->write($result, $targetDirectory, $detectorNumber, $useragent, $detectorCounter)) {
                $testCounter[$detectorNumber] = $detectorCounter;
                ++$detectorNumber;
                $testCounter[$detectorNumber] = 0;
                $detectorTotalCounter += $detectorCounter;
                $detectorCounter = 0;

                $output->writeln('next test for BrowserDetector: ' . $detectorNumber);

                $targetDirectory = $detectorTargetDirectory . sprintf('%1$07d', $detectorNumber) . '/';

                if (!file_exists($targetDirectory)) {
                    mkdir($targetDirectory);
                }
            }
        }

        $output->writeln('count and order tests ...');

        $circleTests = [];

        foreach ($testCounter as $detectorNumber => $count) {
            $group = sprintf('%1$07d', $detectorNumber);

            $circleTests[$group] = $count;
        }

        $countArray = [];
        $groupArray = [];

        foreach ($circleTests as $group => $count) {
            $countArray[$group] = $count;
            $groupArray[$group] = $group;
        }

        array_multisort(
            $countArray,
            SORT_NUMERIC,
            SORT_DESC,
            $groupArray,
            SORT_NUMERIC,
            SORT_DESC,
            $circleTests
        );

        $i           = 0;
        $c           = 0;

        $circleLines     = [$i => []];
        $circleCount     = [$i => 0];
        $circleTestsCopy = $circleTests;

        while (count($circleTestsCopy)) {
            foreach ($circleTestsCopy as $group => $count) {
                if (1000 < ($c + $count)) {
                    ++$i;
                    $c               = 0;
                    $circleLines[$i] = [];
                    $circleCount[$i] = 0;
                }
                $c += $count;
                $circleLines[$i][] = $group;
                $circleCount[$i] += $count;

                unset($circleTestsCopy[$group]);
            }
        }

        $output->writeln('preparing circle.yml ...');

        $circleFile      = $basePath . '.circleci/config.yml';
        $circleciContent = '';

        foreach (array_reverse(array_keys($circleCount)) as $i) {
            $count  = $circleCount[$i];
            $group  = sprintf('%1$07d', $i);

            $tests   = str_pad((string) $count, 4, ' ', STR_PAD_LEFT) . ' test' . (1 !== $count ? 's' : '');

            $testContent = [];

            foreach (array_reverse($circleLines[$i]) as $groupx) {
                $testContent[] = '        \'tests/issues/' . $groupx . '/\',';
            }

            $testFile = $basePath . 'tests/UserAgentsTest/T' . $group . 'Test.php';
            file_put_contents(
                $testFile,
                str_replace(
                    '//### tests ###',
                    implode(PHP_EOL, $testContent),
                    file_get_contents('templates/test.php.txt')
                )
            );

            $columns = 111 + 2 * mb_strlen((string) $count);

            $circleciContent .= PHP_EOL;
            $circleciContent .= '    #' . $tests;
            $circleciContent .= PHP_EOL;
            $circleciContent .= '      - run: php -n -d memory_limit=768M vendor/bin/phpunit --printer \'ScriptFUSION\PHPUnitImmediateExceptionPrinter\ImmediateExceptionPrinter\' --colors --no-coverage --columns ' . $columns . '  tests/UserAgentsTest/T' . $group . 'Test.php -- ' . $tests;
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
     * @param string $useragent
     *
     * @return \UaResult\Result\ResultInterface|null
     * @throws \Psr\Cache\InvalidArgumentException
     * @throws \Seld\JsonLint\ParsingException
     */
    private function handleTest(string $useragent): ?ResultInterface
    {
        $this->logger->info('        detect for new result');

        $newResult = (new Detector($this->cache, $this->logger))->getBrowser($useragent);

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
        }

        $this->logger->info('        rewriting');

        /* rewrite browsers */

        $this->logger->info('        rewriting browser');

        /** @var \UaResult\Browser\BrowserInterface $browser */
        $browser = clone $newResult->getBrowser();

        /* rewrite platforms */

        $this->logger->info('        rewriting platform');

        $platform = clone $newResult->getOs();

        /* @var $platform \UaResult\Os\OsInterface|null */

        $this->logger->info('        rewriting device');

        $normalizedUa = (new NormalizerFactory())->build()->normalize($useragent);

        /* rewrite devices */

        /** @var \UaResult\Device\DeviceInterface $device */
        $device   = clone $newResult->getDevice();
        $replaced = true;

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
                $regexFactory = new RegexFactory($this->cache, $this->logger);
                $regexFactory->detect($normalizedUa);
                [$device]     = $regexFactory->getDevice();
                $replaced     = false;

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

                $device   = new Device(null, null);
            } catch (NotFoundException $e) {
                $this->logger->debug($e);

                $device   = new Device(null, null);
            } catch (GeneralDeviceException $e) {
                $deviceLoader = new DeviceLoader($this->cache, $this->logger);

                try {
                    [$device] = $deviceLoader->load('general mobile device', $normalizedUa);
                } catch (\Exception $e) {
                    $this->logger->crit($e);

                    $device   = new Device(null, null);
                }
            } catch (NoMatchException $e) {
                $this->logger->debug($e);

                $device   = new Device(null, null);
            } catch (\Exception $e) {
                $this->logger->error($e);

                $device   = new Device(null, null);
            }
        }

        /* rewrite engines */

        $this->logger->info('        rewriting engine');

        /** @var \UaResult\Engine\EngineInterface $engine */
        $engine = clone $newResult->getEngine();

        $this->logger->info('        generating result');

        $request = (new GenericRequestFactory())->createRequestFromString($useragent);

        return new Result($request->getHeaders(), $device, $platform, $browser, $engine);
    }
}
