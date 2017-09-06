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
use BrowscapHelper\DataMapper\DeviceTypeMapper;
use BrowscapHelper\DataMapper\EngineNameMapper;
use BrowscapHelper\DataMapper\EngineVersionMapper;
use BrowscapHelper\DataMapper\PlatformNameMapper;
use BrowscapHelper\DataMapper\PlatformVersionMapper;
use BrowserDetector\Helper\GenericRequestFactory;
use BrowserDetector\Loader\NotFoundException;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;
use UaResult\Browser\Browser;
use UaResult\Device\Device;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;

/**
 * Class DirectorySource
 *
 * @author  Thomas Mueller <mimmi20@live.de>
 */
class WhichBrowserSource implements SourceInterface
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
            $agent = trim($row->{'User-Agent'});

            if (empty($agent)) {
                continue;
            }

            yield $agent;
            ++$counter;
        }
    }

    /**
     * @return \UaResult\Result\Result[]
     */
    public function getTests(): iterable
    {
        foreach ($this->loadFromPath() as $row) {
            $row   = json_decode($row, false);
            $agent = trim($row->{'User-Agent'});

            if (empty($agent)) {
                continue;
            }

            $request = (new GenericRequestFactory())->createRequestFromString($agent);

            if (isset($row->browser->name)) {
                $browserName = (new BrowserNameMapper())->mapBrowserName($row->browser->name);
            } else {
                $browserName = null;
            }

            if (empty($row->browser->version->value)) {
                $browserVersion = null;
            } else {
                $browserVersion = (new BrowserVersionMapper())->mapBrowserVersion($row->browser->version->value, $browserName);
            }

            if (!empty($row->browser->type)) {
                try {
                    $browserType = (new BrowserTypeMapper())->mapBrowserType($row->browser->type);
                } catch (NotFoundException $e) {
                    $this->logger->critical($e);
                    $browserType = null;
                }
            } else {
                $browserType = null;
            }

            $browser = new Browser(
                $browserName,
                null,
                $browserVersion,
                $browserType
            );

            if (isset($row->device->type)) {
                try {
                    $deviceType = (new DeviceTypeMapper())->mapDeviceType($row->device->type);
                } catch (NotFoundException $e) {
                    $this->logger->critical($e);
                    $deviceType = null;
                }
            } else {
                $deviceType = null;
            }

            if (isset($row->device->model)) {
                $modelname = $row->device->model;
            } else {
                $modelname = null;
            }

            $device = new Device(
                $modelname,
                (new DeviceMarketingnameMapper())->mapDeviceMarketingName($modelname),
                null,
                null,
                $deviceType
            );

            if (isset($row->os->name)) {
                $platform = (new PlatformNameMapper())->mapOsName($row->os->name);

                if (empty($row->os->version->value)) {
                    $platformVersion = null;
                } else {
                    $platformVersion = (new PlatformVersionMapper())->mapOsVersion($row->os->version->value, $platform);
                }
            } else {
                $platform        = null;
                $platformVersion = null;
            }

            $os = new Os($platform, null, null, $platformVersion);

            if (isset($row->engine->name)) {
                $engineName = (new EngineNameMapper())->mapEngineName($row->engine->name);

                if (empty($row->engine->version->value)) {
                    $engineVersion = null;
                } else {
                    $engineVersion = (new EngineVersionMapper())->mapEngineVersion($row->engine->version->value);
                }
            } else {
                $engineName    = null;
                $engineVersion = null;
            }

            $engine = new Engine(
                $engineName,
                null,
                $engineVersion
            );

            yield $agent => new Result($request->getHeaders(), $device, $os, $browser, $engine);
        }
    }

    /**
     * @return array[]
     */
    private function loadFromPath(): iterable
    {
        $path = 'vendor/whichbrowser/parser/tests/data';

        if (!file_exists($path)) {
            return;
        }

        $this->logger->info('    reading path ' . $path);

        $allTests = [];
        $finder   = new Finder();
        $finder->files();
        $finder->name('*.yaml');
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

            if ('yaml' !== $file->getExtension()) {
                $this->logger->emergency('wrong file extension [' . $file->getExtension() . '] found with finder');
                continue;
            }

            $filepath = $file->getPathname();

            $this->logger->info('    reading file ' . str_pad($filepath, 100, ' ', STR_PAD_RIGHT));
            $data = Yaml::parse(file_get_contents($filepath));

            if (!is_iterable($data)) {
                continue;
            }

            foreach ($data as $row) {
                $agent = $this->getAgentFromRow($row);

                if (empty($agent)) {
                    continue;
                }

                if (array_key_exists($agent, $allTests)) {
                    continue;
                }

                unset($row['headers']);
                $row['User-Agent'] = $agent;

                yield json_encode($row, JSON_FORCE_OBJECT);
                $allTests[$agent] = 1;
            }
        }
    }

    /**
     * @param array $row
     *
     * @return string
     */
    private function getAgentFromRow(array $row): string
    {
        if (isset($row['headers'])) {
            if (isset($row['headers']['User-Agent'])) {
                return $row['headers']['User-Agent'];
            }

            if (class_exists('\http\Header')) {
                // pecl_http versions 2.x/3.x
                $headers = \http\Header::parse($row['headers']);
            } elseif (function_exists('\http_parse_headers')) {
                // pecl_http version 1.x
                $headers = \http_parse_headers($row['headers']);
            } else {
                return '';
            }
        }

        if (isset($headers['User-Agent'])) {
            return $headers['User-Agent'];
        }

        return '';
    }
}
