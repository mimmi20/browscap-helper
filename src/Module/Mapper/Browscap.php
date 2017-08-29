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
use BrowserDetector\Loader\NotFoundException;
use Psr\Cache\CacheItemPoolInterface;
use UaResult\Browser\Browser;
use UaResult\Company\CompanyLoader;
use UaResult\Device\Device;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;
use Wurfl\Request\GenericRequestFactory;

/**
 * Browscap.ini parsing class with caching and update capabilities
 *
 * @category  BrowscapHelper
 *
 * @author    Thomas Mueller <mimmi20@live.de>
 * @copyright 2015 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
class Browscap implements MapperInterface
{
    /**
     * @var \BrowscapHelper\DataMapper\InputMapper|null
     */
    private $mapper = null;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface|null
     */
    private $cache = null;

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
        if (!isset($parserResult->browser)) {
            $browser = new Browser(null, null, null);
        } else {
            $browserName    = $this->mapper->mapBrowserName($parserResult->browser);
            $browserVersion = $this->mapper->mapBrowserVersion(
                $parserResult->version,
                $browserName
            );

            if (!empty($parserResult->browser_type)) {
                $browserType = $parserResult->browser_type;
            } else {
                $browserType = null;
            }

            //if (!empty($parserResult->browser_modus) && 'unknown' !== $parserResult->browser_modus) {
            //    $browserModus = $parserResult->browser_modus;
            //} else {
            //    $browserModus = null;
            //}

            $browserManufacturer = null;
            $browserMakerKey     = $this->mapper->mapBrowserMaker($parserResult->browser_maker, $browserName);
            try {
                $browserManufacturer = (new CompanyLoader($this->cache))->load($browserMakerKey);
            } catch (NotFoundException $e) {
                //$this->logger->info($e);
            }

            $browser = new Browser(
                $browserName,
                $browserManufacturer,
                $browserVersion,
                $this->mapper->mapBrowserType($this->cache, $browserType),
                $parserResult->browser_bits
            );
        }

        if (!isset($parserResult->device_code_name)) {
            $device = new Device(null, null, null, null);
        } else {
            $deviceName = $this->mapper->mapDeviceName($parserResult->device_code_name);

            $deviceManufacturer = null;
            $deviceMakerKey     = $this->mapper->mapDeviceMaker($parserResult->device_maker, $deviceName);
            try {
                $deviceManufacturer = (new CompanyLoader($this->cache))->load($deviceMakerKey);
            } catch (NotFoundException $e) {
                //$this->logger->info($e);
            }

            $deviceBrand    = null;
            $deviceBrandKey = $this->mapper->mapDeviceBrandName($parserResult->device_brand_name, $deviceName);
            try {
                $deviceBrand = (new CompanyLoader($this->cache))->load($deviceBrandKey);
            } catch (NotFoundException $e) {
                //$this->logger->info($e);
            }

            $device = new Device(
                $deviceName,
                $this->mapper->mapDeviceMarketingName($parserResult->device_name, $deviceName),
                $deviceManufacturer,
                $deviceBrand,
                $this->mapper->mapDeviceType($this->cache, $parserResult->device_type),
                $parserResult->device_pointing_method
            );
        }

        if (!isset($parserResult->platform)) {
            $os = new Os(null, null);
        } else {
            $platform        = $this->mapper->mapOsName($parserResult->platform);
            $platformVersion = $this->mapper->mapOsVersion($parserResult->platform_version, $parserResult->platform);

            $osManufacturer = null;
            $osMakerKey     = $this->mapper->mapOsMaker($parserResult->platform_maker, $parserResult->platform);
            try {
                $osManufacturer = (new CompanyLoader($this->cache))->load($osMakerKey);
            } catch (NotFoundException $e) {
                //$this->logger->info($e);
            }

            $os = new Os(
                $platform,
                null,
                $osManufacturer,
                $platformVersion,
                $parserResult->platform_bits
            );
        }

        if (!isset($parserResult->renderingengine_name)) {
            $engine = new Engine(null);
        } else {
            $engineName = $this->mapper->mapEngineName($parserResult->renderingengine_name);

            $engineManufacturer = null;
            try {
                $engineManufacturer = (new CompanyLoader($this->cache))->load($parserResult->renderingengine_maker);
            } catch (NotFoundException $e) {
                //$this->logger->info($e);
            }

            $engine = new Engine(
                $engineName,
                $engineManufacturer,
                $this->mapper->mapEngineVersion($parserResult->renderingengine_version)
            );
        }

        $requestFactory = new GenericRequestFactory();

        return new Result($requestFactory->createRequestForUserAgent($agent), $device, $os, $browser, $engine);
    }
}
