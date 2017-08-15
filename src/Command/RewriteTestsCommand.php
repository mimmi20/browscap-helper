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
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;
use Wurfl\Request\GenericRequestFactory;

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
    private $logger = null;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cache = null;

    /**
     * @var \BrowserDetector\Detector
     */
    private $detector = null;

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
    protected function configure()
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
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $this->logger->pushHandler(new PsrHandler($consoleLogger));

        $sourceDirectory = 'vendor/mimmi20/browser-detector-tests/tests/issues/';

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

            $newCounter = $this->handleFile($file, $checks, $groupCounter, $group);

            if (!$newCounter) {
                continue;
            }

            $testCounter[$group][$fullFilename] += $newCounter;
        }

        $output->writeln('preparing circle.yml ...');

        $circleFile      = 'vendor/mimmi20/browser-detector-tests/circle.yml';
        $circleciContent = 'machine:
  php:
    version: 7.1.0
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

        $circleLines = [];

        foreach ($testCounter as $group => $filesinGroup) {
            $count = 0;

            foreach (array_keys($filesinGroup) as $fileinGroup) {
                $count += $testCounter[$group][$fileinGroup];
            }

            $circleLines[$group] = $count;
        }

        $countArray = [];
        $groupArray = [];

        foreach ($circleLines as $group => $count) {
            $countArray[$group] = $count;
            $groupArray[$group] = $group;
        }

        array_multisort(
            $countArray,
            SORT_NUMERIC,
            SORT_ASC,
            $groupArray,
            SORT_NUMERIC,
            SORT_ASC,
            $circleLines
        );

        foreach ($circleLines as $group => $count) {
            $columns = 111 + 2 * mb_strlen((string) $count);
            $tests   = str_pad((string) $count, 4, ' ', STR_PAD_LEFT) . ' test' . ($count !== 1 ? 's' : '');

            $circleciContent .= PHP_EOL;
            $circleciContent .= '    #' . $tests;
            $circleciContent .= PHP_EOL;
            $circleciContent .= '    - php -n -d memory_limit=768M vendor/bin/phpunit --colors --no-coverage --columns ' . $columns . ' tests/UserAgentsTest/T' . $group . 'Test.php -- ' . $tests;
            $circleciContent .= PHP_EOL;

            $testContent = '<?php
/**
 * This file is part of the browser-detector-tests package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowserDetectorTest\UserAgentsTest;

use BrowserDetectorTest\UserAgentsTest;

/**
 * Class T' . $group . 'Test
 *
 * has ' . trim($tests) . '
 * this file was created/edited automatically, please do not edit it
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 * @group      ' . $group . '
 */
class T' . $group . 'Test extends UserAgentsTest
{
    /**
     * @var string
     */
    protected $sourceDirectory = \'tests/issues/' . $group . '/\';
}
';
            $testFile = 'vendor/mimmi20/browser-detector-tests/tests/UserAgentsTest/T' . $group . 'Test.php';
            file_put_contents($testFile, $testContent);
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
        &$groupCounter,
        $group
    ) {
        $this->logger->info('    checking ...');

        /** @var $file \SplFileInfo */
        if (!$file->isFile() || $file->getExtension() !== 'json') {
            return 0;
        }

        $this->logger->info('    reading ...');

        $tests = json_decode(file_get_contents($file->getPathname()), false);

        if (is_array($tests)) {
            $tests = (object) $tests;
        }

        $oldCounter = count(get_object_vars($tests));

        if ($oldCounter < 1) {
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
            if (is_array($test)) {
                $test = (object) $test;
            }

            if (isset($checks[$test->ua])) {
                // UA was added more than once
                $this->logger->error('    UA "' . $test->ua . '" added more than once, now for key "' . $key . '", before for key "' . $checks[$test->ua] . '"');
                unset($tests->$key);
                continue;
            }

            $this->logger->info('    processing Test ' . $key . ' ...');

            $checks[$test->ua] = $key;
            $newKey            = 'test-' . sprintf('%1$07d', $group) . '-' . sprintf('%1$03d', $groupCounter);

            $outputDetector += [
                $newKey => [
                    'ua'     => $test->ua,
                    'result' => $this->handleTest($test->ua)->toArray(false),
                ],
            ];
            ++$groupCounter;
        }

        $newCounter = count($outputDetector);

        $this->logger->info('    contains now ' . $newCounter . ' tests');

        if ($newCounter < 1) {
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
     *
     * @return \UaResult\Result\Result
     */
    private function handleTest($useragent)
    {
        $this->logger->info('        rewriting');

        $result = (new Detector($this->cache, $this->logger))->getBrowser($useragent);

        /* rewrite browsers */

        $this->logger->info('        rewriting browser');

        /** @var \UaResult\Browser\BrowserInterface $browser */
        $browser = clone $result->getBrowser();

        if (null === $browser) {
            $browser = new Browser(null);
        }

        /* rewrite platforms */

        $this->logger->info('        rewriting platform');

        $platform = clone $result->getOs();

        if (null === $platform) {
            $platform = new Os(null, null);
        }

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
            && !in_array($device->getDeviceName(), ['general Desktop', 'general Apple Device'])
            && false !== mb_stripos($device->getDeviceName(), 'general')
        ) {
            try {
                $regexFactory = new RegexFactory($this->cache, $this->logger);
                $regexFactory->detect($normalizedUa);
                list($device) = $regexFactory->getDevice();
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

                if (null === $device || in_array($device->getDeviceName(), [null, 'unknown'])) {
                    $device = new Device(null, null);
                }
            } catch (NotFoundException $e) {
                $this->logger->debug($e);

                $device   = clone $result->getDevice();
                $replaced = false;

                if (null === $device || in_array($device->getDeviceName(), [null, 'unknown'])) {
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
                    list($device) = $deviceLoader->load('general mobile device', $normalizedUa);
                } catch (\Exception $e) {
                    $this->logger->crit($e);

                    $device = new Device(null, null);
                }
            } catch (NoMatchException $e) {
                $this->logger->debug($e);

                $device = clone $result->getDevice();

                if (null === $device || in_array($device->getDeviceName(), [null, 'unknown'])) {
                    $device = new Device(null, null);
                }
            } catch (\Exception $e) {
                $this->logger->error($e);

                $device = clone $result->getDevice();

                if (null === $device || in_array($device->getDeviceName(), [null, 'unknown'])) {
                    $device = new Device(null, null);
                }
            }
        }

        /* rewrite engines */

        /** @var \UaResult\Engine\EngineInterface $engine */
        $engine = clone $result->getEngine();

        if (null === $engine) {
            $engine = new Engine(null);
        }

        $this->logger->info('        generating result');

        $request = (new GenericRequestFactory())->createRequestFromString($useragent);

        return new Result($request, $device, $platform, $browser, $engine);
    }
}
