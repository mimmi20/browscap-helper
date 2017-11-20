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
use BrowserDetector\Detector;
use BrowserDetector\Factory\NormalizerFactory;
use BrowserDetector\Helper\GenericRequestFactory;
use BrowserDetector\Loader\DeviceLoader;
use BrowserDetector\Loader\NotFoundException;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use UaResult\Browser\Browser;
use UaResult\Device\Device;
use UaResult\Result\Result;
use UaResult\Result\ResultFactory;
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
     * @param \Monolog\Logger                   $logger
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param \BrowserDetector\Detector         $detector
     */
    public function __construct(Logger $logger, CacheItemPoolInterface $cache, Detector $detector)
    {
        $this->logger   = $logger;
        $this->cache    = $cache;
        $this->detector = $detector;

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
     * @throws \LogicException When this abstract method is not implemented
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $this->logger->pushHandler(new PsrHandler($consoleLogger));

        $basePath = 'vendor/mimmi20/browser-detector-tests/';

        $sourceDirectory = $basePath . 'tests/issues/';

        if (!file_exists($sourceDirectory)) {
            $this->logger->crit('source directory not found');

            return 1;
        }

        $filesArray  = scandir($sourceDirectory, SCANDIR_SORT_ASCENDING);
        $files       = [];
        $testCounter = [];
        $groups      = [];

        $output->writeln('count and check directories ...');

        foreach ($filesArray as $filename) {
            if (in_array($filename, ['.', '..'])) {
                continue;
            }

            $output->writeln('  checking directory: ' . $filename);

            if (!is_dir($sourceDirectory . DIRECTORY_SEPARATOR . $filename)) {
                $files[] = $filename;
                $this->logger->warn('file ' . $filename . ' is out of strcture');

                continue;
            }

            $subdirFilesArray = scandir($sourceDirectory . DIRECTORY_SEPARATOR . $filename, SCANDIR_SORT_ASCENDING);

            foreach ($subdirFilesArray as $subdirFilename) {
                if (in_array($subdirFilename, ['.', '..'])) {
                    continue;
                }

                $fullFilename = $filename . DIRECTORY_SEPARATOR . $subdirFilename;
                $files[]      = $fullFilename;
                $group        = $filename;

                $groups[$fullFilename]              = $group;
                $testCounter[$group][$fullFilename] = 0;
            }
        }

        $checks       = [];
        $g            = null;
        $groupCounter = 0;

        $output->writeln('handling files ...');

        foreach ($files as $fullFilename) {
            $output->writeln('  handling file: ' . $fullFilename);

            $file  = new \SplFileInfo($sourceDirectory . DIRECTORY_SEPARATOR . $fullFilename);
            $group = $groups[$fullFilename];

            if ($g !== $group) {
                $groupCounter = 0;
                $g            = $group;
            }

            $newCounter = $this->handleFile($file, $checks, $groupCounter, (int) $group);

            if (!$newCounter) {
                continue;
            }

            $testCounter[$group][$fullFilename] += $newCounter;
        }

        $output->writeln('remove old test files ...');

        $testFilesArray  = scandir($basePath . 'tests/UserAgentsTest/', SCANDIR_SORT_ASCENDING);

        foreach ($testFilesArray as $filename) {
            if (in_array($filename, ['.', '..'])) {
                continue;
            }

            unlink($filename);
        }

        $output->writeln('preparing circle.yml ...');

        $circleFile      = $basePath . 'circle.yml';
        $circleciContent = 'machine:
  php:
    version: 7.1.9
  timezone:
    Europe/Berlin

dependencies:
  pre:
    - rm /opt/circleci/php/$(phpenv global)/etc/conf.d/xdebug.ini
  override:
    - composer update --optimize-autoloader --prefer-dist --prefer-stable --no-progress --no-interaction -vv

test:
  override:
    - composer validate
';

        $circleTests = [];

        foreach ($testCounter as $group => $filesinGroup) {
            $count = 0;

            foreach (array_keys($filesinGroup) as $fileinGroup) {
                $count += $testCounter[$group][$fileinGroup];
            }

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

        foreach ($circleTests as $group => $count) {
            $columns = 111 + 2 * mb_strlen((string) $count);
            $tests   = str_pad((string) $count, 4, ' ', STR_PAD_LEFT) . ' test' . (1 !== $count ? 's' : '');

            $testContent = '<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowserDetectorTest\UserAgentsTest;

use BrowserDetectorTest\UserAgentsTestTrait;
use PHPUnit\Framework\TestCase;

/**
 * Class T' . $group . 'Test
 *
 * has ' . trim($tests) . '
 * this file was created/edited automatically, please do not edit it
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 * @group      ' . $group . '
 */
class T' . $group . 'Test extends TestCase
{
    use UserAgentsTestTrait;

    /**
     * @var string
     */
    private $sourceDirectory = \'tests/issues/' . $group . '/\';
}
';
            $testFile = $basePath . 'tests/UserAgentsTest/T' . $group . 'Test.php';
            file_put_contents($testFile, $testContent);
        }

        foreach (array_keys($circleCount) as $i) {
            $count  = $circleCount[$i];
            $groups = trim($circleLines[$i], ',');

            $columns = 111 + 2 * mb_strlen((string) $count);
            $tests   = str_pad((string) $count, 4, ' ', STR_PAD_LEFT) . ' test' . (1 !== $count ? 's' : '');

            $circleciContent .= PHP_EOL;
            $circleciContent .= '    #' . $tests;
            $circleciContent .= PHP_EOL;
            $circleciContent .= '    - php -n -d memory_limit=768M vendor/bin/phpunit --printer \'ScriptFUSION\PHPUnitImmediateExceptionPrinter\ImmediateExceptionPrinter\' --colors --no-coverage --columns ' . $columns . ' --group ' . $groups . ' -- ' . $tests;
            $circleciContent .= PHP_EOL;
        }

        $output->writeln('writing ' . $circleFile . ' ...');
        file_put_contents($circleFile, $circleciContent);

        $output->writeln('done');

        return 0;
    }

    /**
     * @param \SplFileInfo $file
     * @param array        $checks
     * @param int          $groupCounter
     * @param int          $group
     *
     * @return int
     */
    private function handleFile(
        \SplFileInfo $file,
        array &$checks,
        int &$groupCounter,
        int $group
    ): int {
        $this->logger->info('    checking ...');

        /** @var $file \SplFileInfo */
        if (!$file->isFile() || 'json' !== $file->getExtension()) {
            return 0;
        }

        $this->logger->info('    reading ...');

        $tests = json_decode(file_get_contents($file->getPathname()), true);

        if (null === $tests) {
            $this->logger->info('    file does not contain any test');
            unlink($file->getPathname());

            return 0;
        }

        $oldCounter = count($tests);

        if (1 > $oldCounter) {
            $this->logger->info('    file does not contain any test');
            unlink($file->getPathname());

            return 0;
        }

        if (1 === $oldCounter) {
            $this->logger->info('    contains 1 test');
        } else {
            $this->logger->info('    contains ' . $oldCounter . ' tests');
        }

        $this->logger->info('    processing ...');
        $outputDetector = [];

        foreach ($tests as $key => $test) {
            if (isset($checks[$test['ua']])) {
                // UA was added more than once
                $this->logger->error('    UA "' . $test['ua'] . '" added more than once, now for key "' . $key . '", before for key "' . $checks[$test['ua']] . '"');
                unset($tests->$key);

                continue;
            }

            $this->logger->info('    processing Test ' . $key . ' ...');

            $checks[$test['ua']] = $key;
            $newKey              = 'test-' . sprintf('%1$07d', $group) . '-' . sprintf('%1$03d', $groupCounter);

            $outputDetector += [
                $newKey => [
                    'ua'     => $test['ua'],
                    'result' => $this->handleTest($test['ua'], $test['result'])->toArray(),
                ],
            ];
            ++$groupCounter;
        }

        $newCounter = count($outputDetector);

        $this->logger->info('    contains now ' . $newCounter . ' tests');

        if (1 > $newCounter) {
            $this->logger->info('    all tests are removed from the file');
            unlink($file->getPathname());

            return 0;
        }

        if ($newCounter < $oldCounter) {
            $this->logger->info('    ' . ($oldCounter - $newCounter) . ' test(s) is/are removed from the file');
        }

        $this->logger->info('    removing old file');
        unlink($file->getPathname());

        $this->logger->info('    rewriting file');

        file_put_contents(
            $file->getPathname(),
            json_encode($outputDetector, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_FORCE_OBJECT) . PHP_EOL
        );

        return $newCounter;
    }

    /**
     * @param string $useragent
     * @param array  $oldResultArray
     *
     * @return \UaResult\Result\ResultInterface
     */
    private function handleTest(string $useragent, array $oldResultArray): ResultInterface
    {
        $this->logger->info('        rewriting');

        $oldResult = (new ResultFactory())->fromArray($this->cache, $this->logger, $oldResultArray);
        $result    = (new Detector($this->cache, $this->logger))->getBrowser($useragent);

        /* rewrite browsers */

        $this->logger->info('        rewriting browser');

        /** @var \UaResult\Browser\BrowserInterface $browser */
        //$browser = clone $result->getBrowser();
        $browser = clone $oldResult->getBrowser();

        /* rewrite platforms */

        $this->logger->info('        rewriting platform');

        //$platform = clone $result->getOs();
        $platform = clone $oldResult->getOs();

        /* @var $platform \UaResult\Os\OsInterface|null */

        $this->logger->info('        rewriting device');

        $normalizedUa = (new NormalizerFactory())->build()->normalize($useragent);

        /* rewrite devices */

        /** @var \UaResult\Device\DeviceInterface|null $device */
        $device   = clone $result->getDevice();
        $replaced = false;

        if (null === $device || in_array($device->getDeviceName(), [null, 'unknown'])) {
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

                $device = clone $result->getDevice();

                if (in_array($device->getDeviceName(), [null, 'unknown'])) {
                    $device = new Device(null, null);
                }
            } catch (NotFoundException $e) {
                $this->logger->debug($e);

                $device   = clone $result->getDevice();
                $replaced = false;

                if (in_array($device->getDeviceName(), [null, 'unknown'])) {
                    $device   = new Device(null, null);
                    $replaced = true;
                }

                if (!$replaced
                    && !in_array($device->getDeviceName(), ['general Desktop', 'general Apple Device'])
                    && false !== mb_stripos($device->getDeviceName(), 'general')
                ) {
                    $device = new Device('not found', null);
                }
            } catch (GeneralDeviceException $e) {
                $deviceLoader = new DeviceLoader($this->cache);

                try {
                    [$device] = $deviceLoader->load('general mobile device', $normalizedUa);
                } catch (\Exception $e) {
                    $this->logger->crit($e);

                    $device = new Device(null, null);
                }
            } catch (NoMatchException $e) {
                $this->logger->debug($e);

                $device = clone $result->getDevice();

                if (in_array($device->getDeviceName(), [null, 'unknown'])) {
                    $device = new Device(null, null);
                }
            } catch (\Exception $e) {
                $this->logger->error($e);

                $device = clone $result->getDevice();

                if (in_array($device->getDeviceName(), [null, 'unknown'])) {
                    $device = new Device(null, null);
                }
            }
        }

        /* rewrite engines */

        $this->logger->info('        rewriting engine');

        /** @var \UaResult\Engine\EngineInterface $engine */
        //$engine = clone $result->getEngine();
        $engine = clone $oldResult->getEngine();

        $this->logger->info('        generating result');

        $request = (new GenericRequestFactory())->createRequestFromString($useragent);

        return new Result($request->getHeaders(), $device, $platform, $browser, $engine);
    }
}
