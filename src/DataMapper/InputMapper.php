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

use BrowserDetector\Version\VersionInterface;
use UaBrowserType\TypeInterface as BrowserTypeInterface;
use UaDeviceType\TypeInterface as DeviceTypeInterface;

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
    public function mapBrowserName(string $browserInput): ?string
    {
        return (new BrowserNameMapper())->mapBrowserName($browserInput);
    }

    /**
     * maps the browser version
     *
     * @param string|null $browserVersion
     * @param string|null $browserName
     *
     * @return \BrowserDetector\Version\VersionInterface
     */
    public function mapBrowserVersion(?string $browserVersion = null, ?string $browserName = null): VersionInterface
    {
        return (new BrowserVersionMapper())->mapBrowserVersion($browserVersion, $browserName);
    }

    /**
     * maps the browser type
     *
     * @param string $browserType
     *
     * @return \UaBrowserType\TypeInterface
     */
    public function mapBrowserType(string $browserType): BrowserTypeInterface
    {
        return (new BrowserTypeMapper())->mapBrowserType($browserType);
    }

    /**
     * maps the browser maker
     *
     * @param string      $browserMaker
     * @param string|null $browserName
     *
     * @return string|null
     */
    public function mapBrowserMaker(string $browserMaker, ?string $browserName = null): ?string
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
    public function mapOsName(string $osName): ?string
    {
        return (new PlatformNameMapper())->mapOsName($osName);
    }

    /**
     * maps the maker of the operating system
     *
     * @param string      $osMaker
     * @param string|null $osName
     *
     * @return string|null
     */
    public function mapOsMaker(string $osMaker, ?string $osName = null): ?string
    {
        return (new PlatformMakerMapper())->mapOsMaker($osMaker, $osName);
    }

    /**
     * maps the version of the operating system
     *
     * @param string      $osVersion
     * @param string|null $osName
     *
     * @return \BrowserDetector\Version\VersionInterface
     */
    public function mapOsVersion(string $osVersion, ?string $osName = null): VersionInterface
    {
        return (new PlatformVersionMapper())->mapOsVersion($osVersion, $osName);
    }

    /**
     * maps the name of a device
     *
     * @param string $deviceType
     *
     * @return \UaDeviceType\TypeInterface
     */
    public function mapDeviceType(string $deviceType): DeviceTypeInterface
    {
        return (new DeviceTypeMapper())->mapDeviceType($deviceType);
    }

    /**
     * maps the name of a device
     *
     * @param string $deviceName
     *
     * @return string|null
     */
    public function mapDeviceName(string $deviceName): ?string
    {
        return (new DeviceNameMapper())->mapDeviceName($deviceName);
    }

    /**
     * maps the maker of a device
     *
     * @param string      $deviceMaker
     * @param string|null $deviceName
     *
     * @return string|null
     */
    public function mapDeviceMaker(string $deviceMaker, ?string $deviceName = null): ?string
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
    public function mapDeviceMarketingName(string $marketingName, ?string $deviceName = null): ?string
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
    public function mapDeviceBrandName(string $brandName, ?string $deviceName = null): ?string
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
    public function mapFrameSupport($support): string
    {
        return (new FrameSupportMapper())->mapFrameSupport($support);
    }

    /**
     * maps the version of the operating system
     *
     * @param string $engineVersion
     *
     * @return \BrowserDetector\Version\VersionInterface
     */
    public function mapEngineVersion(string $engineVersion): VersionInterface
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
    public function mapEngineName(string $engineName): ?string
    {
        return (new EngineNameMapper())->mapEngineName($engineName);
    }
}
