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
namespace BrowscapHelper\DataMapper;

use Psr\Cache\CacheItemPoolInterface;

/**
 * class with caching and update capabilities
 *
 * @category  ua-data-mapper
 *
 * @author    Thomas Mueller <mimmi20@live.de>
 * @copyright 2015-2017 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
class InputMapper
{
    /**
     * mapps the browser
     *
     * @param string $browserInput
     *
     * @throws \UnexpectedValueException
     *
     * @return string|null
     */
    public function mapBrowserName($browserInput)
    {
        return (new BrowserNameMapper())->mapBrowserName($browserInput);
    }

    /**
     * maps the browser version
     *
     * @param string $browserVersion
     * @param string $browserName
     *
     * @return \BrowserDetector\Version\Version
     */
    public function mapBrowserVersion($browserVersion, $browserName = null)
    {
        return (new BrowserVersionMapper())->mapBrowserVersion($browserVersion, $browserName);
    }

    /**
     * maps the browser type
     *
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param string                            $browserType
     *
     * @return \UaBrowserType\TypeInterface
     */
    public function mapBrowserType(CacheItemPoolInterface $cache, $browserType)
    {
        return (new BrowserTypeMapper())->mapBrowserType($cache, $browserType);
    }

    /**
     * maps the browser maker
     *
     * @param string $browserMaker
     * @param string $browserName
     *
     * @return string|null
     */
    public function mapBrowserMaker($browserMaker, $browserName = null)
    {
        return (new BrowserMakerMapper())->mapBrowserMaker($browserMaker, $browserName);
    }

    /**
     * maps the name of the operating system
     *
     * @param string $osName
     *
     * @return string|null
     */
    public function mapOsName($osName)
    {
        return (new PlatformNameMapper())->mapOsName($osName);
    }

    /**
     * maps the maker of the operating system
     *
     * @param string $osMaker
     * @param string $osName
     *
     * @return string|null
     */
    public function mapOsMaker($osMaker, $osName = null)
    {
        return (new PlatformMakerMapper())->mapOsMaker($osMaker, $osName);
    }

    /**
     * maps the version of the operating system
     *
     * @param string $osVersion
     * @param string $osName
     *
     * @return \BrowserDetector\Version\Version
     */
    public function mapOsVersion($osVersion, $osName = null)
    {
        return (new PlatformVersionMapper())->mapOsVersion($osVersion, $osName);
    }

    /**
     * maps the name of a device
     *
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param string                            $deviceType
     *
     * @return \UaDeviceType\TypeInterface
     */
    public function mapDeviceType(CacheItemPoolInterface $cache, $deviceType)
    {
        return (new DeviceTypeMapper())->mapDeviceType($cache, $deviceType);
    }

    /**
     * maps the name of a device
     *
     * @param string $deviceName
     *
     * @return string|null
     */
    public function mapDeviceName($deviceName)
    {
        return (new DeviceNameMapper())->mapDeviceName($deviceName);
    }

    /**
     * maps the maker of a device
     *
     * @param string $deviceMaker
     * @param string $deviceName
     *
     * @return string|null
     */
    public function mapDeviceMaker($deviceMaker, $deviceName = null)
    {
        return (new DeviceMakerMapper())->mapDeviceMaker($deviceMaker, $deviceName);
    }

    /**
     * maps the marketing name of a device
     *
     * @param string      $marketingName
     * @param string|null $deviceName
     *
     * @return string|null
     */
    public function mapDeviceMarketingName($marketingName, $deviceName = null)
    {
        $mapper = new DeviceMarketingnameMapper();
        $mname  = $mapper->mapDeviceName($deviceName);

        if (null === $mname) {
            $mname = $mapper->mapDeviceMarketingName($marketingName);
        }

        return $mname;
    }

    /**
     * maps the brand name of a device
     *
     * @param string      $brandName
     * @param string|null $deviceName
     *
     * @return string|null
     */
    public function mapDeviceBrandName($brandName, $deviceName = null)
    {
        $mapper    = new DeviceBrandnameMapper();
        $brandname = $mapper->mapDeviceName($deviceName);

        if (null === $brandname) {
            $brandname = $mapper->mapDeviceBrandName($brandName);
        }

        return $brandname;
    }

    /**
     * maps the value for the frame/iframe support
     *
     * @param string|bool $support
     *
     * @return string
     */
    public function mapFrameSupport($support)
    {
        return (new FrameSupportMapper())->mapFrameSupport($support);
    }

    /**
     * maps the version of the operating system
     *
     * @param string $engineVersion
     *
     * @return \BrowserDetector\Version\Version
     */
    public function mapEngineVersion($engineVersion)
    {
        return (new EngineVersionMapper())->mapEngineVersion($engineVersion);
    }

    /**
     * maps the name of the operating system
     *
     * @param string $engineName
     *
     * @return string|null
     */
    public function mapEngineName($engineName)
    {
        return (new EngineNameMapper())->mapEngineName($engineName);
    }
}
