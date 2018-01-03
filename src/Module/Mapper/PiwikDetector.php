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
use BrowserDetector\Loader\NotFoundException;
use Psr\Cache\CacheItemPoolInterface;
use UaResult\Browser\Browser;
use UaResult\Company\CompanyLoader;
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
class PiwikDetector implements MapperInterface
{
    /**
     * @var \BrowscapHelper\DataMapper\InputMapper
     */
    private $mapper;

    /**
     * @param \BrowscapHelper\DataMapper\InputMapper $mapper
     * @param \Psr\Cache\CacheItemPoolInterface      $cache
     */
    public function __construct(InputMapper $mapper, CacheItemPoolInterface $cache)
    {
        $this->mapper = $mapper;
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
        $browserVersion      = null;
        $browserManufacturer = null;

        if (!empty($parserResult->bot)) {
            $browserName = $this->mapper->mapBrowserName($parserResult->bot->name);

            if (!empty($parserResult->bot->producer->name)) {
                $browserMakerKey = $this->mapper->mapBrowserMaker($parserResult->bot->producer->name, $browserName);

                if (null !== $browserMakerKey) {
                    try {
                        $browserManufacturer = CompanyLoader::getInstance()->load($browserMakerKey);
                    } catch (NotFoundException $e) {
                        //$this->logger->info($e);
                    }
                }
            }

            $browserType = $this->mapper->mapBrowserType('robot');
        } else {
            $browserName    = $this->mapper->mapBrowserName($parserResult->client->name);
            $browserVersion = $this->mapper->mapBrowserVersion(
                $parserResult->client->version,
                $browserName
            );

            if (!empty($parserResult->client->type)) {
                $browserType = $this->mapper->mapBrowserType($parserResult->client->type);
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

        $deviceName    = $this->mapper->mapDeviceName($parserResult->device->model);
        $marketingName = null;

        if (null !== $deviceName) {
            $marketingName = $this->mapper->mapDeviceMarketingName($deviceName);
        }

        $deviceBrand    = null;
        $deviceBrandKey = $this->mapper->mapDeviceBrandName($parserResult->device->brand, $deviceName);

        if (null !== $deviceBrandKey) {
            try {
                $deviceBrand = CompanyLoader::getInstance()->load($deviceBrandKey);
            } catch (NotFoundException $e) {
                //$this->logger->info($e);
            }
        }

        $device = new Device(
            $deviceName,
            $marketingName,
            null,
            $deviceBrand,
            $this->mapper->mapDeviceType($parserResult->device->type)
        );

        $os = new Os(null, null);

        if (!empty($parserResult->os->name)) {
            $osName    = $this->mapper->mapOsName($parserResult->os->name);
            $osVersion = $this->mapper->mapOsVersion($parserResult->os->version, $parserResult->os->name);

            if (!in_array($osName, ['PlayStation'])) {
                $os = new Os($osName, null, null, $osVersion);
            }
        }

        if (!empty($parserResult->client->engine)) {
            $engineName = $this->mapper->mapEngineName($parserResult->client->engine);

            $engine = new Engine($engineName);
        } else {
            $engine = new Engine(null);
        }

        $requestFactory = new GenericRequestFactory();

        return new Result($requestFactory->createRequestFromString(trim($agent))->getHeaders(), $device, $os, $browser, $engine);
    }
}
