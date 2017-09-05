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
namespace BrowscapHelper\Module\Mapper;

use BrowscapHelper\DataMapper\InputMapper;
use BrowserDetector\Helper\GenericRequestFactory;
use Psr\Cache\CacheItemPoolInterface;
use UaResult\Browser\Browser;
use UaResult\Device\Device;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;
use UaResult\Result\ResultInterface;

/**
 * BrowscapHelper.ini parsing class with caching and update capabilities
 *
 * @category  BrowscapHelper
 *
 * @author    Thomas Mueller <mimmi20@live.de>
 * @copyright 2015 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
class WhichBrowser implements MapperInterface
{
    /**
     * @var \BrowscapHelper\DataMapper\InputMapper
     */
    private $mapper;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cache;

    /**
     * @param \BrowscapHelper\DataMapper\InputMapper $mapper
     * @param \Psr\Cache\CacheItemPoolInterface      $cache
     */
    public function __construct(InputMapper $mapper, CacheItemPoolInterface $cache)
    {
        $this->mapper = $mapper;
        $this->cache  = $cache;
    }

    /**
     * Gets the information about the browser by User Agent
     *
     * @param \stdClass $parserResult
     * @param string    $agent
     *
     * @return \UaResult\Result\ResultInterface the object containing the browsers details
     */
    public function map($parserResult, string $agent): ResultInterface
    {
        $browserName = $this->mapper->mapBrowserName($parserResult->browser->name);

        if (empty($parserResult->browser->version->value)) {
            $browserVersion = null;
        } else {
            $browserVersion = $this->mapper->mapBrowserVersion($parserResult->browser->version->value, $browserName);
        }

        if (!empty($parserResult->browser->type)) {
            $browserType = $this->mapper->mapBrowserType($parserResult->browser->type);
        } else {
            $browserType = null;
        }

        $browser = new Browser(
            $browserName,
            null,
            $browserVersion,
            $browserType
        );

        $device = new Device(
            $parserResult->device->model,
            $this->mapper->mapDeviceMarketingName($parserResult->device->model),
            null,
            null,
            $this->mapper->mapDeviceType($parserResult->device->type)
        );

        $platform = $this->mapper->mapOsName($parserResult->os->name);

        if (empty($parserResult->os->version->value)) {
            $platformVersion = null;
        } else {
            $platformVersion = $this->mapper->mapOsVersion($parserResult->os->version->value, $platform);
        }

        $os = new Os($platform, null, null, $platformVersion);

        if (empty($parserResult->engine->version->value)) {
            $engineVersion = null;
        } else {
            $engineVersion = $this->mapper->mapEngineVersion($parserResult->engine->version->value);
        }

        $engine = new Engine(
            $this->mapper->mapEngineName($parserResult->engine->name),
            null,
            $engineVersion
        );

        $requestFactory = new GenericRequestFactory();

        return new Result($requestFactory->createRequestFromString(trim($agent))->getHeaders(), $device, $os, $browser, $engine);
    }
}
