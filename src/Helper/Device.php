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
namespace BrowscapHelper\Helper;

use BrowserDetector\Factory\DeviceFactory;
use BrowserDetector\Loader\DeviceLoader;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class Device
 *
 * @category   Browscap Helper
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 */
class Device
{
    /**
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param string                            $useragent
     *
     * @return \UaResult\Device\Device
     */
    public function detect(CacheItemPoolInterface $cache, $useragent)
    {
        $deviceLoader = new DeviceLoader($cache);
        $device       = null;

        try {
            /* @var \UaResult\Device\Device $device */
            list($device) = (new DeviceFactory($deviceLoader))->detect($useragent);
        } catch (\Exception $e) {
            $device = null;
        }

        if (null === $device
            || in_array($device->getDeviceName(), [null, 'unknown'])
            || false !== mb_stripos($device->getDeviceName(), 'general')
        ) {
            $device = new \UaResult\Device\Device(null, null);
        }

        return $device;
    }
}
