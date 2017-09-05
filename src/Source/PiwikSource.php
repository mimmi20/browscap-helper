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

use BrowscapHelper\DataMapper\BrowserNameMapper;
use BrowscapHelper\DataMapper\BrowserTypeMapper;
use BrowscapHelper\DataMapper\BrowserVersionMapper;
use BrowscapHelper\DataMapper\DeviceMarketingnameMapper;
use BrowscapHelper\DataMapper\DeviceNameMapper;
use BrowscapHelper\DataMapper\DeviceTypeMapper;
use BrowscapHelper\DataMapper\EngineNameMapper;
use BrowscapHelper\DataMapper\PlatformNameMapper;
use BrowscapHelper\DataMapper\PlatformVersionMapper;
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
class PiwikSource implements SourceInterface
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
     * @return string[]
     */
    public function getUserAgents(int $limit = 0): iterable
    {
        $counter = 0;

        foreach ($this->loadFromPath() as $row) {
            if ($limit && $counter >= $limit) {
                return;
            }

            $row = json_decode($row, false);
            yield trim($row->user_agent);
            ++$counter;
        }
    }

    /**
     * @return \UaResult\Result\Result[]
     */
    public function getTests(): iterable
    {
        foreach ($this->loadFromPath() as $row) {
            $row     = json_decode($row, false);
            $request = (new GenericRequestFactory())->createRequestFromString($row->user_agent);

            $browserManufacturer = null;
            $browserVersion      = null;
            $browserName         = null;
            $browserType         = null;

            if (!empty($row->bot)) {
                $browserName = (new BrowserNameMapper())->mapBrowserName((string) $row->bot->name);

                if (!empty($row->bot->producer->name)) {
                    try {
                        $browserManufacturer = (new CompanyLoader($this->cache))->loadByName((string) $row->bot->producer->name);
                    } catch (NotFoundException $e) {
                        $this->logger->critical($e);
                        $browserManufacturer = null;
                    }
                }

                try {
                    $browserType = (new BrowserTypeMapper())->mapBrowserType('robot');
                } catch (NotFoundException $e) {
                    $this->logger->critical($e);
                    $browserType = null;
                }
            } elseif (isset($row->client->name)) {
                $browserName    = (new BrowserNameMapper())->mapBrowserName((string) $row->client->name);
                $browserVersion = (new BrowserVersionMapper())->mapBrowserVersion(
                    $row->client->version,
                    $browserName
                );

                if (!empty($row->client->type)) {
                    try {
                        $browserType = (new BrowserTypeMapper())->mapBrowserType((string) $row->client->type);
                    } catch (NotFoundException $e) {
                        $this->logger->critical($e);
                        $browserType = null;
                    }
                } else {
                    $browserType = null;
                }
            }

            $browser = new Browser(
                $browserName,
                $browserManufacturer,
                $browserVersion,
                $browserType
            );

            if (isset($row->device->model)) {
                $deviceName  = (new DeviceNameMapper())->mapDeviceName((string) $row->device->model);
                $deviceBrand = null;

                try {
                    $deviceBrand = (new CompanyLoader($this->cache))->loadByBrandName((string) $row->device->brand);
                } catch (NotFoundException $e) {
                    $this->logger->critical($e);
                    $deviceBrand = null;
                }

                try {
                    $deviceType = (new DeviceTypeMapper())->mapDeviceType((string) $row->device->type);
                } catch (NotFoundException $e) {
                    $this->logger->critical($e);
                    $deviceType = null;
                }
            } else {
                $deviceName  = 'unknown';
                $deviceBrand = null;
                $deviceType  = (new DeviceTypeMapper())->mapDeviceType('unknown');
            }

            $device = new Device(
                $deviceName,
                (new DeviceMarketingnameMapper())->mapDeviceMarketingName($deviceName),
                null,
                $deviceBrand,
                $deviceType
            );

            $os = new Os(null, null);

            if (!empty($row->os->name)) {
                $osName = (new PlatformNameMapper())->mapOsName((string) $row->os->name);

                if (!in_array($osName, ['PlayStation'])) {
                    $osVersion = (new PlatformVersionMapper())->mapOsVersion((string) $row->os->version, (string) $row->os->name);
                    $os        = new Os($osName, null, null, $osVersion);
                }
            }

            if (!empty($row->client->engine)) {
                $engineName = (new EngineNameMapper())->mapEngineName((string) $row->client->engine);

                $engine = new Engine($engineName);
            } else {
                $engine = new Engine(null);
            }

            yield trim($row->user_agent) => new Result($request->getHeaders(), $device, $os, $browser, $engine);
        }
    }

    /**
     * @return string[]
     */
    private function loadFromPath(): iterable
    {
        $path = 'vendor/piwik/device-detector/Tests/fixtures';

        if (!file_exists($path)) {
            return;
        }

        $this->logger->info('    reading path ' . $path);

        $allTests = [];
        $finder   = new Finder();
        $finder->files();
        $finder->name('*.yml');
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($path);

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            if (!$file->isFile()) {
                continue;
            }

            if ('yml' !== $file->getExtension()) {
                continue;
            }

            $filepath = $file->getPathname();

            $this->logger->info('    reading file ' . str_pad($filepath, 100, ' ', STR_PAD_RIGHT));
            $data = \Spyc::YAMLLoad($filepath);

            if (!is_iterable($data)) {
                continue;
            }

            foreach ($data as $row) {
                if (empty($row['user_agent'])) {
                    continue;
                }

                $agent = trim($row['user_agent']);

                if (array_key_exists($agent, $allTests)) {
                    continue;
                }

                yield json_encode($row, JSON_FORCE_OBJECT);
                $allTests[$agent] = 1;
            }
        }
    }
}
