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

use BrowscapHelper\Helper\Device;
use BrowscapHelper\Reader\BrowscapTestReader;
use BrowscapHelper\Reader\DetectorTestReader;
use BrowserDetector\BrowserDetector;
use BrowserDetector\Factory\Platform;
use BrowserDetector\Loader\NotFoundException;
use BrowserDetector\Loader\PlatformLoader;
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

                $files[] = $filename . DIRECTORY_SEPARATOR . $subdirFilename;
                $group   = $filename;

                if ('00000-browscap' === $filename) {
                    $group = '00000';
                }

                $testCounter[$group][$filename . DIRECTORY_SEPARATOR . $subdirFilename] = 0;
            }
        }

        $checks = [];
        $data   = [];

        foreach ($files as $filename) {
            $file = new \SplFileInfo($sourceDirectory . DIRECTORY_SEPARATOR . $filename);

            $output->writeln('file ' . $file->getBasename());
            $output->writeln('    checking ...');

            /** @var $file \SplFileInfo */
            if (!$file->isFile() || !in_array($file->getExtension(), ['php', 'json'])) {
                continue;
            }

            $output->writeln('    reading ...');

            switch ($file->getExtension()) {
                case 'php':
                    $reader = new BrowscapTestReader();
                    $reader->setLocalFile($file->getPathname());

                    $tests = $reader->getTests();
                    break;
                case 'json':
                    $reader = new DetectorTestReader();
                    $reader->setLocalFile($file->getPathname());

                    $tests = $reader->getTests();
                    break;
                default:
                    continue;
            }

            if (empty($tests)) {
                $output->writeln('    removing empty file');
                unlink($file->getPathname());

                continue;
            }

            if (is_array($tests)) {
                $tests = (object) $tests;
            }

            if (empty($tests)) {
                $output->writeln('    file does not contain any test');
                continue;
            }

            foreach ($testCounter as $group => $filesinGroup) {
                foreach (array_keys($filesinGroup) as $fileinGroup) {
                    if ($fileinGroup !== $filename) {
                        continue;
                    }

                    $testCounter[$group][$fileinGroup] += count($tests);
                }
            }

            $output->writeln('    processing ...');
            $this->handleFile($output, $tests, $file, $detector, $data, $checks, $cache);
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
            $circleciContent .= '    #' . str_pad(
                $count,
                6,
                ' ',
                STR_PAD_LEFT
            ) . ' test' . ($count !== 1 ? 's' : '') . PHP_EOL;
            $circleciContent .= '    #- vendor/bin/phpunit -c phpunit.regex.xml --no-coverage --group ' . $group . ' --colors=auto --columns 117 --test-suffix=' . $group . 'Test.php' . PHP_EOL;
            $circleciContent .= '    - vendor/bin/phpunit -c phpunit.compare.xml --no-coverage --group ' . $group . ' --colors=auto --columns 117 --test-suffix=' . $group . 'Test.php' . PHP_EOL;
        }

        $output->writeln('writing ' . $circleFile . ' ...');
        file_put_contents($circleFile, $circleciContent);

        $output->writeln('done');
    }

    /**
     * @param \stdClass              $tests
     * @param \SplFileInfo           $file
     * @param BrowserDetector        $detector
     * @param array                  $data
     * @param array                  $checks
     * @param CacheItemPoolInterface $cache
     */
    private function handleFile(
        OutputInterface $output,
        \stdClass $tests,
        \SplFileInfo $file,
        BrowserDetector $detector,
        array &$data,
        array &$checks,
        CacheItemPoolInterface $cache
    ) {
        $outputDetector = [];

        foreach ($tests as $key => $test) {
            if (isset($data[$key])) {
                // Test data is duplicated for key
                $output->writeln('    Test data is duplicated for key "' . $key . '"');
                unset($tests[$key]);
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
                unset($tests[$key]);
                continue;
            }

            $data[$key]        = $test;
            $checks[$test->ua] = $key;

            $output->writeln('    processing Test ' . $key . ' ...');
            $outputDetector += $this->handleTest($output, $test, $detector, $key, $cache);
        }

        $basename = $file->getBasename('.' . $file->getExtension());

        $output->writeln('    removing old file');
        unlink($file->getPathname());

        $output->writeln('    rewriting file');

        file_put_contents(
            $file->getPath() . '/' . $basename . '.json',
            json_encode($outputDetector, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
        );
    }

    /**
     * @param \stdClass              $test
     * @param BrowserDetector        $detector
     * @param string                 $key
     * @param CacheItemPoolInterface $cache
     *
     * @return array
     */
    private function handleTest(
        OutputInterface $output,
        \stdClass $test,
        BrowserDetector $detector,
        $key,
        CacheItemPoolInterface $cache
    ) {
        /** rewrite test numbers */

        if (preg_match('/^test\-(\d+)\-(\d+)$/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$05d', (int) $matches[1]) . '-' . sprintf('%1$05d', (int) $matches[2]);
        } elseif (preg_match('/^test\-(\d+)$/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$05d', (int) $matches[1]) . '-00000';
        } elseif (preg_match('/^test\-(\d+)\-test(\d+)$/', $key, $matches)) {
            $key = 'test-' . sprintf('%1$05d', (int) $matches[1]) . '-' . sprintf('%1$05d', (int) $matches[2]);
        }

        /** rewrite platforms */

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

        /** rewrite devices */

        try {
            list($deviceBrand, $deviceCode, $devicePointing, $deviceType, $deviceMaker, $deviceName, $deviceOrientation, $device) = $this->rewriteDevice(
                $test,
                $cache,
                $platform
            );

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
                $deviceType = $test->properties->Device_Type;
            } else {
                $deviceType = 'unknown';
            }

            if (isset($test->properties->Device_Pointing_Method)) {
                $devicePointing = $test->properties->Device_Pointing_Method;
            } else {
                $devicePointing = 'unknown';
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
                $deviceOrientation = 'unknown';
            }

            $device = null;
        }

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
                    'Device_Type'             => $deviceType,
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

        $useragent      = $test->ua;
        $platformLoader = new PlatformLoader($cache);

        // rewrite Darwin platform
        if ('Darwin' === $platformCodename) {
            $platform = (new Platform\DarwinFactory($cache, $platformLoader))->detect($useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif ('Windows' === $platformCodename) {
            $platform = (new Platform\WindowsFactory($cache, $platformLoader))->detect($useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif ('Android' === $platformCodename && preg_match('/windows phone/i', $useragent)) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (preg_match('/Puffin\/[\d\.]+I(T|P)/', $useragent)) {
            $platform = $platformLoader->load('ios', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (preg_match('/Puffin\/[\d\.]+A(T|P)/', $useragent)) {
            $platform = $platformLoader->load('android', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (preg_match('/Puffin\/[\d\.]+W(T|P)/', $useragent)) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'linux mint')) {
            $platform = $platformLoader->load('linux mint', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'kubuntu')) {
            $platform = $platformLoader->load('kubuntu', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'ubuntu')) {
            $platform = $platformLoader->load('ubuntu', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (preg_match('/HP\-UX/', $useragent)) {
            $platform = $platformLoader->load('hp-ux', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif ('Windows' === $platformCodename && preg_match('/windows ce/i', $useragent)) {
            $platform = $platformLoader->load('windows ce', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (preg_match('/(red hat|redhat)/i', $useragent)) {
            $platform = $platformLoader->load('redhat linux', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif ('Windows Mobile OS' === $platformCodename && preg_match('/Windows Mobile; WCE/', $useragent)) {
            $platform = $platformLoader->load('windows ce', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows Phone')) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'wds')) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'wpdesktop')) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'xblwp7')) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'zunewp7')) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Tizen')) {
            $platform = $platformLoader->load('tizen', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows CE')) {
            $platform = $platformLoader->load('windows ce', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (preg_match('/MIUI/', $useragent)) {
            $platform = $platformLoader->load('miui os', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Linux; Android')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Linux; U; Android')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'U; Adr')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Android') || false !== strpos($useragent, 'MTK')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'UCWEB/2.0 (Linux; U; Opera Mini')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Linux; GoogleTV')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'OpenBSD')) {
            $platform = $platformLoader->load('openbsd', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Symbian') || false !== strpos($useragent, 'Series 60')) {
            $platform = $platformLoader->load('symbian', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'MIDP')) {
            $platform = $platformLoader->load('java', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 10.0')) {
            $platform = $platformLoader->load('windows nt 10.0', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 6.4')) {
            $platform = $platformLoader->load('windows nt 6.4', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 6.3') && false !== strpos($useragent, 'ARM')) {
            $platform = $platformLoader->load('windows nt 6.3; arm', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 6.3')) {
            $platform = $platformLoader->load('windows nt 6.3', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 6.2') && false !== strpos($useragent, 'ARM')) {
            $platform = $platformLoader->load('windows nt 6.2; arm', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 6.2')) {
            $platform = $platformLoader->load('windows nt 6.2', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 6.1')) {
            $platform = $platformLoader->load('windows nt 6.1', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 6')) {
            $platform = $platformLoader->load('windows nt 6.0', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 5.3')) {
            $platform = $platformLoader->load('windows nt 5.3', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 5.2')) {
            $platform = $platformLoader->load('windows nt 5.2', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 5.1')) {
            $platform = $platformLoader->load('windows nt 5.1', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 5.01')) {
            $platform = $platformLoader->load('windows nt 5.01', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 5.0')) {
            $platform = $platformLoader->load('windows nt 5.0', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 4.10')) {
            $platform = $platformLoader->load('windows nt 4.10', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 4.1')) {
            $platform = $platformLoader->load('windows nt 4.1', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 4.0')) {
            $platform = $platformLoader->load('windows nt 4.0', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 3.5')) {
            $platform = $platformLoader->load('windows nt 3.5', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows NT 3.1')) {
            $platform = $platformLoader->load('windows nt 3.1', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Windows[ \-]NT')) {
            $platform = $platformLoader->load('windows nt', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'cygwin')) {
            $platform = $platformLoader->load('cygwin', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'CPU OS')) {
            $platform = $platformLoader->load('ios', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'CPU iPhone OS')) {
            $platform = $platformLoader->load('ios', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'CPU like Mac OS X')) {
            $platform = $platformLoader->load('ios', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'iOS')) {
            $platform = $platformLoader->load('ios', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Mac OS X')) {
            $platform = $platformLoader->load('mac os x', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'hpwOS')) {
            $platform = $platformLoader->load('webos', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Debian APT-HTTP')) {
            $platform = $platformLoader->load('debian', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (preg_match('/linux arm/i', $useragent)) {
            $platform = $platformLoader->load('linux smartphone os (maemo)', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'fedora')) {
            $platform = $platformLoader->load('fedora linux', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'suse')) {
            $platform = $platformLoader->load('suse linux', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'centos')) {
            $platform = $platformLoader->load('cent os linux', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'mandriva')) {
            $platform = $platformLoader->load('mandriva linux', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'gentoo')) {
            $platform = $platformLoader->load('gentoo linux', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'slackware')) {
            $platform = $platformLoader->load('slackware linux', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'CrOS')) {
            $platform = $platformLoader->load('chromeos', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'debian')) {
            $platform = $platformLoader->load('debian', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'android; linux arm')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (preg_match('/(maemo|like android|linux\/x2\/r1|linux arm)/i', $useragent)) {
            $platform = $platformLoader->load('linux smartphone os (maemo)', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'moblin')) {
            $platform = $platformLoader->load('moblin', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== stripos($useragent, 'infegyatlas') || false !== stripos($useragent, 'jobboerse')) {
            $platform = $platformLoader->load('unknown', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (preg_match('/Puffin\/[\d\.]+(A|I|W|M)(T|P)?/', $useragent)) {
            $platform = $platformLoader->load('unknown', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'Linux')) {
            $platform = $platformLoader->load('linux', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (false !== strpos($useragent, 'SymbOS')) {
            $platform = $platformLoader->load('symbian', $useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } elseif (preg_match('/CFNetwork/', $useragent)) {
            $platform = (new Platform\DarwinFactory($cache, $platformLoader))->detect($useragent);

            $platformCodename      = $platform->getName();
            $platformMarketingname = $platform->getMarketingName();
            $platformVersion       = $platform->getVersion()->getVersion();
            $platformBits          = $platform->getBits();
            $platformMaker         = $platform->getManufacturer();
            $platformBrandname     = $platform->getBrand();
        } else {
            $result = $detector->getBrowser($useragent);

            $platform = $result->getOs();

            if ($platformCodename === $platform->getName()) {
                $platformMarketingname = $platform->getMarketingName();
                $platformVersion       = $platform->getVersion()->getVersion();
                $platformBits          = $platform->getBits();
                $platformMaker         = $platform->getManufacturer();
                $platformBrandname     = $platform->getBrand();
            }
        }

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
     *
     * @return array
     */
    private function rewriteDevice(\stdClass $test, CacheItemPoolInterface $cache, OsInterface $platform)
    {
        list(
            $deviceName,
            $deviceMaker,
            $deviceType,
            $devicePointing,
            $deviceCode,
            $deviceBrand, , ,
            $deviceOrientation,
            $device) = (new Device())->detect($cache, $test->ua, $platform);

        if ($deviceName === 'unknown') {
            $deviceName = $test->properties->Device_Name;
        }

        if ($deviceMaker === 'unknown') {
            $deviceMaker = $test->properties->Device_Maker;
        }

        if ($deviceType === 'unknown') {
            $deviceType = $test->properties->Device_Type;
        }

        if ($devicePointing === 'unknown') {
            $devicePointing = $test->properties->Device_Pointing_Method;
        }

        if ($deviceCode === 'unknown') {
            $deviceCode = $test->properties->Device_Code_Name;
        }

        if ($deviceBrand === 'unknown') {
            $deviceBrand = $test->properties->Device_Brand_Name;
        }

        if ('unknown' === $deviceOrientation) {
            $deviceOrientation = $test->properties->Device_Dual_Orientation;
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
