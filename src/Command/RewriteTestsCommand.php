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
use BrowscapHelper\Reader;
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
        $logger = new Logger('browser-detector-helper');
        $logger->pushHandler(new Handler\NullHandler());
        $logger->pushHandler(new Handler\StreamHandler('error.log', Logger::ERROR));

        $adapter  = new Local(__DIR__ . '/../../cache/');
        $cache    = new FilesystemCachePool(new Filesystem($adapter));
        $detector = new BrowserDetector($cache, $logger);

        $sourceDirectory = 'vendor/mimmi20/browser-detector/tests/issues/';

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

        $checks = [];
        $data   = [];
        $g      = null;
        $c      = 0;

        foreach ($files as $fullFilename) {
            $file  = new \SplFileInfo($sourceDirectory . DIRECTORY_SEPARATOR . $fullFilename);
            $group = $groups[$fullFilename];

            if ($g !== $group) {
                $c = 0;
                $g = $group;
            }

            $counter = $this->handleFile($output, $file, $detector, $data, $checks, $cache, $c);

            if (!$counter) {
                continue;
            }

            $testCounter[$group][$fullFilename] += $counter;
        }

        $circleFile      = 'vendor/mimmi20/browser-detector/circle.yml';
        $circleciContent = 'machine:
  php:
    version: 7.0.4
  timezone:
    Europe/Berlin

dependencies:
  override:
    - composer update --optimize-autoloader --prefer-dist --prefer-stable --no-interaction --no-progress

