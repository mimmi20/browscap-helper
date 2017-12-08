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
namespace BrowscapHelper\Source;

use BrowscapHelper\DataMapper\BrowserTypeMapper;
use BrowscapHelper\DataMapper\BrowserVersionMapper;
use BrowscapHelper\DataMapper\DeviceTypeMapper;
use BrowscapHelper\DataMapper\EngineVersionMapper;
use BrowserDetector\Helper\GenericRequestFactory;
use BrowserDetector\Loader\NotFoundException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use UaResult\Browser\Browser;
use UaResult\Company\CompanyLoader;
use UaResult\Device\Device;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;

/**
 * Class DirectorySource
 *
 * @author  Thomas Mueller <mimmi20@live.de>
 */
class BrowscapSource implements SourceInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cache;

    /**
     * @param \Psr\Log\LoggerInterface          $logger
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     */
    public function __construct(LoggerInterface $logger, CacheItemPoolInterface $cache)
    {
        $this->logger = $logger;
        $this->cache  = $cache;
    }

    /**
     * @param int $limit
     *
     * @return iterable|string[]
     */
    public function getUserAgents(int $limit = 0): iterable
    {
        $counter = 0;

        foreach ($this->loadFromPath() as $row) {
            if ($limit && $counter >= $limit) {
                return;
            }

            $agent = trim($row['ua']);

            if (empty($agent)) {
                continue;
            }

            yield $agent;
            ++$counter;
        }
    }

    /**
     * @return iterable|\UaResult\Result\Result[]
     */
    public function getTests(): iterable
    {
        foreach ($this->loadFromPath() as $row) {
            $agent = trim($row['ua']);

            if (empty($agent)) {
                continue;
            }

            $request = (new GenericRequestFactory())->createRequestFromString($agent);

            if (array_key_exists('Browser_Type', $row['properties'])) {
                try {
                    $browserType = (new BrowserTypeMapper())->mapBrowserType($row['properties']['Browser_Type']);
                } catch (NotFoundException $e) {
                    $this->logger->critical('browser type not found: ' . $row['properties']['Browser_Type']);
                    $browserType = null;
                }
            } else {
                $this->logger->warning('The browser type is missing for UA "' . $agent . '"');
                $browserType = null;
            }

            if (array_key_exists('Browser_Maker', $row['properties'])) {
                try {
                    $browserMaker = CompanyLoader::getInstance($this->cache)->load($row['properties']['Browser_Maker']);
                } catch (NotFoundException $e) {
                    $this->logger->critical('company not found: ' . $row['properties']['Browser_Maker']);
                    $browserMaker = null;
                }
            } else {
                $this->logger->warning('The browser maker is missing for UA "' . $agent . '"');
                $browserMaker = null;
            }

            if (array_key_exists('Browser_Bits', $row['properties'])) {
                $bits = (int) $row['properties']['Browser_Bits'];
            } else {
                $this->logger->warning('The browser bits are missing for UA "' . $agent . '"');
                $bits = null;
            }

            if (array_key_exists('Browser_Modus', $row['properties'])) {
                $modus = $row['properties']['Browser_Modus'];
            } else {
                $this->logger->warning('The browser modus is missing for UA "' . $agent . '"');
                $modus = null;
            }

            $browser = new Browser(
                $row['properties']['Browser'],
                $browserMaker,
                (new BrowserVersionMapper())->mapBrowserVersion($row['properties']['Version'], $row['properties']['Browser']),
                $browserType,
                $bits,
                $modus
            );

            if (array_key_exists('Device_Maker', $row['properties'])) {
                try {
                    $deviceMaker = CompanyLoader::getInstance($this->cache)->load($row['properties']['Device_Maker']);
                } catch (NotFoundException $e) {
                    $this->logger->critical('company not found: ' . $row['properties']['Device_Maker']);
                    $deviceMaker = null;
                }
            } else {
                $this->logger->warning('The device maker is missing for UA "' . $agent . '"');
                $deviceMaker = null;
            }

            if (array_key_exists('Device_Brand_Name', $row['properties'])) {
                try {
                    $deviceBrand = CompanyLoader::getInstance($this->cache)->load($row['properties']['Device_Brand_Name']);
                } catch (NotFoundException $e) {
                    $this->logger->critical('company not found: ' . $row['properties']['Device_Brand_Name']);
                    $deviceBrand = null;
                }
            } else {
                $this->logger->warning('The device brand name is missing for UA "' . $agent . '"');
                $deviceBrand = null;
            }

            if (array_key_exists('Device_Type', $row['properties'])) {
                try {
                    $deviceType = (new DeviceTypeMapper())->mapDeviceType($row['properties']['Device_Type']);
                } catch (NotFoundException $e) {
                    $this->logger->critical('device type not found: ' . $row['properties']['Device_Type']);
                    $deviceType = null;
                }
            } else {
                $this->logger->warning('The device type is missing for UA "' . $agent . '"');
                $deviceType = null;
            }

            if (array_key_exists('Device_Code_Name', $row['properties'])) {
                $codeName = $row['properties']['Device_Code_Name'];
            } else {
                $this->logger->warning('The device code name is missing for UA "' . $agent . '"');
                $codeName = null;
            }

            if (array_key_exists('Device_Name', $row['properties'])) {
                $deviceName = $row['properties']['Device_Name'];
            } else {
                $this->logger->warning('The device name is missing for UA "' . $agent . '"');
                $deviceName = null;
            }

            if (array_key_exists('Device_Pointing_Method', $row['properties'])) {
                $pointing = $row['properties']['Device_Pointing_Method'];
            } else {
                $this->logger->warning('The device pointing method is missing for UA "' . $agent . '"');
                $pointing = null;
            }

            $device = new Device(
                $codeName,
                $deviceName,
                $deviceMaker,
                $deviceBrand,
                $deviceType,
                $pointing
            );

            if (array_key_exists('Platform', $row['properties'])) {
                if (array_key_exists('Platform_Maker', $row['properties'])) {
                    try {
                        $platformMaker = CompanyLoader::getInstance($this->cache)->load($row['properties']['Platform_Maker']);
                    } catch (NotFoundException $e) {
                        $this->logger->critical('company not found: ' . $row['properties']['Platform_Maker']);
                        $platformMaker = null;
                    }
                } else {
                    $this->logger->warning('The platform maker is missing for UA "' . $agent . '"');
                    $platformMaker = null;
                }

                $platform = new Os(
                    $row['properties']['Platform'],
                    null,
                    $platformMaker
                );
            } else {
                $this->logger->warning('The platform name is missing for UA "' . $agent . '"');
                $platform = null;
            }

            if (array_key_exists('RenderingEngine_Name', $row['properties'])) {
                if (array_key_exists('Platform_Maker', $row['properties'])) {
                    try {
                        $engineMaker = CompanyLoader::getInstance($this->cache)->load($row['properties']['RenderingEngine_Maker']);
                    } catch (NotFoundException $e) {
                        $this->logger->critical('company not found: ' . $row['properties']['RenderingEngine_Maker']);
                        $engineMaker = null;
                    }
                } else {
                    $this->logger->warning('The engine maker is missing for UA "' . $agent . '"');
                    $engineMaker = null;
                }

                if (array_key_exists('RenderingEngine_Version', $row['properties'])) {
                    $engineVersion = (new EngineVersionMapper())->mapEngineVersion($row['properties']['RenderingEngine_Version']);
                } else {
                    $this->logger->warning('The engine version is missing for UA "' . $agent . '"');
                    $engineVersion = null;
                }

                $engine = new Engine(
                    $row['properties']['RenderingEngine_Name'],
                    $engineMaker,
                    $engineVersion
                );
            } else {
                $this->logger->warning('The engine name is missing for UA "' . $agent . '"');
                $engine = null;
            }

            yield $agent => new Result($request->getHeaders(), $device, $platform, $browser, $engine);
        }
    }

    /**
     * @return array[]|iterable
     */
    private function loadFromPath(): iterable
    {
        $path = 'vendor/browscap/browscap/tests/fixtures/issues';

        if (!file_exists($path)) {
            return;
        }

        $this->logger->info('    reading path ' . $path);

        $allTests = [];
        $finder   = new Finder();
        $finder->files();
        $finder->name('*.php');
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($path);

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            if (!$file->isFile()) {
                $this->logger->emergency('not-files selected with finder');

                continue;
            }

            if ('php' !== $file->getExtension()) {
                $this->logger->emergency('wrong file extension [' . $file->getExtension() . '] found with finder');

                continue;
            }

            $filepath = $file->getPathname();

            $this->logger->info('    reading file ' . str_pad($filepath, 100, ' ', STR_PAD_RIGHT));
            $data = include $filepath;

            if (!is_array($data)) {
                continue;
            }

            foreach ($data as $row) {
                if (!array_key_exists('ua', $row)) {
                    continue;
                }

                $agent = trim($row['ua']);

                if (array_key_exists($agent, $allTests)) {
                    continue;
                }

                yield $row;
                $allTests[$agent] = 1;
            }
        }
    }
}
