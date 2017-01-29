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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use UaResult\Device\Device;
use UaResult\Os\OsInterface;

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

            $newCounter = $this->handleFile($output, $file, $detector, $data, $checks, $cache, $counter, $group);

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
            $circleciContent .= '    - php -n vendor/bin/phpunit -c phpunit.compare.xml --no-coverage --group ';
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
     * @param CacheItemPoolInterface                            $cache
     * @param int                                               $counter
     * @param int                                               $group
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
        &$counter,
        $group
    ) {
        $output->writeln('file ' . $file->getBasename());
        $output->writeln('    checking ...');

        /** @var $file \SplFileInfo */
        if (!$file->isFile() || $file->getExtension() !== 'json') {
            return 0;
        }

        $output->writeln('    reading ...');

        $tests = json_decode(file_get_contents($file->getPathname()));

        if (empty($tests)) {
            $output->writeln('    removing empty file');
            unlink($file->getPathname());

            return 0;
        }

        if (is_array($tests)) {
            $tests = (object) $tests;
        }

        if (empty($tests)) {
            $output->writeln('    file does not contain any test');
            unlink($file->getPathname());

            return 0;
        }

        $oldCounter = count(get_object_vars($tests));

        if ($oldCounter < 1) {
            $output->writeln('    file does not contain any test, removing');
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

            if (is_array($test->properties)) {
                $test->properties = (object) $test->properties;
            }

            if (isset($checks[$test->ua])) {
                // UA was added more than once
                $output->writeln('    UA "' . $test->ua . '" added more than once, now for key "' . $key . '", before for key "' . $checks[$test->ua] . '"');
                unset($tests->$key);
                continue;
            }

            $data[$key]        = $test;
            $checks[$test->ua] = $key;

            $outputDetector += $this->handleTest($output, $test, $detector, $key, $cache, $counter, $group);
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
     * @param string                                            $key
     * @param CacheItemPoolInterface                            $cache
     * @param int                                               $counter
     * @param int                                               $group
     *
     * @return array
     */
    private function handleTest(
        OutputInterface $output,
        \stdClass $test,
        BrowserDetector $detector,
        $key,
        CacheItemPoolInterface $cache,
        $counter,
        $group
    ) {
        $output->writeln('    processing Test ' . $key . ' ...');
        $output->writeln('        ua: ' . $test->ua);

        /** rewrite test numbers */

        $key = 'test-' . sprintf('%1$08d', (int) $group) . '-' . sprintf('%1$08d', $counter);

        /** rewrite browsers */

        if (isset($test->properties->Browser_Name)) {
            $browserName = $test->properties->Browser_Name;
        } else {
            $output->writeln('["' . $key . '"] browser name for UA "' . $test->ua . '" is missing, using "unknown" instead');

            $browserName = 'unknown';
        }

        if (isset($test->properties->Browser_Type)) {
            $browserType = $test->properties->Browser_Type;
        } else {
            $output->writeln('["' . $key . '"] browser type for UA "' . $test->ua . '" is missing, using "unknown" instead');

            $browserType = 'unknown';
        }

        if (isset($test->properties->Browser_Bits)) {
            $browserBits = $test->properties->Browser_Bits;
        } else {
            $output->writeln('["' . $key . '"] browser bits for UA "' . $test->ua . '" is missing, using "0" instead');

            $browserBits = 0;
        }

        if (isset($test->properties->Browser_Maker)) {
            $browserMaker = $test->properties->Browser_Maker;
        } else {
            $output->writeln('["' . $key . '"] browser maker for UA "' . $test->ua . '" is missing, using "unknown" instead');

            $browserMaker = 'unknown';
        }

        if (isset($test->properties->Browser_Modus)) {
            $browserModus = $test->properties->Browser_Modus;
        } else {
            $output->writeln('["' . $key . '"] browser modus for UA "' . $test->ua . '" is missing, using "unknown" instead');

            $browserModus = 'unknown';
        }

        if (isset($test->properties->Browser_Version)) {
            $browserVersion = $test->properties->Browser_Version;
        } else {
            $output->writeln('["' . $key . '"] browser version for UA "' . $test->ua . '" is missing, using "0.0.0" instead');

            $browserVersion = '0.0.0';
        }

        /** rewrite platforms */

        $output->writeln('        rewriting platform');

        try {
            $platform = $this->rewritePlatforms(
                $output,
                $test,
                $detector,
                $key,
                $cache
            );
        } catch (NotFoundException $e) {
            if (isset($test->properties->Platform_Codename)) {
                $platformCodename = $test->properties->Platform_Codename;
            } elseif (isset($test->properties->Platform_Name)) {
                $platformCodename = $test->properties->Platform_Name;
            } else {
                $output->writeln('["' . $key . '"] platform name for UA "' . $test->ua . '" is missing, using "unknown" instead');

                $platformCodename = 'unknown';
            }

            if (isset($test->properties->Platform_Marketingname)) {
                $platformMarketingname = $test->properties->Platform_Marketingname;
            } else {
                $platformMarketingname = $platformCodename;
            }

            if (isset($test->properties->Platform_Version)) {
                $platformVersion = $test->properties->Platform_Version;
            } else {
                $output->writeln('["' . $key . '"] platform version for UA "' . $test->ua . '" is missing, using "unknown" instead');

                $platformVersion = 'unknown';
            }

            if (isset($test->properties->Platform_Bits)) {
                $platformBits = $test->properties->Platform_Bits;
            } else {
                $output->writeln('["' . $key . '"] platform bits for UA "' . $test->ua . '" are missing, using "unknown" instead');

                $platformBits = 'unknown';
            }

            if (isset($test->properties->Platform_Maker)) {
                $platformMaker = $test->properties->Platform_Maker;
            } else {
                $output->writeln('["' . $key . '"] platform maker for UA "' . $test->ua . '" is missing, using "unknown" instead');

                $platformMaker = 'unknown';
            }

            $platform = new \UaResult\Os\Os($platformCodename, $platformMarketingname, $platformMaker, $platformVersion, $platformBits);
        }

        /** @var $platform OsInterface|null */

        $output->writeln('        rewriting device');

        /** rewrite devices */

        try {
            $device = $this->rewriteDevice(
                $test,
                $cache,
                $platform,
                $detector
            );

            /** @var $deviceType \UaDeviceType\TypeInterface */
            /** @var $device \UaResult\Device\DeviceInterface */

            if (null !== $device->getPlatform()) {
                $platform = $device->getPlatform();
            }
        } catch (NotFoundException $e) {
            if (isset($test->properties->Device_Name)) {
                $deviceName = $test->properties->Device_Name;
            } else {
                $deviceName = 'unknown';
            }

            if (isset($test->properties->Device_Maker)) {
                $deviceMaker = $test->properties->Device_Maker;
            } else {
                $deviceMaker = 'unknown';
            }

            if (isset($test->properties->Device_Type)) {
                $className = '\UaDeviceType\\' . $test->properties->Device_Type;

                if (class_exists($className)) {
                    $deviceType = new $className();
                } else {
                    $deviceType = new \UaDeviceType\Unknown();
                }
            } else {
                $deviceType = new \UaDeviceType\Unknown();
            }

            if (isset($test->properties->Device_Pointing_Method)) {
                $devicePointing = $test->properties->Device_Pointing_Method;
            } else {
                $devicePointing = null;
            }

            if (isset($test->properties->Device_Code_Name)) {
                $deviceCode = $test->properties->Device_Code_Name;
            } else {
                $deviceCode = 'unknown';
            }

            if (isset($test->properties->Device_Brand_Name)) {
                $deviceBrand = $test->properties->Device_Brand_Name;
            } else {
                $deviceBrand = 'unknown';
            }

            if (isset($test->properties->Device_Dual_Orientation)) {
                $deviceOrientation = $test->properties->Device_Dual_Orientation;
            } else {
                $deviceOrientation = false;
            }

            $device = new Device(
                $deviceCode,
                $deviceName,
                $deviceMaker,
                $deviceBrand,
                null,
                $platform,
                $deviceType,
                $devicePointing,
                null,
                null,
                $deviceOrientation
            );
        }

        /** rewrite engines */

        if (isset($test->properties->RenderingEngine_Name)) {
            $engineName = $test->properties->RenderingEngine_Name;
        } else {
            $output->writeln('["' . $key . '"] engine name for UA "' . $test->ua . '" is missing, using "unknown" instead');

            $engineName = 'unknown';
        }

        if (isset($test->properties->RenderingEngine_Version)) {
            $engineVersion = $test->properties->RenderingEngine_Version;
        } else {
            $output->writeln('["' . $key . '"] engine version for UA "' . $test->ua . '" is missing, using "0.0.0" instead');

            $engineVersion = '0.0.0';
        }

        if (isset($test->properties->RenderingEngine_Maker)) {
            $engineMaker = $test->properties->RenderingEngine_Maker;
        } else {
            $output->writeln('["' . $key . '"] engine maker for UA "' . $test->ua . '" is missing, using "unknown" instead');

            $engineMaker = 'unknown';
        }

        $output->writeln('        generating result');

        /** generate result */
        return [
            $key => [
                'ua'         => $test->ua,
                'result' => [
                    'Browser_Name'            => $browserName,
                    'Browser_Type'            => $browserType,
                    'Browser_Bits'            => $browserBits,
                    'Browser_Maker'           => $browserMaker,
                    'Browser_Modus'           => $browserModus,
                    'Browser_Version'         => $browserVersion,
                    'Platform_Codename'       => $platform->getName(),
                    'Platform_Marketingname'  => $platform->getMarketingName(),
                    'Platform_Version'        => $platform->getVersion()->getVersion(),
                    'Platform_Bits'           => $platform->getBits(),
                    'Platform_Maker'          => $platform->getManufacturer(),
                    'Device_Name'             => $device->getMarketingName(),
                    'Device_Maker'            => $device->getManufacturer(),
                    'Device_Type'             => get_class($deviceType),
                    'Device_Pointing_Method'  => $device->getPointingMethod(),
                    'Device_Dual_Orientation' => $device->getDualOrientation(),
                    'Device_Code_Name'        => $device->getDeviceName(),
                    'Device_Brand_Name'       => $device->getBrand(),
                    'RenderingEngine_Name'    => $engineName,
                    'RenderingEngine_Version' => $engineVersion,
                    'RenderingEngine_Maker'   => $engineMaker,
                ],
            ],
        ];
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @param \stdClass                                         $test
     * @param BrowserDetector                                   $detector
     * @param string                                            $key
     * @param CacheItemPoolInterface                            $cache
     *
     * @throws \BrowserDetector\Loader\NotFoundException
     * @throws \UnexpectedValueException
     * @return \UaResult\Os\OsInterface
     */
    private function rewritePlatforms(
        OutputInterface $output,
        \stdClass $test,
        BrowserDetector $detector,
        $key,
        CacheItemPoolInterface $cache
    ) {
        if (isset($test->properties->Platform_Codename)) {
            $platformCodename = $test->properties->Platform_Codename;
        } else {
            $output->writeln('["' . $key . '"] platform name for UA "' . $test->ua . '" is missing, using "unknown" instead');

            $platformCodename = 'unknown';
        }

        if (isset($test->properties->Platform_Marketingname)) {
            $platformMarketingname = $test->properties->Platform_Marketingname;
        } else {
            $platformMarketingname = $platformCodename;
        }

        if (isset($test->properties->Platform_Version)) {
            $platformVersion = $test->properties->Platform_Version;
        } else {
            $output->writeln('["' . $key . '"] platform version for UA "' . $test->ua . '" is missing, using "unknown" instead');

            $platformVersion = 'unknown';
        }

        if (isset($test->properties->Platform_Maker)) {
            $platformMaker = $test->properties->Platform_Maker;
        } else {
            $output->writeln('["' . $key . '"] platform maker for UA "' . $test->ua . '" is missing, using "unknown" instead');

            $platformMaker = 'unknown';
        }

        list(, , , , , , , , , $platform) = (new Helper\Platform())->detect($cache, $test->ua, $detector, $platformCodename, $platformMarketingname, $platformMaker, $platformVersion);

        return $platform;
    }

    /**
     * @param \stdClass              $test
     * @param CacheItemPoolInterface $cache
     * @param OsInterface            $platform
     * @param BrowserDetector        $detector
     *
     * @throws \BrowserDetector\Loader\NotFoundException
     * @return \UaResult\Device\DeviceInterface
     */
    private function rewriteDevice(\stdClass $test, CacheItemPoolInterface $cache, OsInterface $platform, BrowserDetector $detector)
    {
        if (isset($test->properties->Device_Code_Name)) {
            $deviceCode = $test->properties->Device_Code_Name;
        } else {
            $deviceCode = 'unknown';
        }

        if (isset($test->properties->Device_Brand_Name)) {
            $deviceBrand = $test->properties->Device_Brand_Name;
        } else {
            $deviceBrand = 'unknown';
        }

        if (isset($test->properties->Device_Pointing_Method)) {
            $devicePointing = $test->properties->Device_Pointing_Method;
        } else {
            $devicePointing = null;
        }

        if (isset($test->properties->Device_Type)) {
            $deviceType = $test->properties->Device_Type;
        } else {
            $deviceType = null;
        }

        if (isset($test->properties->Device_Maker)) {
            $deviceMaker = $test->properties->Device_Maker;
        } else {
            $deviceMaker = 'unknown';
        }

        if (isset($test->properties->Device_Name)) {
            $deviceName = $test->properties->Device_Name;
        } else {
            $deviceName = 'unknown';
        }

        if (isset($test->properties->Device_Dual_Orientation)) {
            $deviceOrientation = $test->properties->Device_Dual_Orientation;
        } else {
            $deviceOrientation = false;
        }

        /** @var \UaResult\Device\DeviceInterface $device */
        $device = (new Helper\Device())->detect(
                $cache,
                $test->ua,
                $platform,
                $detector,
                $deviceCode,
                $deviceBrand,
                $devicePointing,
                $deviceType,
                $deviceMaker,
                $deviceName,
                $deviceOrientation
            );
        /** @var $deviceType \UaDeviceType\TypeInterface */

        if ($deviceCode === 'unknown'
            || $deviceCode === null
            || (false !== stripos($deviceCode, 'general') && (!in_array($deviceCode, ['general Mobile Device', 'general Mobile Phone', 'general Desktop', 'general Apple Device'])))
        ) {
            $deviceLoader = new DeviceLoader($cache);
            $device       = $deviceLoader->load('unknown', $test->ua);
        }

        return $device;
    }
}