test:
  override:
    - mkdir -p $CIRCLE_TEST_REPORTS/phpunit
    - vendor/bin/phpunit -c phpunit.library.xml --exclude-group useragenttest --coverage-text --colors=auto --log-junit $CIRCLE_TEST_REPORTS/phpunit/junit.xml
    #- vendor/bin/phpunit -c phpunit.compare.xml --no-coverage --group useragenttest --colors=auto
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
            $circleciContent .= PHP_EOL;
            $circleciContent .= '    #' . str_pad(
                $count,
                6,
                ' ',
                STR_PAD_LEFT
            ) . ' test' . ($count !== 1 ? 's' : '') . PHP_EOL;
            //$circleciContent .= '    - php -n vendor/bin/phpunit -c phpunit.regex.xml --no-coverage --group ' . $group . ' --colors=auto --columns 117 tests/RegexesTest/T' . $group . 'Test.php' . PHP_EOL;
            $circleciContent .= '    - php -n vendor/bin/phpunit -c phpunit.compare.xml --no-coverage --group ' . $group . ' --colors=auto --columns 117 tests/UserAgentsTest/T' . $group . 'Test.php' . PHP_EOL;

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
     * @param string $userAgent
     * @param array  $expectedProperties
     *
     * @throws \\Exception
     * @group  integration
     * @group  useragenttest
     * @group  ' . $group . '
     */
    public function testUserAgents($userAgent, $expectedProperties)
    {
        if (!is_array($expectedProperties) || !count($expectedProperties)) {
            self::markTestSkipped(\'Could not run test - no properties were defined to test\');
        }

        parent::testUserAgents($userAgent, $expectedProperties);
    }
}
';
            $testFile = 'vendor/mimmi20/browser-detector/tests/UserAgentsTest/T' . $group . 'Test.php';
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
     * @param int                                               $c
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
        &$c
    ) {
        $output->writeln('file ' . $file->getBasename());
        $output->writeln('    checking ...');

        /** @var $file \SplFileInfo */
        if (!$file->isFile() || !in_array($file->getExtension(), ['php', 'json'])) {
            return 0;
        }

        $output->writeln('    reading ...');

        switch ($file->getExtension()) {
            case 'php':
                $reader = new Reader\BrowscapTestReader();
                $reader->setLocalFile($file->getPathname());

                $tests = $reader->getTests();
                break;
            case 'json':
                $reader = new Reader\DetectorTestReader();
                $reader->setLocalFile($file->getPathname());

                $tests = $reader->getTests();
                break;
            default:
                return 0;
        }

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

        $counter = count(get_object_vars($tests));

        if ($counter < 1) {
            $output->writeln('    file does not contain any test');
            unlink($file->getPathname());

            return 0;
        }

        if (1 === $counter) {
            $output->writeln('    contains 1 test');
        } else {
            $output->writeln('    contains ' . $counter . ' tests');
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

            $outputDetector += $this->handleTest($output, $test, $detector, $key, $cache, $c);
            ++$c;
        }

        $newCounter = count($outputDetector);

        $output->writeln('    contains now ' . $newCounter . ' tests');

        if ($newCounter < 1) {
            $output->writeln('    all tests are removed from the file');
            unlink($file->getPathname());

            return 0;
        } elseif ($newCounter < $counter) {
            $output->writeln('    ' . ($counter - $newCounter) . ' test(s) is/are removed from the file');
        }

        $basename = $file->getBasename('.' . $file->getExtension());

        $output->writeln('    removing old file');
        unlink($file->getPathname());

        $output->writeln('    rewriting file');

        file_put_contents(
            $file->getPath() . '/' . $basename . '.json',
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
     *
     * @return array
     */
    private function handleTest(
        OutputInterface $output,
        \stdClass $test,
        BrowserDetector $detector,
        $key,
        CacheItemPoolInterface $cache,
        $counter
    ) {
        $output->writeln('    processing Test ' . $key . ' ...');
        $output->writeln('        ua: ' . $test->ua);

        /** rewrite test numbers */

        if (preg_match('/^browscap\-issue\-(\d+)\-(\d+)$/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$08d', (int) $matches[1]) . '-' . sprintf('%1$08d', (int) $matches[2]);
        } elseif (preg_match('/^browscap\-issue\-(\d+)$/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$08d', (int) $matches[1]) . '-' . sprintf('%1$08d', 0);
        } elseif (preg_match('/^browscap\-issue\-([^\-]+)\-(.*)$/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$08d', 0) . '-' . sprintf('%1$08d', $counter);
        } elseif (preg_match('/^browscap\-issue\-(\d+)\-(.*)$/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$08d', (int) $matches[1]) . '-' . sprintf('%1$08d', $counter);
        } elseif (preg_match('/^test\-(\d+)\-(\d+)$/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$08d', (int) $matches[1]) . '-' . sprintf('%1$08d', (int) $matches[2]);
        } elseif (preg_match('/^test\-(\d+)$/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$08d', (int) $matches[1]) . '-' . sprintf('%1$08d', 0);
        } elseif (preg_match('/^test\-(\d+)\-test(\d+)$/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$08d', (int) $matches[1]) . '-' . sprintf('%1$08d', (int) $matches[2]);
        }

        /** rewrite platforms */

        $output->writeln('        rewriting platform');

        try {
            list($platformCodename, $platformMarketingname, $platformVersion, $platformBits, $platformMaker, $platformBrandname, $platform) = $this->rewritePlatforms(
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

            if (isset($test->properties->Platform_Brand_Name)) {
                $platformBrandname = $test->properties->Platform_Brand_Name;
            } else {
                $output->writeln('["' . $key . '"] platform brand for UA "' . $test->ua . '" is missing, using "unknown" instead');

                $platformBrandname = 'unknown';
            }

            $platform = null;
        }

        /** @var $platform OsInterface|null */

        $output->writeln('        rewriting device');

        /** rewrite devices */

        try {
            list($deviceBrand, $deviceCode, $devicePointing, $deviceType, $deviceMaker, $deviceName, $deviceOrientation, $device) = $this->rewriteDevice(
                $test,
                $cache,
                $platform,
                $detector
            );

            /** @var $deviceType \UaDeviceType\TypeInterface */
            /** @var $device \UaResult\Device\DeviceInterface */

            if (null !== ($platform = $device->getPlatform())) {
                $platformCodename      = $platform->getName();
                $platformMarketingname = $platform->getMarketingName();
                $platformVersion       = $platform->getVersion()->getVersion();
                $platformBits          = $platform->getBits();
                $platformMaker         = $platform->getManufacturer();
                $platformBrandname     = $platform->getBrand();
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

            $device = null;
        }

        if (!($deviceType instanceof \UaDeviceType\TypeInterface)) {
            if (is_string($deviceType)) {
                $className = '\UaDeviceType\\' . $test->properties->Device_Type;

                if (class_exists($className)) {
                    $deviceType = new $className();
                } else {
                    $deviceType = new \UaDeviceType\Unknown();
                }
            } else {
                $deviceType = new \UaDeviceType\Unknown();
            }
        }

        $output->writeln('        generating result');

        /** generate result */
        return [
            $key => [
                'ua'         => $test->ua,
                'properties' => [
                    'Browser_Name'            => $test->properties->Browser_Name,
                    'Browser_Type'            => $test->properties->Browser_Type,
                    'Browser_Bits'            => $test->properties->Browser_Bits,
                    'Browser_Maker'           => $test->properties->Browser_Maker,
                    'Browser_Modus'           => $test->properties->Browser_Modus,
                    'Browser_Version'         => $test->properties->Browser_Version,
                    'Platform_Codename'       => $platformCodename,
                    'Platform_Marketingname'  => $platformMarketingname,
                    'Platform_Version'        => $platformVersion,
                    'Platform_Bits'           => $platformBits,
                    'Platform_Maker'          => $platformMaker,
                    'Platform_Brand_Name'     => $platformBrandname,
                    'Device_Name'             => $deviceName,
                    'Device_Maker'            => $deviceMaker,
                    'Device_Type'             => get_class($deviceType),
                    'Device_Pointing_Method'  => $devicePointing,
                    'Device_Dual_Orientation' => $deviceOrientation,
                    'Device_Code_Name'        => $deviceCode,
                    'Device_Brand_Name'       => $deviceBrand,
                    'RenderingEngine_Name'    => $test->properties->RenderingEngine_Name,
                    'RenderingEngine_Version' => $test->properties->RenderingEngine_Version,
                    'RenderingEngine_Maker'   => $test->properties->RenderingEngine_Maker,
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
     * @return array
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

        if (isset($test->properties->Platform_Brand_Name)) {
            $platformBrandname = $test->properties->Platform_Brand_Name;
        } else {
            $output->writeln('["' . $key . '"] platform brand for UA "' . $test->ua . '" is missing, using "unknown" instead');

            $platformBrandname = 'unknown';
        }

        list(, , , , , , ,
            $platformCodename,
            $platformMarketingname,
            $platformMaker,
            $platformBrandname,
            $platformVersion, ,
            $platformBits,
            $platform) = (new Helper\Platform())->detect($cache, $test->ua, $detector, $platformCodename, $platformMarketingname, $platformMaker, $platformBrandname, $platformVersion);

        return [
            $platformCodename,
            $platformMarketingname,
            $platformVersion,
            $platformBits,
            $platformMaker,
            $platformBrandname,
            $platform,
        ];
    }

    /**
     * @param \stdClass              $test
     * @param CacheItemPoolInterface $cache
     * @param OsInterface            $platform
     * @param BrowserDetector        $detector
     *
     * @throws \BrowserDetector\Loader\NotFoundException
     * @return array
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

        list(
            $deviceName,
            $deviceMaker,
            $deviceType,
            $devicePointing,
            $deviceCode,
            $deviceBrand, , ,
            $deviceOrientation,
            $device) = (new Helper\Device())->detect(
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

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        }

        return [
            $deviceBrand,
            $deviceCode,
            $devicePointing,
            $deviceType,
            $deviceMaker,
            $deviceName,
            $deviceOrientation,
            $device,
        ];
    }
}
