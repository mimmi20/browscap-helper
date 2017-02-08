<?php
/**
 * Copyright (c) 1998-2014 Browser Capabilities Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   Browscap
 * @copyright  1998-2014 Browser Capabilities Project
 * @license    MIT
 */

namespace BrowscapHelper\Command;

use BrowscapHelper\Helper;
use BrowserDetector\BrowserDetector;
use BrowserDetector\Loader\DeviceLoader;
use BrowserDetector\Loader\NotFoundException;
use Cache\Adapter\Filesystem\FilesystemCachePool;
use League\Flysystem\Adapter\Local;
use League\Flysystem\Filesystem;
use Monolog\Handler;
use Monolog\Logger;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UaResult\Os\OsInterface;
use UaResult\Result\Result;
use UaResult\Result\ResultFactory;
use Wurfl\Request\GenericRequestFactory;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class RewriteTestsCommand extends Command
{
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
     * @return null|int        null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('init logger ...');
        $logger = new Logger('browser-detector-helper');
        $logger->pushHandler(new Handler\NullHandler());
        $logger->pushHandler(new Handler\StreamHandler('error.log', Logger::ERROR));

        $output->writeln('init cache ...');
        $adapter  = new Local(__DIR__ . '/../../cache/');
        $cache    = new FilesystemCachePool(new Filesystem($adapter));

        $output->writeln('init detector ...');
        $detector = new BrowserDetector($cache, $logger);

        $sourceDirectory = 'vendor/mimmi20/browser-detector-tests/tests/issues/';

        $filesArray  = scandir($sourceDirectory, SCANDIR_SORT_ASCENDING);
        $files       = [];
        $testCounter = [];
        $groups      = [];

        foreach ($filesArray as $filename) {
            if (in_array($filename, ['.', '..'])) {
                continue;
            }

            if (!is_dir($sourceDirectory . DIRECTORY_SEPARATOR . $filename)) {
                $files[] = $filename;
                $output->writeln('file ' . $filename . ' is out of strcture');
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

        $checks  = [];
        $data    = [];
        $g       = null;
        $counter = 0;

        foreach ($files as $fullFilename) {
            $file  = new \SplFileInfo($sourceDirectory . DIRECTORY_SEPARATOR . $fullFilename);
            $group = $groups[$fullFilename];

            if ($g !== $group) {
                $counter = 0;
                $g       = $group;
            }

            $newCounter = $this->handleFile($output, $file, $detector, $data, $checks, $cache, $logger, $counter);

            if (!$newCounter) {
                continue;
            }

            $testCounter[$group][$fullFilename] += $newCounter;
        }

        $circleFile      = 'vendor/mimmi20/browser-detector-tests/circle.yml';
        $circleciContent = 'machine:
  php:
    version: 7.0.4
  timezone:
    Europe/Berlin

dependencies:
  override:
    - composer update --optimize-autoloader --prefer-dist --prefer-stable --no-interaction --no-progress

test:
  override:';

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
            $circleciContent .= PHP_EOL;
            $circleciContent .= '    #' . str_pad($count, 6, ' ', STR_PAD_LEFT) . ' test' . ($count !== 1 ? 's' : '');
            $circleciContent .= PHP_EOL;
            $circleciContent .= '    - php -n vendor/bin/phpunit --no-coverage --group ';
            $circleciContent .= $group . ' --colors=auto --columns 117 tests/UserAgentsTest/T' . $group . 'Test.php';
            $circleciContent .= PHP_EOL;

            $testContent = '<?php
/**
 * Copyright (c) 2012-' . date('Y') . ' Thomas Mueller
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   CompareTest
 *
 * @copyright 2012-' . date('Y') . ' Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */

namespace BrowserDetectorTest\UserAgentsTest;

use BrowserDetectorTest\UserAgentsTest;
use UaResult\Result\Result;

/**
 * Class UserAgentsTest
 *
 * @category   CompareTest
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 * @group      useragenttest
 */
class T' . $group . 'Test extends UserAgentsTest
{
    /**
     * @var string
     */
    protected $sourceDirectory = \'tests/issues/' . $group . '/\';

    /**
     * @dataProvider userAgentDataProvider
     *
     * @param string                  $userAgent
     * @param \UaResult\Result\Result $expectedResult
     *
     * @throws \\Exception
     * @group  integration
     * @group  useragenttest
     * @group  ' . $group . '
     */
    public function testUserAgents($userAgent, Result $expectedResult)
    {
        parent::testUserAgents($userAgent, $expectedResult);
    }
}
';
            $testFile = 'vendor/mimmi20/browser-detector-tests/tests/UserAgentsTest/T' . $group . 'Test.php';
            file_put_contents($testFile, $testContent);
        }

        $output->writeln('writing ' . $circleFile . ' ...');
        file_put_contents($circleFile, $circleciContent);

        $output->writeln('done');
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \SplFileInfo                                      $file
     * @param BrowserDetector                                   $detector
     * @param array                                             $data
     * @param array                                             $checks
     * @param \Psr\Cache\CacheItemPoolInterface                 $cache
     * @param \Psr\Log\LoggerInterface                          $logger
     * @param int                                               $counter
     *
     * @return int
     */
    private function handleFile(
        OutputInterface $output,
        \SplFileInfo $file,
        BrowserDetector $detector,
        array &$data,
        array &$checks,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger,
        &$counter
    ) {
        $output->writeln('file ' . $file->getBasename());
        $output->writeln('    checking ...');

        /** @var $file \SplFileInfo */
        if (!$file->isFile() || $file->getExtension() !== 'json') {
            return 0;
        }

        $output->writeln('    reading ...');

        $tests = json_decode(file_get_contents($file->getPathname()));

        if (is_array($tests)) {
            $tests = (object) $tests;
        }

        $oldCounter = count(get_object_vars($tests));

        if ($oldCounter < 1) {
            $output->writeln('    file does not contain any test');
            unlink($file->getPathname());

            return 0;
        }

        if (1 === $oldCounter) {
            $output->writeln('    contains 1 test');
        } else {
            $output->writeln('    contains ' . $oldCounter . ' tests');
        }

        $output->writeln('    processing ...');
        $outputDetector = [];

        foreach ($tests as $key => $test) {
            if (isset($data[$key])) {
                // Test data is duplicated for key
                $output->writeln('    Test data is duplicated for key "' . $key . '"');
                unset($tests->$key);
                continue;
            }

            if (is_array($test)) {
                $test = (object) $test;
            }

            if (isset($checks[$test->ua])) {
                // UA was added more than once
                $output->writeln('    UA "' . $test->ua . '" added more than once, now for key "' . $key . '", before for key "' . $checks[$test->ua] . '"');
                unset($tests->$key);
                continue;
            }

            $data[$key]        = $test->ua;
            $checks[$test->ua] = $key;

            $output->writeln('    processing Test ' . $key . ' ...');

            $outputDetector += [
                $key => [
                    'ua'     => $test->ua,
                    'result' => $this->handleTest($output, $test, $detector, $cache, $logger)->toArray(),
                ],
            ];
            ++$counter;
        }

        $newCounter = count($outputDetector);

        $output->writeln('    contains now ' . $newCounter . ' tests');

        if ($newCounter < 1) {
            $output->writeln('    all tests are removed from the file');
            unlink($file->getPathname());

            return 0;
        } elseif ($newCounter < $oldCounter) {
            $output->writeln('    ' . ($oldCounter - $newCounter) . ' test(s) is/are removed from the file');
        }

        $output->writeln('    removing old file');
        unlink($file->getPathname());

        $output->writeln('    rewriting file');

        file_put_contents(
            $file->getPathname(),
            json_encode($outputDetector, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . PHP_EOL
        );

        return $newCounter;
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \stdClass                                         $test
     * @param BrowserDetector                                   $detector
     * @param \Psr\Cache\CacheItemPoolInterface                 $cache
     * @param \Psr\Log\LoggerInterface                          $logger
     *
     * @return \UaResult\Result\Result
     */
    private function handleTest(
        OutputInterface $output,
        \stdClass $test,
        BrowserDetector $detector,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger
    ) {
        $output->writeln('        ua: ' . $test->ua);

        $result = (new ResultFactory())->fromArray($cache, $logger, (array) $test->result);

        /** rewrite browsers */

        $browser = $result->getBrowser();

        /** rewrite platforms */

        $output->writeln('        rewriting platform');

        $platform = $result->getOs();

        try {
            list(, , , , , , , , , $platform) = (new Helper\Platform())->detect($cache, $test->ua, $detector, $platform->getName(), $platform->getMarketingName(), $platform->getManufacturer(), $platform->getVersion()->getVersion());
        } catch (NotFoundException $e) {
            $logger->warning($e);
        }

        /** @var $platform OsInterface|null */

        $output->writeln('        rewriting device');

        /** rewrite devices */

        $device     = $result->getDevice();
        $deviceCode = $device->getDeviceName();

        try {
            $device = (new Helper\Device())->detect(
                $cache,
                $test->ua,
                $platform,
                $detector,
                $deviceCode,
                $device->getBrand()->getBrandName(),
                $device->getPointingMethod(),
                $device->getType()->getName(),
                $device->getManufacturer()->getName(),
                $device->getMarketingName(),
                $device->getDualOrientation()
            );
            /** @var $deviceType \UaDeviceType\TypeInterface */

            if ($deviceCode === 'unknown'
                || $deviceCode === null
                || (false !== stripos($deviceCode, 'general') && (!in_array($deviceCode, ['general Mobile Device', 'general Mobile Phone', 'general Desktop', 'general Apple Device'])))
            ) {
                $deviceLoader = new DeviceLoader($cache);
                $device       = $deviceLoader->load('unknown', $test->ua);
            }

            /** @var $deviceType \UaDeviceType\TypeInterface */
            /** @var $device \UaResult\Device\DeviceInterface */
        } catch (NotFoundException $e) {
            $logger->warning($e);
        }

        /** rewrite engines */

        $engine = $result->getEngine();

        $output->writeln('        generating result');

        $request = (new GenericRequestFactory())->createRequestForUserAgent($test->ua);

        return new Result($request, $device, $platform, $browser, $engine);
    }
}
