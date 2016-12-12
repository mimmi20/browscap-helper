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

namespace BrowscapHelper\Helper;

use BrowserDetector\Loader\DeviceLoader;
use Psr\Cache\CacheItemPoolInterface;
use UaResult\Os\OsInterface;
use BrowserDetector\BrowserDetector;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class Device
{
    /**
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param string                            $useragent
     * @param OsInterface $platform
     * @param BrowserDetector                   $detector
     * @param string $deviceCode
     *
     * @return array
     */
    public function detect(
        CacheItemPoolInterface $cache,
        $useragent,
        OsInterface $platform,
        BrowserDetector $detector,
        $deviceCode,
        $deviceBrand       = null,
        $devicePointing    = null,
        $deviceType        = null,
        $deviceMaker       = null,
        $deviceName        = null,
        $deviceOrientation = null,
        $isTablet          = false,
        $mobileDevice      = false
    )
    {
        $deviceLoader = new DeviceLoader($cache);
        $device       = null;

        if (false !== strpos($useragent, 'Windows Phone')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($useragent, 'wds')) {
            $mobileDevice                   = true;
        } elseif (false !== stripos($useragent, 'wpdesktop')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($useragent, 'Tizen')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($useragent, 'Windows CE')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($useragent, 'Linux; Android')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($useragent, 'Linux; U; Android')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($useragent, 'U; Adr')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($useragent, 'Android') || false !== strpos($useragent, 'MTK')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($useragent, 'Symbian') || false !== strpos($useragent, 'Series 60')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($useragent, 'MIDP')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($useragent, 'Windows NT 10.0')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 6.4')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 6.3') && false !== strpos($useragent, 'ARM')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 6.3')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 6.2') && false !== strpos($useragent, 'ARM')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 6.2')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 6.1')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 6.0')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 5.3')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 5.2')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 5.1')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 5.01')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 5.0')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 4.1')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 4.0')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 3.5')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT 3.1')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Windows NT')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== stripos($useragent, 'cygwin')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('windows desktop', $useragent);
        } elseif (false !== strpos($useragent, 'CPU OS')) {
            $mobileDevice                   = true;

            if (false !== strpos($useragent, 'iPad')) {
                $device = $deviceLoader->load('ipad', $useragent);
            } elseif (false !== strpos($useragent, 'iPod')) {
                $device = $deviceLoader->load('ipod touch', $useragent);
            } elseif (false !== strpos($useragent, 'iPhone')) {
                $device = $deviceLoader->load('iphone', $useragent);
            }
        } elseif (false !== strpos($useragent, 'CPU iPhone OS')) {
            $mobileDevice                   = true;

            if (false !== strpos($useragent, 'iPad')) {
                $device = $deviceLoader->load('ipad', $useragent);
            } elseif (false !== strpos($useragent, 'iPod')) {
                $device = $deviceLoader->load('ipod touch', $useragent);
            } elseif (false !== strpos($useragent, 'iPhone')) {
                $device = $deviceLoader->load('iphone', $useragent);
            }
        } elseif (false !== strpos($useragent, 'CPU like Mac OS X')) {
            $mobileDevice                   = true;

            if (false !== strpos($useragent, 'iPad')) {
                $device = $deviceLoader->load('ipad', $useragent);
            } elseif (false !== strpos($useragent, 'iPod')) {
                $device = $deviceLoader->load('ipod touch', $useragent);
            } elseif (false !== strpos($useragent, 'iPhone')) {
                $device = $deviceLoader->load('iphone', $useragent);
            }
        } elseif (false !== strpos($useragent, 'iOS')) {
            $mobileDevice                   = true;

            if (false !== strpos($useragent, 'iPad')) {
                $device = $deviceLoader->load('ipad', $useragent);
            } elseif (false !== strpos($useragent, 'iPod')) {
                $device = $deviceLoader->load('ipod touch', $useragent);
            } elseif (false !== strpos($useragent, 'iPhone')) {
                $device = $deviceLoader->load('iphone', $useragent);
            }
        } elseif (false !== strpos($useragent, 'Mac OS X')) {
            $device = $deviceLoader->load('macintosh', $useragent);
        } elseif (false !== stripos($useragent, 'kubuntu')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('linux desktop', $useragent);
        } elseif (false !== stripos($useragent, 'ubuntu')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('linux desktop', $useragent);
        } elseif (false !== stripos($useragent, 'fedora')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('linux desktop', $useragent);
        } elseif (false !== stripos($useragent, 'suse')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('linux desktop', $useragent);
        } elseif (false !== stripos($useragent, 'mandriva')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('linux desktop', $useragent);
        } elseif (false !== stripos($useragent, 'gentoo')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('linux desktop', $useragent);
        } elseif (false !== stripos($useragent, 'slackware')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('linux desktop', $useragent);
        } elseif (false !== strpos($useragent, 'CrOS')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('linux desktop', $useragent);
        } elseif (false !== strpos($useragent, 'Linux')) {
            $mobileDevice                   = false;
            $device                         = $deviceLoader->load('linux desktop', $useragent);
        } elseif (false !== strpos($useragent, 'SymbOS')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($useragent, 'hpwOS')) {
            $mobileDevice                   = true;
        } elseif (false !== strpos($useragent, 'Silk') && false === strpos($useragent, 'Android')) {
            $mobileDevice              = true;
        }

        if (preg_match('/redmi 3s/i', $useragent)) {
            $device = $deviceLoader->load('redmi 3s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/redmi 3/i', $useragent)) {
            $device = $deviceLoader->load('redmi 3', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Redmi Note 2/i', $useragent)) {
            $device = $deviceLoader->load('redmi note 2', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Redmi_Note_3/i', $useragent)) {
            $device = $deviceLoader->load('redmi note 3', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/mi max/i', $useragent)) {
            $device = $deviceLoader->load('mi max', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/mi 4lte/i', $useragent)) {
            $device = $deviceLoader->load('mi 4 lte', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/mi 4w/i', $useragent)) {
            $device = $deviceLoader->load('mi 4w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/mi pad/i', $useragent)) {
            $device = $deviceLoader->load('mi pad', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/rm\-997/i', $useragent) && !preg_match('/(nokia|microsoft)/i', $useragent)) {
            $device = $deviceLoader->load('ross&moor rm-997', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/rm\-560/i', $useragent) && !preg_match('/(nokia|microsoft)/i', $useragent)) {
            $device = $deviceLoader->load('rm-560', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(rm\-1113|lumia 640 lte)/i', $useragent)) {
            $device = $deviceLoader->load('rm-1113', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(rm\-1075|lumia 640 dual sim)/i', $useragent)) {
            $device = $deviceLoader->load('rm-1075', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(rm\-1031|lumia 532)/i', $useragent)) {
            $device = $deviceLoader->load('rm-1031', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(rm\-\d{3,4})/i', $useragent, $matches)) {
            $device = $deviceLoader->load($matches[1], $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(lumia 1020|nokia; 909|arm; 909)/i', $useragent)) {
            $device = $deviceLoader->load('lumia 1020', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(lumia|nokia) 925/i', $useragent)) {
            $device = $deviceLoader->load('lumia 925', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(lumia 650|id336)/i', $useragent)) {
            $device = $deviceLoader->load('lumia 650', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(lumia \d{3,4} xl)/i', $useragent, $matches)) {
            $device = $deviceLoader->load($matches[1], $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(lumia \d{3,4})/i', $useragent, $matches)) {
            $device = $deviceLoader->load($matches[1], $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/8X by HTC/i', $useragent)) {
            $device = $deviceLoader->load('8x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/8S by HTC/i', $useragent)) {
            $device = $deviceLoader->load('8s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/radar( c110e|; orange)/i', $useragent)) {
            $device = $deviceLoader->load('radar c110e', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI; W1\-U00/i', $useragent)) {
            $device = $deviceLoader->load('w1-u00', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI; W2\-U00/i', $useragent)) {
            $device = $deviceLoader->load('w2-u00', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[_ ]m9plus/i', $useragent)) {
            $device = $deviceLoader->load('m9 plus', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[_ ]m9/i', $useragent)) {
            $device = $deviceLoader->load('m9', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[_ ]m8s/i', $useragent)) {
            $device = $deviceLoader->load('m8s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[_ ]m8/i', $useragent)) {
            $device = $deviceLoader->load('htc m8', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/pn07120/i', $useragent)) {
            $device = $deviceLoader->load('pn07120', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(one[ _]max|himauhl_htc_asia_tw)/i', $useragent)) {
            $device = $deviceLoader->load('one max', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]mini[ _]2/i', $useragent)) {
            $device = $deviceLoader->load('one mini 2', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[_ ]mini/i', $useragent)) {
            $device = $deviceLoader->load('one mini', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(one[ _]sv|onesv)/i', $useragent)) {
            $device = $deviceLoader->load('one sv', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(one[ _]s|ones)/i', $useragent) && !preg_match('/iOS/', $useragent)) {
            $device = $deviceLoader->load('pj401', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(one[ _]x\+|onexplus)/i', $useragent)) {
            $device = $deviceLoader->load('pm63100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]xl/i', $useragent)) {
            $device = $deviceLoader->load('htc pj83100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(one[ _]x|onex|PJ83100)/i', $useragent)) {
            $device = $deviceLoader->load('pj83100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(one[ _]v|onev)/i', $useragent)) {
            $device = $deviceLoader->load('one v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(PC36100|EVO 4G)/i', $useragent)) {
            $device = $deviceLoader->load('pc36100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Evo 3D GSM/i', $useragent)) {
            $device = $deviceLoader->load('evo 3d gsm', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HTC T328d/i', $useragent)) {
            $device = $deviceLoader->load('t328d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HTC T328w/i', $useragent)) {
            $device = $deviceLoader->load('t328w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HTC T329d/i', $useragent)) {
            $device = $deviceLoader->load('t329d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HTC 919d/i', $useragent)) {
            $device = $deviceLoader->load('919d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HTC D820us/i', $useragent)) {
            $device = $deviceLoader->load('d820us', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HTC D820mu/i', $useragent)) {
            $device = $deviceLoader->load('d820mu', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HTC 809d/i', $useragent)) {
            $device = $deviceLoader->load('809d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HTC[ ]?802t/i', $useragent)) {
            $device = $deviceLoader->load('802t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HTC 606w/i', $useragent)) {
            $device = $deviceLoader->load('desire 606w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HTC D516d/i', $useragent)) {
            $device = $deviceLoader->load('desire 516', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HTC Butterfly/i', $useragent)) {
            $device = $deviceLoader->load('butterfly', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]820s/i', $useragent)) {
            $device = $deviceLoader->load('desire 820s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]820/i', $useragent)) {
            $device = $deviceLoader->load('desire 820', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]816g/i', $useragent)) {
            $device = $deviceLoader->load('desire 816g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]816/i', $useragent)) {
            $device = $deviceLoader->load('desire 816', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]728g/i', $useragent)) {
            $device = $deviceLoader->load('desire 728g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]700/i', $useragent)) {
            $device = $deviceLoader->load('desire 700', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]610/i', $useragent)) {
            $device = $deviceLoader->load('desire 610', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]600c/i', $useragent)) {
            $device = $deviceLoader->load('desire 600c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]600/i', $useragent)) {
            $device = $deviceLoader->load('desire 600', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]626g/i', $useragent)) {
            $device = $deviceLoader->load('desire 626g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]626/i', $useragent)) {
            $device = $deviceLoader->load('desire 626', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]620g/i', $useragent)) {
            $device = $deviceLoader->load('desire 620g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]526g/i', $useragent)) {
            $device = $deviceLoader->load('desire 526g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]500/i', $useragent)) {
            $device = $deviceLoader->load('desire 500', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]510/i', $useragent)) {
            $device = $deviceLoader->load('desire 510', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]530/i', $useragent)) {
            $device = $deviceLoader->load('desire 530', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]400/i', $useragent)) {
            $device = $deviceLoader->load('desire 400', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]320/i', $useragent)) {
            $device = $deviceLoader->load('desire 320', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]310/i', $useragent)) {
            $device = $deviceLoader->load('desire 310', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]300/i', $useragent)) {
            $device = $deviceLoader->load('desire 300', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(0p4e2|desire[ _]601)/i', $useragent)) {
            $device = $deviceLoader->load('0p4e2', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire[ _]eye/i', $useragent)) {
            $device = $deviceLoader->load('desire eye', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desire_a8181/i', $useragent)) {
            $device = $deviceLoader->load('a8181', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/desirez\_a7272/i', $useragent)) {
            $device = $deviceLoader->load('a7272', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(desire[ _]z|desirez)/i', $useragent)) {
            $device = $deviceLoader->load('desire z', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/s510e/i', $useragent)) {
            $device = $deviceLoader->load('s510e', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(desire[ _]sv|desiresv)/i', $useragent)) {
            $device = $deviceLoader->load('desire sv', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(desire[ _]s|desires)/i', $useragent)) {
            $device = $deviceLoader->load('desire s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/a9191/i', $useragent)) {
            $device = $deviceLoader->load('a9191', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(desire hd|desirehd)/i', $useragent)) {
            $device = $deviceLoader->load('desire hd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/apa9292kt/i', $useragent)) {
            $device = $deviceLoader->load('9292', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/a9192/i', $useragent)) {
            $device = $deviceLoader->load('inspire 4g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(hd7|mondrian)/i', $useragent)) {
            $device = $deviceLoader->load('t9292', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(pc36100|evo 4g|kingdom)/i', $useragent)) {
            $device = $deviceLoader->load('pc36100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/htc_ruby/i', $useragent)) {
            $device = $deviceLoader->load('ruby', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nexus 9/i', $useragent)) {
            $device = $deviceLoader->load('nexus 9', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Nexus One/i', $useragent)) {
            $device = $deviceLoader->load('nexus one', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/htc_amaze/i', $useragent)) {
            $device = $deviceLoader->load('amaze 4g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/htc_butterfly_s_901s/i', $useragent)) {
            $device = $deviceLoader->load('s901s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HTC[ _]Sensation[ _]4G/i', $useragent)) {
            $device = $deviceLoader->load('sensation 4g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sensation[ _]z710e/i', $useragent)) {
            $device = $deviceLoader->load('z710e', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(sensation|pyramid)/i', $useragent)) {
            $device = $deviceLoader->load('z710', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/genm14/i', $useragent)) {
            $device = $deviceLoader->load('xl2', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Nokia_XL/i', $useragent)) {
            $device = $deviceLoader->load('xl', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaX3\-02/i', $useragent)) {
            $device = $deviceLoader->load('x3-02', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaX6\-00/i', $useragent)) {
            $device = $deviceLoader->load('x6-00', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaX2\-00/i', $useragent)) {
            $device = $deviceLoader->load('x2-00', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaX2\-01/i', $useragent)) {
            $device = $deviceLoader->load('x2-01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaX2\-02/i', $useragent)) {
            $device = $deviceLoader->load('x2-02', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaX2\-05/i', $useragent)) {
            $device = $deviceLoader->load('x2-05', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nokia300/i', $useragent)) {
            $device = $deviceLoader->load('300', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nokia200/i', $useragent)) {
            $device = $deviceLoader->load('200', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nokia203/i', $useragent)) {
            $device = $deviceLoader->load('203', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nokia210/i', $useragent)) {
            $device = $deviceLoader->load('210', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nokia206/i', $useragent)) {
            $device = $deviceLoader->load('206', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nokia205/i', $useragent)) {
            $device = $deviceLoader->load('205', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(nokia500|nokiaasha500)/i', $useragent)) {
            $device = $deviceLoader->load('500', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nokia501/i', $useragent)) {
            $device = $deviceLoader->load('501', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Nokia5800d/i', $useragent)) {
            $device = $deviceLoader->load('5800 xpressmusic', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Nokia5230/i', $useragent)) {
            $device = $deviceLoader->load('5230', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaC2\-01/i', $useragent)) {
            $device = $deviceLoader->load('c2-01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaC6\-01/i', $useragent)) {
            $device = $deviceLoader->load('c6-01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaC6\-00/i', $useragent)) {
            $device = $deviceLoader->load('c6-00', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaC5\-00/i', $useragent)) {
            $device = $deviceLoader->load('c5-00', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaN8\-00/i', $useragent)) {
            $device = $deviceLoader->load('n8-00', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaN82/i', $useragent)) {
            $device = $deviceLoader->load('n82', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaN95/i', $useragent)) {
            $device = $deviceLoader->load('n95', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nokia ?n70/i', $useragent)) {
            $device = $deviceLoader->load('n70', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NOKIA6700s/i', $useragent)) {
            $device = $deviceLoader->load('6700s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NOKIA6700c/i', $useragent)) {
            $device = $deviceLoader->load('6700 classic', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NOKIA6120c/i', $useragent)) {
            $device = $deviceLoader->load('6120c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaE71\-1/i', $useragent)) {
            $device = $deviceLoader->load('e71-1', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NokiaE71/i', $useragent)) {
            $device = $deviceLoader->load('e71', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nokia7230/i', $useragent)) {
            $device = $deviceLoader->load('7230', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/L50u/i', $useragent)) {
            $device = $deviceLoader->load('l50u', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SonyEricssonS312/i', $useragent)) {
            $device = $deviceLoader->load('s312', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(xperia z1|c6903)/i', $useragent)) {
            $device = $deviceLoader->load('c6903', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(Xperia Z|C6603)/i', $useragent)) {
            $device = $deviceLoader->load('c6603', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/C6602/i', $useragent)) {
            $device = $deviceLoader->load('c6602', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/C6606/i', $useragent)) {
            $device = $deviceLoader->load('c6606', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/C6833/i', $useragent)) {
            $device = $deviceLoader->load('c6833', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LT26ii/i', $useragent)) {
            $device = $deviceLoader->load('lt26ii', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LT26i/i', $useragent)) {
            $device = $deviceLoader->load('lt26i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LT22i/i', $useragent)) {
            $device = $deviceLoader->load('lt22i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LT18iv/i', $useragent)) {
            $device = $deviceLoader->load('lt18iv', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LT18i/i', $useragent)) {
            $device = $deviceLoader->load('lt18i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LT18a/i', $useragent)) {
            $device = $deviceLoader->load('lt18a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LT18/i', $useragent)) {
            $device = $deviceLoader->load('lt18', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LT15iv/i', $useragent)) {
            $device = $deviceLoader->load('lt15iv', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LT15i/i', $useragent)) {
            $device = $deviceLoader->load('lt15i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MT27i/i', $useragent)) {
            $device = $deviceLoader->load('mt27i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LT26w/i', $useragent)) {
            $device = $deviceLoader->load('lt26w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LT25i/i', $useragent)) {
            $device = $deviceLoader->load('lt25i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LT30p/i', $useragent)) {
            $device = $deviceLoader->load('lt30p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ST26i/i', $useragent)) {
            $device = $deviceLoader->load('st26i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ST27i/i', $useragent)) {
            $device = $deviceLoader->load('st27i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ST23i/i', $useragent)) {
            $device = $deviceLoader->load('st23i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ST18iv/i', $useragent)) {
            $device = $deviceLoader->load('st18iv', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ST18i/i', $useragent)) {
            $device = $deviceLoader->load('st18i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/D6603/i', $useragent)) {
            $device = $deviceLoader->load('d6603', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/D6633/i', $useragent)) {
            $device = $deviceLoader->load('d6633', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/D6503/i', $useragent)) {
            $device = $deviceLoader->load('d6503', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/D6000/i', $useragent)) {
            $device = $deviceLoader->load('d6000', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/D5803/i', $useragent)) {
            $device = $deviceLoader->load('d5803', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/D5103/i', $useragent)) {
            $device = $deviceLoader->load('d5103', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/D5303/i', $useragent)) {
            $device = $deviceLoader->load('d5303', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/D5503/i', $useragent)) {
            $device = $deviceLoader->load('d5503', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/D2005/i', $useragent)) {
            $device = $deviceLoader->load('d2005', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/D2203/i', $useragent)) {
            $device = $deviceLoader->load('d2203', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/D2403/i', $useragent)) {
            $device = $deviceLoader->load('d2403', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/D2303/i', $useragent)) {
            $device = $deviceLoader->load('d2303', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/C5303/i', $useragent)) {
            $device = $deviceLoader->load('c5303', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/C5502/i', $useragent)) {
            $device = $deviceLoader->load('c5502', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/C6902/i', $useragent)) {
            $device = $deviceLoader->load('c6902', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/C6503/i', $useragent)) {
            $device = $deviceLoader->load('c6503', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/C1905/i', $useragent)) {
            $device = $deviceLoader->load('c1905', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/C1505/i', $useragent)) {
            $device = $deviceLoader->load('c1505', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/C2105/i', $useragent)) {
            $device = $deviceLoader->load('c2105', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/C2005/i', $useragent)) {
            $device = $deviceLoader->load('c2005', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGP512/i', $useragent)) {
            $device = $deviceLoader->load('sgp512', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGP521/i', $useragent)) {
            $device = $deviceLoader->load('sgp521', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGP511/i', $useragent)) {
            $device = $deviceLoader->load('sgp511', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGP771/i', $useragent)) {
            $device = $deviceLoader->load('sgp771', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGP712/i', $useragent)) {
            $device = $deviceLoader->load('sgp712', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGP412/i', $useragent)) {
            $device = $deviceLoader->load('sgp412', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGP311/i', $useragent)) {
            $device = $deviceLoader->load('sgp311', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGP312/i', $useragent)) {
            $device = $deviceLoader->load('sgp312', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGP321/i', $useragent)) {
            $device = $deviceLoader->load('sgp321', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGP611/i', $useragent)) {
            $device = $deviceLoader->load('sgp611', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGP621/i', $useragent)) {
            $device = $deviceLoader->load('sgp621', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGPT12/i', $useragent)) {
            $device = $deviceLoader->load('sgpt12', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Sony Tablet S/i', $useragent)) {
            $device = $deviceLoader->load('tablet s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/E5823/i', $useragent)) {
            $device = $deviceLoader->load('e5823', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/E5603/i', $useragent)) {
            $device = $deviceLoader->load('e5603', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/E2303/i', $useragent)) {
            $device = $deviceLoader->load('e2303', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/E2003/i', $useragent)) {
            $device = $deviceLoader->load('e2003', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/E2105/i', $useragent)) {
            $device = $deviceLoader->load('e2105', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/F3111/i', $useragent)) {
            $device = $deviceLoader->load('f3111', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/E6653/i', $useragent)) {
            $device = $deviceLoader->load('e6653', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/E6553/i', $useragent)) {
            $device = $deviceLoader->load('e6553', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/E6653/i', $useragent)) {
            $device = $deviceLoader->load('e6653', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/E6853/i', $useragent)) {
            $device = $deviceLoader->load('e6853', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SO\-01E/i', $useragent)) {
            $device = $deviceLoader->load('so-01e', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SO\-02E/i', $useragent)) {
            $device = $deviceLoader->load('so-02e', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SO\-01D/i', $useragent)) {
            $device = $deviceLoader->load('so-01d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SO\-01C/i', $useragent)) {
            $device = $deviceLoader->load('so-01c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SO\-01B/i', $useragent)) {
            $device = $deviceLoader->load('so-01b', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SO\-05D/i', $useragent)) {
            $device = $deviceLoader->load('so-05d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SonyEricssonST25a/i', $useragent)) {
            $device = $deviceLoader->load('st25a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SonyEricssonST25iv/i', $useragent)) {
            $device = $deviceLoader->load('st25iv', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SonyEricssonST25i/i', $useragent)) {
            $device = $deviceLoader->load('st25i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SonyEricssonK770i/i', $useragent)) {
            $device = $deviceLoader->load('k770i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PlayStation 4/i', $useragent)) {
            $device = $deviceLoader->load('playstation 4', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PlayStation 3/i', $useragent)) {
            $device = $deviceLoader->load('playstation 3', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PlayStation Vita/i', $useragent)) {
            $device = $deviceLoader->load('playstation vita', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ONEPLUS A3000/i', $useragent)) {
            $device = $deviceLoader->load('a3000', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ONE E1003/i', $useragent)) {
            $device = $deviceLoader->load('e1003', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ONE A2005/i', $useragent)) {
            $device = $deviceLoader->load('a2005', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ONE A2003/i', $useragent)) {
            $device = $deviceLoader->load('a2003', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ONE A2001/i', $useragent)) {
            $device = $deviceLoader->load('a2001', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MZ\-MX5/i', $useragent)) {
            $device = $deviceLoader->load('mx5', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G9006V/i', $useragent)) {
            $device = $deviceLoader->load('sm-g9006v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G900F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g900f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G900a/i', $useragent)) {
            $device = $deviceLoader->load('sm-g900a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G900h/i', $useragent)) {
            $device = $deviceLoader->load('sm-g900h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G900i/i', $useragent)) {
            $device = $deviceLoader->load('sm-g900i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G900t/i', $useragent)) {
            $device = $deviceLoader->load('sm-g900t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G900v/i', $useragent)) {
            $device = $deviceLoader->load('sm-g900v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G900w8/i', $useragent)) {
            $device = $deviceLoader->load('sm-g900w8', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G900/i', $useragent)) {
            $device = $deviceLoader->load('sm-g900', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G903F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g903f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G901F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g901f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G928F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g928f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G928C/i', $useragent)) {
            $device = $deviceLoader->load('sm-g928c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G928P/i', $useragent)) {
            $device = $deviceLoader->load('sm-g928p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G928V/i', $useragent)) {
            $device = $deviceLoader->load('sm-g928v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G928G/i', $useragent)) {
            $device = $deviceLoader->load('sm-g928g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G928I/i', $useragent)) {
            $device = $deviceLoader->load('sm-g928i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G928W8/i', $useragent)) {
            $device = $deviceLoader->load('sm-g928w8', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G9287/i', $useragent)) {
            $device = $deviceLoader->load('sm-g9287', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G925F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g925f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G925I/i', $useragent)) {
            $device = $deviceLoader->load('sm-g925i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G925P/i', $useragent)) {
            $device = $deviceLoader->load('sm-g925p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G925T/i', $useragent)) {
            $device = $deviceLoader->load('sm-g925t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G925R4/i', $useragent)) {
            $device = $deviceLoader->load('sm-g925r4', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G925K/i', $useragent)) {
            $device = $deviceLoader->load('sm-g925k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G920V/i', $useragent)) {
            $device = $deviceLoader->load('sm-g920v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G920L/i', $useragent)) {
            $device = $deviceLoader->load('sm-g920l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G920P/i', $useragent)) {
            $device = $deviceLoader->load('sm-g920p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G920K/i', $useragent)) {
            $device = $deviceLoader->load('sm-g920k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G920FD/i', $useragent)) {
            $device = $deviceLoader->load('sm-g920fd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G920F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g920f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G920S/i', $useragent)) {
            $device = $deviceLoader->load('sm-g920s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G920I/i', $useragent)) {
            $device = $deviceLoader->load('sm-g920i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G920A/i', $useragent)) {
            $device = $deviceLoader->load('sm-g920a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G920T1/i', $useragent)) {
            $device = $deviceLoader->load('sm-g920t1', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G920T/i', $useragent)) {
            $device = $deviceLoader->load('sm-g920t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G9200/i', $useragent)) {
            $device = $deviceLoader->load('sm-g9200', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G9208/i', $useragent)) {
            $device = $deviceLoader->load('sm-g9208', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G9209/i', $useragent)) {
            $device = $deviceLoader->load('sm-g9209', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G920W8/i', $useragent)) {
            $device = $deviceLoader->load('sm-g920w8', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G920R/i', $useragent)) {
            $device = $deviceLoader->load('sm-g920r', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G930FD/i', $useragent)) {
            $device = $deviceLoader->load('sm-g930fd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G930F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g930f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G930A/i', $useragent)) {
            $device = $deviceLoader->load('sm-g930a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G930R/i', $useragent)) {
            $device = $deviceLoader->load('sm-g930r', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G930V/i', $useragent)) {
            $device = $deviceLoader->load('sm-g930v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G930P/i', $useragent)) {
            $device = $deviceLoader->load('sm-g930p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G930T/i', $useragent)) {
            $device = $deviceLoader->load('sm-g930t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G9308/i', $useragent)) {
            $device = $deviceLoader->load('sm-g9308', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G930/i', $useragent)) {
            $device = $deviceLoader->load('sm-g930', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G935FD/i', $useragent)) {
            $device = $deviceLoader->load('sm-g935fd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G935F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g935f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G935A/i', $useragent)) {
            $device = $deviceLoader->load('sm-g935a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G935P/i', $useragent)) {
            $device = $deviceLoader->load('sm-g935p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G935R/i', $useragent)) {
            $device = $deviceLoader->load('sm-g935r', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G935T/i', $useragent)) {
            $device = $deviceLoader->load('sm-g935t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G935V/i', $useragent)) {
            $device = $deviceLoader->load('sm-g935v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G935K/i', $useragent)) {
            $device = $deviceLoader->load('sm-g935k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G935L/i', $useragent)) {
            $device = $deviceLoader->load('sm-g935l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G935S/i', $useragent)) {
            $device = $deviceLoader->load('sm-g935s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G935W8/i', $useragent)) {
            $device = $deviceLoader->load('sm-g935w8', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G935X/i', $useragent)) {
            $device = $deviceLoader->load('sm-g935x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G9350/i', $useragent)) {
            $device = $deviceLoader->load('sm-g9350', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sm\-g850fq/i', $useragent)) {
            $device = $deviceLoader->load('sm-g850fq', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(sm\-g850f|galaxy alpha)/i', $useragent)) {
            $device = $deviceLoader->load('sm-g850f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sm\-g850a/i', $useragent)) {
            $device = $deviceLoader->load('sm-g850a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sm\-g850m/i', $useragent)) {
            $device = $deviceLoader->load('sm-g850m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sm\-g850t/i', $useragent)) {
            $device = $deviceLoader->load('sm-g850t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sm\-g850w/i', $useragent)) {
            $device = $deviceLoader->load('sm-g850w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sm\-g850y/i', $useragent)) {
            $device = $deviceLoader->load('sm-g850y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G870A/i', $useragent)) {
            $device = $deviceLoader->load('sm-g870a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G870F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g870f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sm\-g800hq/i', $useragent)) {
            $device = $deviceLoader->load('sm-g800hq', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sm\-g800h/i', $useragent)) {
            $device = $deviceLoader->load('sm-g800h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sm\-g800f/i', $useragent)) {
            $device = $deviceLoader->load('sm-g800f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sm\-g800m/i', $useragent)) {
            $device = $deviceLoader->load('sm-g800m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sm\-g800a/i', $useragent)) {
            $device = $deviceLoader->load('sm-g800a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sm\-g800r4/i', $useragent)) {
            $device = $deviceLoader->load('sm-g800r4', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sm\-g800y/i', $useragent)) {
            $device = $deviceLoader->load('sm-g800y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G890A/i', $useragent)) {
            $device = $deviceLoader->load('sm-g890a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G530H/i', $useragent)) {
            $device = $deviceLoader->load('sm-g530h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G530T/i', $useragent)) {
            $device = $deviceLoader->load('sm-g530t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G530FZ/i', $useragent)) {
            $device = $deviceLoader->load('sm-g530fz', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G530F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g530f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G530BT/i', $useragent)) {
            $device = $deviceLoader->load('sm-g530bt', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G530M/i', $useragent)) {
            $device = $deviceLoader->load('sm-g530m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G530Y/i', $useragent)) {
            $device = $deviceLoader->load('sm-g530y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G5306W/i', $useragent)) {
            $device = $deviceLoader->load('sm-g5306w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G5308W/i', $useragent)) {
            $device = $deviceLoader->load('sm-g5308w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G531F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g531f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G531H/i', $useragent)) {
            $device = $deviceLoader->load('sm-g531h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G388F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g388f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G389F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g389f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G386F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g386f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G3815/i', $useragent)) {
            $device = $deviceLoader->load('sm-g3815', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G360HU/i', $useragent)) {
            $device = $deviceLoader->load('sm-g360hu', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G360H/i', $useragent)) {
            $device = $deviceLoader->load('sm-g360h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G360T1/i', $useragent)) {
            $device = $deviceLoader->load('sm-g360t1', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G360T/i', $useragent)) {
            $device = $deviceLoader->load('sm-g360t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G360G/i', $useragent)) {
            $device = $deviceLoader->load('sm-g360g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G360F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g360f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G360BT/i', $useragent)) {
            $device = $deviceLoader->load('sm-g360bt', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G361F/i', $useragent)) {
            $device = $deviceLoader->load('sm-g361f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G361H/i', $useragent)) {
            $device = $deviceLoader->load('sm-g361h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G313HU/i', $useragent)) {
            $device = $deviceLoader->load('sm-g313hu', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G313HN/i', $useragent)) {
            $device = $deviceLoader->load('sm-g313hn', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G310HN/i', $useragent)) {
            $device = $deviceLoader->load('sm-g310hn', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G318H/i', $useragent)) {
            $device = $deviceLoader->load('sm-g318h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G355HQ/i', $useragent)) {
            $device = $deviceLoader->load('sm-g355hq', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G355HN/i', $useragent)) {
            $device = $deviceLoader->load('sm-g355hn', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G355H/i', $useragent)) {
            $device = $deviceLoader->load('sm-g355h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G355M/i', $useragent)) {
            $device = $deviceLoader->load('sm-g355m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G350e/i', $useragent)) {
            $device = $deviceLoader->load('sm-g350e', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G3500/i', $useragent)) {
            $device = $deviceLoader->load('sm-g3500', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G3502L/i', $useragent)) {
            $device = $deviceLoader->load('sm-g3502l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G3502T/i', $useragent)) {
            $device = $deviceLoader->load('sm-g3502t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G350/i', $useragent)) {
            $device = $deviceLoader->load('sm-g350', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G357fz/i', $useragent)) {
            $device = $deviceLoader->load('sm-g357fz', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G130H/i', $useragent)) {
            $device = $deviceLoader->load('sm-g130h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G110H/i', $useragent)) {
            $device = $deviceLoader->load('sm-g110h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G720N0/i', $useragent)) {
            $device = $deviceLoader->load('sm-g720n0', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G720D/i', $useragent)) {
            $device = $deviceLoader->load('sm-g720d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G7202/i', $useragent)) {
            $device = $deviceLoader->load('sm-g7202', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G710L/i', $useragent)) {
            $device = $deviceLoader->load('sm-g710l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G7102T/i', $useragent)) {
            $device = $deviceLoader->load('sm-g7102t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G7102/i', $useragent)) {
            $device = $deviceLoader->load('sm-g7102', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G7105L/i', $useragent)) {
            $device = $deviceLoader->load('sm-g7105l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G7105/i', $useragent)) {
            $device = $deviceLoader->load('sm-g7105', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G7106/i', $useragent)) {
            $device = $deviceLoader->load('sm-g7106', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G7108V/i', $useragent)) {
            $device = $deviceLoader->load('sm-g7108v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G7108/i', $useragent)) {
            $device = $deviceLoader->load('sm-g7108', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G7109/i', $useragent)) {
            $device = $deviceLoader->load('sm-g7109', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-G710/i', $useragent)) {
            $device = $deviceLoader->load('sm-g710', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T110/i', $useragent)) {
            $device = $deviceLoader->load('sm-t110', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T113/i', $useragent)) {
            $device = $deviceLoader->load('sm-t113', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T116/i', $useragent)) {
            $device = $deviceLoader->load('sm-t116', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T111/i', $useragent)) {
            $device = $deviceLoader->load('sm-t111', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T2105/i', $useragent)) {
            $device = $deviceLoader->load('sm-t2105', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T210l/i', $useragent)) {
            $device = $deviceLoader->load('sm-t210l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T210r/i', $useragent)) {
            $device = $deviceLoader->load('sm-t210r', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T210/i', $useragent)) {
            $device = $deviceLoader->load('sm-t210', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T211/i', $useragent)) {
            $device = $deviceLoader->load('sm-t211', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T230NU/i', $useragent)) {
            $device = $deviceLoader->load('sm-t230nu', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T230/i', $useragent)) {
            $device = $deviceLoader->load('sm-t230', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T231/i', $useragent)) {
            $device = $deviceLoader->load('sm-t231', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T235/i', $useragent)) {
            $device = $deviceLoader->load('sm-t235', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T280/i', $useragent)) {
            $device = $deviceLoader->load('sm-t280', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T525/i', $useragent)) {
            $device = $deviceLoader->load('sm-t525', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T520/i', $useragent)) {
            $device = $deviceLoader->load('sm-t520', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T580/i', $useragent)) {
            $device = $deviceLoader->load('sm-t580', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T585/i', $useragent)) {
            $device = $deviceLoader->load('sm-t585', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T550x/i', $useragent)) {
            $device = $deviceLoader->load('sm-t550x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T550/i', $useragent)) {
            $device = $deviceLoader->load('sm-t550', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T555/i', $useragent)) {
            $device = $deviceLoader->load('sm-t555', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T560/i', $useragent)) {
            $device = $deviceLoader->load('sm-t560', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T561/i', $useragent)) {
            $device = $deviceLoader->load('sm-t561', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T530nu/i', $useragent)) {
            $device = $deviceLoader->load('sm-t530nu', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T530/i', $useragent)) {
            $device = $deviceLoader->load('sm-t530', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T531/i', $useragent)) {
            $device = $deviceLoader->load('sm-t531', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T535/i', $useragent)) {
            $device = $deviceLoader->load('sm-t535', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T533/i', $useragent)) {
            $device = $deviceLoader->load('sm-t533', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T810x/i', $useragent)) {
            $device = $deviceLoader->load('sm-t810x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T810/i', $useragent)) {
            $device = $deviceLoader->load('sm-t810', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T815y/i', $useragent)) {
            $device = $deviceLoader->load('sm-t815y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T815/i', $useragent)) {
            $device = $deviceLoader->load('sm-t815', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T813/i', $useragent)) {
            $device = $deviceLoader->load('sm-t813', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T819/i', $useragent)) {
            $device = $deviceLoader->load('sm-t819', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T805/i', $useragent)) {
            $device = $deviceLoader->load('sm-t805', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T800/i', $useragent)) {
            $device = $deviceLoader->load('sm-t800', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T315/i', $useragent)) {
            $device = $deviceLoader->load('sm-t315', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T311/i', $useragent)) {
            $device = $deviceLoader->load('sm-t311', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T310/i', $useragent)) {
            $device = $deviceLoader->load('sm-t310', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T320/i', $useragent)) {
            $device = $deviceLoader->load('sm-t320', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T325/i', $useragent)) {
            $device = $deviceLoader->load('sm-t325', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T335/i', $useragent)) {
            $device = $deviceLoader->load('sm-t335', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T331/i', $useragent)) {
            $device = $deviceLoader->load('sm-t331', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T330/i', $useragent)) {
            $device = $deviceLoader->load('sm-t330', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T350/i', $useragent)) {
            $device = $deviceLoader->load('sm-t350', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T355Y/i', $useragent)) {
            $device = $deviceLoader->load('sm-t355y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T365/i', $useragent)) {
            $device = $deviceLoader->load('sm-t365', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T710/i', $useragent)) {
            $device = $deviceLoader->load('sm-t710', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T715/i', $useragent)) {
            $device = $deviceLoader->load('sm-t715', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T719/i', $useragent)) {
            $device = $deviceLoader->load('sm-t719', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T713/i', $useragent)) {
            $device = $deviceLoader->load('sm-t713', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T700/i', $useragent)) {
            $device = $deviceLoader->load('sm-t700', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T705m/i', $useragent)) {
            $device = $deviceLoader->load('sm-t705m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T705/i', $useragent)) {
            $device = $deviceLoader->load('sm-t705', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T900/i', $useragent)) {
            $device = $deviceLoader->load('sm-t900', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-T670/i', $useragent)) {
            $device = $deviceLoader->load('sm-t670', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-C101/i', $useragent)) {
            $device = $deviceLoader->load('sm-c101', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-C105/i', $useragent)) {
            $device = $deviceLoader->load('sm-c105', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-C115/i', $useragent)) {
            $device = $deviceLoader->load('sm-c115', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-C111/i', $useragent)) {
            $device = $deviceLoader->load('sm-c111', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9005/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9005', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9002/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9002', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9008V/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9008v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9009/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9009', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9007/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9007', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9006/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9006', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N900A/i', $useragent)) {
            $device = $deviceLoader->load('sm-n900a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N900V/i', $useragent)) {
            $device = $deviceLoader->load('sm-n900v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N900K/i', $useragent)) {
            $device = $deviceLoader->load('sm-n900k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N900S/i', $useragent)) {
            $device = $deviceLoader->load('sm-n900s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N900T/i', $useragent)) {
            $device = $deviceLoader->load('sm-n900t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N900P/i', $useragent)) {
            $device = $deviceLoader->load('sm-n900p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N900L/i', $useragent)) {
            $device = $deviceLoader->load('sm-n900l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N900W8/i', $useragent)) {
            $device = $deviceLoader->load('sm-n900w8', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9000Q/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9000q', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N900/i', $useragent)) {
            $device = $deviceLoader->load('sm-n900', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910FQ/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910fq', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910FD/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910fd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910F/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910A/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910C/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910G/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910H/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910K/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910L/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910M/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910R4/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910r4', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910P/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910S/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910T1/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910t1', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910T3/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910t3', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910T/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910U/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910u', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910V/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910W8/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910w8', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N910X/i', $useragent)) {
            $device = $deviceLoader->load('sm-n910x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9100H/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9100h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9100/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N915G/i', $useragent)) {
            $device = $deviceLoader->load('sm-n915g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N915T/i', $useragent)) {
            $device = $deviceLoader->load('sm-n915t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N915D/i', $useragent)) {
            $device = $deviceLoader->load('sm-n915d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N915K/i', $useragent)) {
            $device = $deviceLoader->load('sm-n915k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N915L/i', $useragent)) {
            $device = $deviceLoader->load('sm-n915l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N915S/i', $useragent)) {
            $device = $deviceLoader->load('sm-n915s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N915P/i', $useragent)) {
            $device = $deviceLoader->load('sm-n915p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N915FY/i', $useragent)) {
            $device = $deviceLoader->load('sm-n915fy', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N915F/i', $useragent)) {
            $device = $deviceLoader->load('sm-n915f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N915A/i', $useragent)) {
            $device = $deviceLoader->load('sm-n915a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N915V/i', $useragent)) {
            $device = $deviceLoader->load('sm-n915v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9150/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9150', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N916S/i', $useragent)) {
            $device = $deviceLoader->load('sm-n916s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930FD/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930fd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930F/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930U/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930u', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930W8/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930w8', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9300/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9300', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9308/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9308', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930K/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930L/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930S/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930AZ/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930az', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930A/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930P/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930V/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930T1/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930t1', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930T/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930R4/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930r4', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930R4/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930r4', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930R6/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930r6', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N930R7/i', $useragent)) {
            $device = $deviceLoader->load('sm-n930r7', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N920V/i', $useragent)) {
            $device = $deviceLoader->load('sm-n920v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N920T/i', $useragent)) {
            $device = $deviceLoader->load('sm-n920t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N920P/i', $useragent)) {
            $device = $deviceLoader->load('sm-n920p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N920A/i', $useragent)) {
            $device = $deviceLoader->load('sm-n920a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N920W8/i', $useragent)) {
            $device = $deviceLoader->load('sm-n920w8', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9208/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9208', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N9200/i', $useragent)) {
            $device = $deviceLoader->load('sm-n9200', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N920I/i', $useragent)) {
            $device = $deviceLoader->load('sm-n920i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N920C/i', $useragent)) {
            $device = $deviceLoader->load('sm-n920c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N920G/i', $useragent)) {
            $device = $deviceLoader->load('sm-n920g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N920K/i', $useragent)) {
            $device = $deviceLoader->load('sm-n920k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N920L/i', $useragent)) {
            $device = $deviceLoader->load('sm-n920l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N920S/i', $useragent)) {
            $device = $deviceLoader->load('sm-n920s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N920R/i', $useragent)) {
            $device = $deviceLoader->load('sm-n920r', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N7505L/i', $useragent)) {
            $device = $deviceLoader->load('sm-n7505l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N7505/i', $useragent)) {
            $device = $deviceLoader->load('sm-n7505', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N7502/i', $useragent)) {
            $device = $deviceLoader->load('sm-n7502', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N7500Q/i', $useragent)) {
            $device = $deviceLoader->load('sm-n7500q', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-N750/i', $useragent)) {
            $device = $deviceLoader->load('sm-n750', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-E500H/i', $useragent)) {
            $device = $deviceLoader->load('sm-e500h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-E700F/i', $useragent)) {
            $device = $deviceLoader->load('sm-e700f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-E700H/i', $useragent)) {
            $device = $deviceLoader->load('sm-e700h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-E700M/i', $useragent)) {
            $device = $deviceLoader->load('sm-e700m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-E7000/i', $useragent)) {
            $device = $deviceLoader->load('sm-e7000', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-E7009/i', $useragent)) {
            $device = $deviceLoader->load('sm-e7009', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A500FU/i', $useragent)) {
            $device = $deviceLoader->load('sm-a500fu', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A500F/i', $useragent)) {
            $device = $deviceLoader->load('sm-a500f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A500H/i', $useragent)) {
            $device = $deviceLoader->load('sm-a500h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A500L/i', $useragent)) {
            $device = $deviceLoader->load('sm-a500l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A500Y/i', $useragent)) {
            $device = $deviceLoader->load('sm-a500y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A5000/i', $useragent)) {
            $device = $deviceLoader->load('sm-a5000', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A300FU/i', $useragent)) {
            $device = $deviceLoader->load('sm-a300fu', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A300F/i', $useragent)) {
            $device = $deviceLoader->load('sm-a300f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A300H/i', $useragent)) {
            $device = $deviceLoader->load('sm-a300h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A310F/i', $useragent)) {
            $device = $deviceLoader->load('sm-a310f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A510FD/i', $useragent)) {
            $device = $deviceLoader->load('sm-a510fd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A510F/i', $useragent)) {
            $device = $deviceLoader->load('sm-a510f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A510M/i', $useragent)) {
            $device = $deviceLoader->load('sm-a510m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A510Y/i', $useragent)) {
            $device = $deviceLoader->load('sm-a510y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A5100/i', $useragent)) {
            $device = $deviceLoader->load('sm-a5100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A510S/i', $useragent)) {
            $device = $deviceLoader->load('sm-a510s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A700FD/i', $useragent)) {
            $device = $deviceLoader->load('sm-a700fd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A700F/i', $useragent)) {
            $device = $deviceLoader->load('sm-a700f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A700S/i', $useragent)) {
            $device = $deviceLoader->load('sm-a700s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A700K/i', $useragent)) {
            $device = $deviceLoader->load('sm-a700k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A700L/i', $useragent)) {
            $device = $deviceLoader->load('sm-a700l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A700H/i', $useragent)) {
            $device = $deviceLoader->load('sm-a700h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A700YD/i', $useragent)) {
            $device = $deviceLoader->load('sm-a700yd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A7000/i', $useragent)) {
            $device = $deviceLoader->load('sm-a7000', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A7009/i', $useragent)) {
            $device = $deviceLoader->load('sm-a7009', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A710FD/i', $useragent)) {
            $device = $deviceLoader->load('sm-a710fd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A710F/i', $useragent)) {
            $device = $deviceLoader->load('sm-a710f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A710M/i', $useragent)) {
            $device = $deviceLoader->load('sm-a710m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A7100/i', $useragent)) {
            $device = $deviceLoader->load('sm-a7100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A710Y/i', $useragent)) {
            $device = $deviceLoader->load('sm-a710y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A800F/i', $useragent)) {
            $device = $deviceLoader->load('sm-a800f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A800Y/i', $useragent)) {
            $device = $deviceLoader->load('sm-a800y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A800I/i', $useragent)) {
            $device = $deviceLoader->load('sm-a800i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A8000/i', $useragent)) {
            $device = $deviceLoader->load('sm-a8000', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-A9000/i', $useragent)) {
            $device = $deviceLoader->load('sm-a9000', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J510FN/i', $useragent)) {
            $device = $deviceLoader->load('sm-j510fn', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J510F/i', $useragent)) {
            $device = $deviceLoader->load('sm-j510f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J500FN/i', $useragent)) {
            $device = $deviceLoader->load('sm-j500fn', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J500F/i', $useragent)) {
            $device = $deviceLoader->load('sm-j500f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J500G/i', $useragent)) {
            $device = $deviceLoader->load('sm-j500g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J500Y/i', $useragent)) {
            $device = $deviceLoader->load('sm-j500y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J500M/i', $useragent)) {
            $device = $deviceLoader->load('sm-j500m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J500H/i', $useragent)) {
            $device = $deviceLoader->load('sm-j500h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J5007/i', $useragent)) {
            $device = $deviceLoader->load('sm-j5007', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J320g/i', $useragent)) {
            $device = $deviceLoader->load('sm-j320g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J320fn/i', $useragent)) {
            $device = $deviceLoader->load('sm-j320fn', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J320f/i', $useragent)) {
            $device = $deviceLoader->load('sm-j320f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J3109/i', $useragent)) {
            $device = $deviceLoader->load('sm-j3109', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J100H/i', $useragent)) {
            $device = $deviceLoader->load('sm-j100h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J100Y/i', $useragent)) {
            $device = $deviceLoader->load('sm-j100y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J100F/i', $useragent)) {
            $device = $deviceLoader->load('sm-j100f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J105H/i', $useragent)) {
            $device = $deviceLoader->load('sm-j105h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J100ML/i', $useragent)) {
            $device = $deviceLoader->load('sm-j100ml', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J110F/i', $useragent)) {
            $device = $deviceLoader->load('sm-j110f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J110G/i', $useragent)) {
            $device = $deviceLoader->load('sm-j110g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J110H/i', $useragent)) {
            $device = $deviceLoader->load('sm-j110h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J110L/i', $useragent)) {
            $device = $deviceLoader->load('sm-j110l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J110M/i', $useragent)) {
            $device = $deviceLoader->load('sm-j110m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J111F/i', $useragent)) {
            $device = $deviceLoader->load('sm-j111f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J120FN/i', $useragent)) {
            $device = $deviceLoader->load('sm-j120fn', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J120F/i', $useragent)) {
            $device = $deviceLoader->load('sm-j120f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J120G/i', $useragent)) {
            $device = $deviceLoader->load('sm-j120g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J120H/i', $useragent)) {
            $device = $deviceLoader->load('sm-j120h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J120M/i', $useragent)) {
            $device = $deviceLoader->load('sm-j120m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J200GU/i', $useragent)) {
            $device = $deviceLoader->load('sm-j200gu', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J200G/i', $useragent)) {
            $device = $deviceLoader->load('sm-j200g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J200F/i', $useragent)) {
            $device = $deviceLoader->load('sm-j200f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J200H/i', $useragent)) {
            $device = $deviceLoader->load('sm-j200h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J200BT/i', $useragent)) {
            $device = $deviceLoader->load('sm-j200bt', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J200Y/i', $useragent)) {
            $device = $deviceLoader->load('sm-j200y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J700F/i', $useragent)) {
            $device = $deviceLoader->load('sm-j700f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J700M/i', $useragent)) {
            $device = $deviceLoader->load('sm-j700m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J700H/i', $useragent)) {
            $device = $deviceLoader->load('sm-j700h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J710FN/i', $useragent)) {
            $device = $deviceLoader->load('sm-j710fn', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J710F/i', $useragent)) {
            $device = $deviceLoader->load('sm-j710f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J710H/i', $useragent)) {
            $device = $deviceLoader->load('sm-j710h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-J710M/i', $useragent)) {
            $device = $deviceLoader->load('sm-j710m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-P600/i', $useragent)) {
            $device = $deviceLoader->load('sm-p600', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-P601/i', $useragent)) {
            $device = $deviceLoader->load('sm-p601', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-P605/i', $useragent)) {
            $device = $deviceLoader->load('sm-p605', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-P550/i', $useragent)) {
            $device = $deviceLoader->load('sm-p550', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-P901/i', $useragent)) {
            $device = $deviceLoader->load('sm-p901', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-P900/i', $useragent)) {
            $device = $deviceLoader->load('sm-p900', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-P907A/i', $useragent)) {
            $device = $deviceLoader->load('sm-p907a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-P905M/i', $useragent)) {
            $device = $deviceLoader->load('sm-p905m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-P905V/i', $useragent)) {
            $device = $deviceLoader->load('sm-p905v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-P905/i', $useragent)) {
            $device = $deviceLoader->load('sm-p905', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-P355/i', $useragent)) {
            $device = $deviceLoader->load('sm-p355', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-P350/i', $useragent)) {
            $device = $deviceLoader->load('sm-p350', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-Z130H/i', $useragent)) {
            $device = $deviceLoader->load('sm-z130h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-S820L/i', $useragent)) {
            $device = $deviceLoader->load('sm-s820l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SM\-B550H/i', $useragent)) {
            $device = $deviceLoader->load('sm-b550h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Nexus Player/i', $useragent)) {
            $device = $deviceLoader->load('nexus player', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NEO\-X5/i', $useragent)) {
            $device = $deviceLoader->load('neo x5', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/vns\-l31/i', $useragent)) {
            $device = $deviceLoader->load('vns-l31', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/vns\-l21/i', $useragent)) {
            $device = $deviceLoader->load('vns-l21', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/tit\-u02/i', $useragent)) {
            $device = $deviceLoader->load('tit-u02', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/g750\-u10/i', $useragent)) {
            $device = $deviceLoader->load('g750-u10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/g750\-t00/i', $useragent)) {
            $device = $deviceLoader->load('g750-t00', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/g730\-u10/i', $useragent)) {
            $device = $deviceLoader->load('g730-u10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/g730\-u27/i', $useragent)) {
            $device = $deviceLoader->load('g730-u27', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/G510\-0100/i', $useragent)) {
            $device = $deviceLoader->load('g510-0100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/G525\-U00/i', $useragent)) {
            $device = $deviceLoader->load('g525-u00', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MediaPad 7 Youth/i', $useragent)) {
            $device = $deviceLoader->load('mediapad 7 youth', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MediaPad 7 Lite/i', $useragent)) {
            $device = $deviceLoader->load('mediapad 7 lite', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PE\-TL10/i', $useragent)) {
            $device = $deviceLoader->load('pe-tl10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/P6\-U06/i', $useragent)) {
            $device = $deviceLoader->load('p6-u06', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI G6\-L11/i', $useragent)) {
            $device = $deviceLoader->load('g6-l11', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI G6\-U10/i', $useragent)) {
            $device = $deviceLoader->load('g6-u10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/G7\-L01/', $useragent)) {
            $device = $deviceLoader->load('g7-l01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/G7\-L11/', $useragent)) {
            $device = $deviceLoader->load('g7-l11', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/G700\-U10/', $useragent)) {
            $device = $deviceLoader->load('g700-u10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/G740\-L00/', $useragent)) {
            $device = $deviceLoader->load('g740-l00', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI P7\-L10/i', $useragent)) {
            $device = $deviceLoader->load('p7-l10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI P7\-L09/i', $useragent)) {
            $device = $deviceLoader->load('p7-l09', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(p7 mini|p7mini)/i', $useragent)) {
            $device = $deviceLoader->load('p7 mini', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI P2\-6011/i', $useragent)) {
            $device = $deviceLoader->load('p2-6011', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI SCL\-L01/i', $useragent)) {
            $device = $deviceLoader->load('scl-l01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI SCL\-L21/i', $useragent)) {
            $device = $deviceLoader->load('scl-l21', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI SCL\-U31/i', $useragent)) {
            $device = $deviceLoader->load('scl-u31', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI NXT\-L29/i', $useragent)) {
            $device = $deviceLoader->load('nxt-l29', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI NXT\-AL10/i', $useragent)) {
            $device = $deviceLoader->load('nxt-al10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/GEM\-701L/', $useragent)) {
            $device = $deviceLoader->load('gem-701l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/GEM\-702L/', $useragent)) {
            $device = $deviceLoader->load('gem-702l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/GEM\-703L/', $useragent)) {
            $device = $deviceLoader->load('gem-703l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/G620S\-L01/', $useragent)) {
            $device = $deviceLoader->load('g620s-l01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI G610\-U20/i', $useragent)) {
            $device = $deviceLoader->load('g610-u20', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/G630\-U20/i', $useragent)) {
            $device = $deviceLoader->load('g630-u20', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/G630\-U251/i', $useragent)) {
            $device = $deviceLoader->load('g630-u251', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/EVA\-L09/i', $useragent)) {
            $device = $deviceLoader->load('eva-l09', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/EVA\-L19/i', $useragent)) {
            $device = $deviceLoader->load('eva-l19', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/FRD\-L09/i', $useragent)) {
            $device = $deviceLoader->load('frd-l09', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/mediapad 10 link\+/i', $useragent)) {
            $device = $deviceLoader->load('mediapad 10+', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/mediapad 10 link/i', $useragent)) {
            $device = $deviceLoader->load('s7-301w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/mediapad 10 fhd/i', $useragent)) {
            $device = $deviceLoader->load('mediapad 10 fhd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MediaPad T1 8\.0/i', $useragent)) {
            $device = $deviceLoader->load('s8-701u', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MediaPad X1 7\.0/i', $useragent)) {
            $device = $deviceLoader->load('mediapad x1 7.0', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MediaPad M1 8\.0/i', $useragent)) {
            $device = $deviceLoader->load('mediapad m1 8.0', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/u8651t/i', $useragent)) {
            $device = $deviceLoader->load('u8651t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/u8651s/i', $useragent)) {
            $device = $deviceLoader->load('u8651s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/u8651/i', $useragent)) {
            $device = $deviceLoader->load('u8651', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI U8666 Build\/HuaweiU8666E/i', $useragent)) {
            $device = $deviceLoader->load('u8666', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/u8666e/i', $useragent)) {
            $device = $deviceLoader->load('u8666e', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/u8666/i', $useragent)) {
            $device = $deviceLoader->load('u8666', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/U8950d/i', $useragent)) {
            $device = $deviceLoader->load('u8950d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/U8950n\-1/i', $useragent)) {
            $device = $deviceLoader->load('u8950n-1', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/U8950n/i', $useragent)) {
            $device = $deviceLoader->load('u8950n', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/U8950\-1/i', $useragent)) {
            $device = $deviceLoader->load('u8950-1', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/U8950/i', $useragent)) {
            $device = $deviceLoader->load('u8950', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/U9200/i', $useragent)) {
            $device = $deviceLoader->load('u9200', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/U8860/i', $useragent)) {
            $device = $deviceLoader->load('u8860', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Huawei Y511/i', $useragent)) {
            $device = $deviceLoader->load('y511', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y600\-U00/i', $useragent)) {
            $device = $deviceLoader->load('y600-u00', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y600\-U20/i', $useragent)) {
            $device = $deviceLoader->load('y600-u20', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y635\-L21/i', $useragent)) {
            $device = $deviceLoader->load('y635-l21', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y625\-U51/i', $useragent)) {
            $device = $deviceLoader->load('y625-u51', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y625\-U21/i', $useragent)) {
            $device = $deviceLoader->load('y625-u21', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Huawei Y530\-U00/i', $useragent)) {
            $device = $deviceLoader->load('y530-u00', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y320\-U30/i', $useragent)) {
            $device = $deviceLoader->load('y320-u30', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y320\-U10/i', $useragent)) {
            $device = $deviceLoader->load('y320-u10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y330\-U11/i', $useragent)) {
            $device = $deviceLoader->load('y330-u11', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y336\-U02/i', $useragent)) {
            $device = $deviceLoader->load('y336-u02', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y360\-U61/i', $useragent)) {
            $device = $deviceLoader->load('y360-u61', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y360\-U31/i', $useragent)) {
            $device = $deviceLoader->load('y360-u31', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y340\-U081/i', $useragent)) {
            $device = $deviceLoader->load('y340-u081', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y330\-U05/i', $useragent)) {
            $device = $deviceLoader->load('y330-u05', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y330\-U01/i', $useragent)) {
            $device = $deviceLoader->load('y330-u01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y220\-U10/i', $useragent)) {
            $device = $deviceLoader->load('y220-u10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI Y300/i', $useragent)) {
            $device = $deviceLoader->load('y300', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI ALE\-21/', $useragent)) {
            $device = $deviceLoader->load('ale 21', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ALE\-L21/', $useragent)) {
            $device = $deviceLoader->load('ale-l21', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ALE\-L02/', $useragent)) {
            $device = $deviceLoader->load('ale-l02', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/H30\-U10/i', $useragent)) {
            $device = $deviceLoader->load('h30-u10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/KIW\-L21/i', $useragent)) {
            $device = $deviceLoader->load('kiw-l21', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lyo\-L21/i', $useragent)) {
            $device = $deviceLoader->load('lyo-l21', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nmo\-L31/i', $useragent)) {
            $device = $deviceLoader->load('nmo-l31', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HUAWEI P8max/i', $useragent)) {
            $device = $deviceLoader->load('p8max', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TAG\-AL00/i', $useragent)) {
            $device = $deviceLoader->load('tag-al00', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TAG\-L21/i', $useragent)) {
            $device = $deviceLoader->load('tag-l21', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TAG\-L01/i', $useragent)) {
            $device = $deviceLoader->load('tag-l01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/GRA\-L09/i', $useragent)) {
            $device = $deviceLoader->load('gra-l09', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/VIE\-L09/i', $useragent)) {
            $device = $deviceLoader->load('vie-l09', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/VIE\-AL10/i', $useragent)) {
            $device = $deviceLoader->load('vie-al10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/GRACE/', $useragent)) {
            $device = $deviceLoader->load('grace', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/S8\-701w/i', $useragent)) {
            $device = $deviceLoader->load('s8-701w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MT7\-TL10/i', $useragent)) {
            $device = $deviceLoader->load('mt7-tl10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(MT7\-L09|JAZZ)/', $useragent)) {
            $device = $deviceLoader->load('mt7-l09', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MT1\-U06/i', $useragent)) {
            $device = $deviceLoader->load('mt1-u06', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/D2\-0082/', $useragent)) {
            $device = $deviceLoader->load('d2-0082', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HN3\-U01/i', $useragent)) {
            $device = $deviceLoader->load('hn3-u01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HOL\-U19/i', $useragent)) {
            $device = $deviceLoader->load('hol-u19', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(t1\-701u|t1 7\.0)/i', $useragent)) {
            $device = $deviceLoader->load('t1-701u', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/T1\-a21l/i', $useragent)) {
            $device = $deviceLoader->load('t1-a21l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/T1\-a21w/i', $useragent)) {
            $device = $deviceLoader->load('t1-a21w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/FDR\-a01l/i', $useragent)) {
            $device = $deviceLoader->load('fdr-a01l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/FDR\-a01w/i', $useragent)) {
            $device = $deviceLoader->load('fdr-a01w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/M2\-a01l/i', $useragent)) {
            $device = $deviceLoader->load('m2-a01l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/M2\-a01w/i', $useragent)) {
            $device = $deviceLoader->load('m2-a01w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/M2\-801w/i', $useragent)) {
            $device = $deviceLoader->load('m2-801w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/M2\-801l/i', $useragent)) {
            $device = $deviceLoader->load('m2-801l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/RIO\-L01/i', $useragent)) {
            $device = $deviceLoader->load('rio-l01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/CRR\-L09/i', $useragent)) {
            $device = $deviceLoader->load('crr-l09', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/CUN\-L03/i', $useragent)) {
            $device = $deviceLoader->load('cun-l03', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/CUN\-L21/i', $useragent)) {
            $device = $deviceLoader->load('cun-l21', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/CHC\-U01/i', $useragent)) {
            $device = $deviceLoader->load('chc-u01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ATH\-UL01/i', $useragent)) {
            $device = $deviceLoader->load('ath-ul01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y550\-L01/i', $useragent)) {
            $device = $deviceLoader->load('y550-l01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y560\-L01/i', $useragent)) {
            $device = $deviceLoader->load('y560-l01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y540\-U01/i', $useragent)) {
            $device = $deviceLoader->load('y540-u01', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Y210\-0100/i', $useragent)) {
            $device = $deviceLoader->load('y210-0100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/F5281/i', $useragent)) {
            $device = $deviceLoader->load('f5281', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Aquaris M10/i', $useragent)) {
            $device = $deviceLoader->load('aquaris m10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Aquaris M5/i', $useragent)) {
            $device = $deviceLoader->load('aquaris m5', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Aquaris[ _]M4\.5/i', $useragent)) {
            $device = $deviceLoader->load('aquaris m4.5', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Aquaris E5 HD/i', $useragent)) {
            $device = $deviceLoader->load('aquaris e5 hd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/BQS\-4005/i', $useragent)) {
            $device = $deviceLoader->load('bqs-4005', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/BQS\-4007/i', $useragent)) {
            $device = $deviceLoader->load('bqs-4007', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9195i/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9195i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9195/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9195', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9190/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9190', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9192/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9192', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9100g/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9100g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9100p/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9100p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9100/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9105p/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9105p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9105/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9105', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9103/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9103', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9152/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9152', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9300i/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9300i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9300/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9300', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9301i/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9301i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9301q/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9301q', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9301/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9301', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9305/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9305', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9060i/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9060i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9060l/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9060l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9060/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9060', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9070p/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9070p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9070/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9070', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9003l/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9003l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9003/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9003', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9001/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9001', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9000/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9000', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9082L/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9082l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9082/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9082', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9505g/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9505g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9505x/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9505x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9505/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9505', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9506/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9506', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9502/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9502', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9500/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9500', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9502/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9502', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9515/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9515', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9295/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9295', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9205/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9205', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9200/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9200', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i5500/i', $useragent)) {
            $device = $deviceLoader->load('gt-i5500', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i9515/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9515', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i5500/i', $useragent)) {
            $device = $deviceLoader->load('gt-i5500', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i5700/i', $useragent)) {
            $device = $deviceLoader->load('gt-i5700', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i8190n/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8190n', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i8190/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8190', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i8150/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8150', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i8160p/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8160p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i8160/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8160', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i8200n/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8200n', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i8200/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8200', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i8260/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8260', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i8262/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8262', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i8552/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8552', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i8530/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8530', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(gt\-i8910|i8910)/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8910', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i8730/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8730', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-i8750/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8750', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-e3309t/i', $useragent)) {
            $device = $deviceLoader->load('gt-e3309t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-e2202/i', $useragent)) {
            $device = $deviceLoader->load('gt-e2202', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-e2252/i', $useragent)) {
            $device = $deviceLoader->load('gt-e2252', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-b7722/i', $useragent)) {
            $device = $deviceLoader->load('gt-b7722', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7262/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7262', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7275r/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7275r', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7275/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7275', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7272/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7272', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7270/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7270', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7500/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7500', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7580/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7580', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7582/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7582', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7562l/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7562l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7562/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7562', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7560/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7560', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7392/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7392', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7390/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7390', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s7710/i', $useragent)) {
            $device = $deviceLoader->load('gt-s7710', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s3802/i', $useragent)) {
            $device = $deviceLoader->load('gt-s3802', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s3653/i', $useragent)) {
            $device = $deviceLoader->load('gt-s3653', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5620/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5620', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5660/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5660', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5301L/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5301l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5301/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5301', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5302/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5302', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5300b/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5300b', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5300/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5300', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5310m/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5310m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5310/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5310', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5360/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5360', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5363/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5363', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5369/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5369', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5380/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5380', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5830l/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5830l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5830i/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5830i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5830c/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5830c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5830/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5830', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s6810b/i', $useragent)) {
            $device = $deviceLoader->load('gt-s6810b', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s6810p/i', $useragent)) {
            $device = $deviceLoader->load('gt-s6810p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s6810/i', $useragent)) {
            $device = $deviceLoader->load('gt-s6810', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s6500t/i', $useragent)) {
            $device = $deviceLoader->load('gt-s6500t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s6500d/i', $useragent)) {
            $device = $deviceLoader->load('gt-s6500d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s6500/i', $useragent)) {
            $device = $deviceLoader->load('gt-s6500', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s6312/i', $useragent)) {
            $device = $deviceLoader->load('gt-s6312', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s6310n/i', $useragent)) {
            $device = $deviceLoader->load('gt-s6310n', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s6310/i', $useragent)) {
            $device = $deviceLoader->load('gt-s6310', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s6102b/i', $useragent)) {
            $device = $deviceLoader->load('gt-s6102b', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s6102/i', $useragent)) {
            $device = $deviceLoader->load('gt-s6102', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5839i/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5839i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5570/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5570', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5280/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5280', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5220/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5220', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-s5233s/i', $useragent)) {
            $device = $deviceLoader->load('gt-s5233s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-c6712/i', $useragent)) {
            $device = $deviceLoader->load('gt-c6712', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-c3262/i', $useragent)) {
            $device = $deviceLoader->load('gt-c3262', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-c3322/i', $useragent)) {
            $device = $deviceLoader->load('gt-c3322', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-c3780/i', $useragent)) {
            $device = $deviceLoader->load('gt-c3780', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p5110/i', $useragent)) {
            $device = $deviceLoader->load('gt-p5110', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p5100/i', $useragent)) {
            $device = $deviceLoader->load('gt-p5100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-P5210/i', $useragent)) {
            $device = $deviceLoader->load('gt-p5210', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-P5200/i', $useragent)) {
            $device = $deviceLoader->load('gt-p5200', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-P5220/i', $useragent)) {
            $device = $deviceLoader->load('gt-p5220', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p7510/i', $useragent)) {
            $device = $deviceLoader->load('gt-p7510', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p7511/i', $useragent)) {
            $device = $deviceLoader->load('gt-p7511', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p7500M/i', $useragent)) {
            $device = $deviceLoader->load('gt-p7500m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p7500/i', $useragent)) {
            $device = $deviceLoader->load('gt-p7500', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p7501/i', $useragent)) {
            $device = $deviceLoader->load('gt-p7501', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p7100/i', $useragent)) {
            $device = $deviceLoader->load('gt-p7100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p7310/i', $useragent)) {
            $device = $deviceLoader->load('gt-p7310', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p3100/i', $useragent)) {
            $device = $deviceLoader->load('gt-p3100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p3110/i', $useragent)) {
            $device = $deviceLoader->load('gt-p3110', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p3113/i', $useragent)) {
            $device = $deviceLoader->load('gt-p3113', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p1010/i', $useragent)) {
            $device = $deviceLoader->load('gt-p1010', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p1000m/i', $useragent)) {
            $device = $deviceLoader->load('gt-p1000m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p1000n/i', $useragent)) {
            $device = $deviceLoader->load('gt-p1000n', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p1000/i', $useragent)) {
            $device = $deviceLoader->load('gt-p1000', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p6201/i', $useragent)) {
            $device = $deviceLoader->load('gt-p6201', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p6211/i', $useragent)) {
            $device = $deviceLoader->load('gt-p6211', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-p6200/i', $useragent)) {
            $device = $deviceLoader->load('gt-p6200', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-n7100/i', $useragent)) {
            $device = $deviceLoader->load('gt-n7100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-n7105/i', $useragent)) {
            $device = $deviceLoader->load('gt-n7105', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-n7000/i', $useragent)) {
            $device = $deviceLoader->load('gt-n7000', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-n5110/i', $useragent)) {
            $device = $deviceLoader->load('gt-n5110', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-n5100/i', $useragent)) {
            $device = $deviceLoader->load('gt-n5100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-n5120/i', $useragent)) {
            $device = $deviceLoader->load('gt-n5120', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-n8010/i', $useragent)) {
            $device = $deviceLoader->load('gt-n8010', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-n8013/i', $useragent)) {
            $device = $deviceLoader->load('gt-n8013', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-n8020/i', $useragent)) {
            $device = $deviceLoader->load('gt-n8020', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-n8005/i', $useragent)) {
            $device = $deviceLoader->load('gt-n8005', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/N8000D/', $useragent)) {
            $device = $deviceLoader->load('gt-n8000d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-n8000/i', $useragent)) {
            $device = $deviceLoader->load('gt-n8000', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-e250i/i', $useragent)) {
            $device = $deviceLoader->load('sgh-e250i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-e250/i', $useragent)) {
            $device = $deviceLoader->load('sgh-e250', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-t528g/i', $useragent)) {
            $device = $deviceLoader->load('sgh-t528g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-t989d/i', $useragent)) {
            $device = $deviceLoader->load('sgh-t989d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-t989/i', $useragent)) {
            $device = $deviceLoader->load('sgh-t989', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-t999/i', $useragent)) {
            $device = $deviceLoader->load('sgh-t999', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-t959v/i', $useragent)) {
            $device = $deviceLoader->load('sgh-t959v', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-t959/i', $useragent)) {
            $device = $deviceLoader->load('sgh-t959', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-t839/i', $useragent)) {
            $device = $deviceLoader->load('sgh-t839', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-t859/i', $useragent)) {
            $device = $deviceLoader->load('sgh-t859', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-t889/i', $useragent)) {
            $device = $deviceLoader->load('sgh-t889', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-t899m/i', $useragent)) {
            $device = $deviceLoader->load('sgh-t899m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-i257/i', $useragent)) {
            $device = $deviceLoader->load('sgh-i257', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGH\-I717/i', $useragent)) {
            $device = $deviceLoader->load('sgh-i717', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGH\-I727R/i', $useragent)) {
            $device = $deviceLoader->load('sgh-i727r', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGH\-I727/i', $useragent)) {
            $device = $deviceLoader->load('sgh-i727', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGH\-I317/i', $useragent)) {
            $device = $deviceLoader->load('sgh-i317', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGH\-I337m/i', $useragent)) {
            $device = $deviceLoader->load('sgh-i337m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGH\-I337/i', $useragent)) {
            $device = $deviceLoader->load('sgh-i337', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGH\-I467/i', $useragent)) {
            $device = $deviceLoader->load('sgh-i467', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SGH\-I897/i', $useragent)) {
            $device = $deviceLoader->load('sgh-i897', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-m919/i', $useragent)) {
            $device = $deviceLoader->load('sgh-m919', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-f480i/i', $useragent)) {
            $device = $deviceLoader->load('sgh-f480i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sgh\-f480/i', $useragent)) {
            $device = $deviceLoader->load('sgh-f480', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sch\-r970/i', $useragent)) {
            $device = $deviceLoader->load('sch-r970', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sch\-r950/i', $useragent)) {
            $device = $deviceLoader->load('sch-r950', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sch\-r530u/i', $useragent)) {
            $device = $deviceLoader->load('sch-r530u', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sch\-r530c/i', $useragent)) {
            $device = $deviceLoader->load('sch-r530c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sch\-i815/i', $useragent)) {
            $device = $deviceLoader->load('sch-i815', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sch\-i545/i', $useragent)) {
            $device = $deviceLoader->load('sch-i545', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sch\-i535/i', $useragent)) {
            $device = $deviceLoader->load('sch-i535', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sch\-i605/i', $useragent)) {
            $device = $deviceLoader->load('sch-i605', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sch\-i435/i', $useragent)) {
            $device = $deviceLoader->load('sch-i435', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sch\-i400/i', $useragent)) {
            $device = $deviceLoader->load('sch-i400', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sch\-n719/i', $useragent)) {
            $device = $deviceLoader->load('sch-n719', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sc\-02f/i', $useragent)) {
            $device = $deviceLoader->load('sc-02f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sc\-02c/i', $useragent)) {
            $device = $deviceLoader->load('sc-02c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sc\-02b/i', $useragent)) {
            $device = $deviceLoader->load('sc-02b', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sc\-01f/i', $useragent)) {
            $device = $deviceLoader->load('sc-01f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sc\-06d/i', $useragent)) {
            $device = $deviceLoader->load('sc-06d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/shv\-e210l/i', $useragent)) {
            $device = $deviceLoader->load('shv-e210l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/shv\-e210s/i', $useragent)) {
            $device = $deviceLoader->load('shv-e210s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/shv\-e210k/i', $useragent)) {
            $device = $deviceLoader->load('shv-e210k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/shv\-e250l/i', $useragent)) {
            $device = $deviceLoader->load('shv-e250l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/shv\-e250k/i', $useragent)) {
            $device = $deviceLoader->load('shv-e250k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/shv\-e250s/i', $useragent)) {
            $device = $deviceLoader->load('shv-e250s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/shv\-e160s/i', $useragent)) {
            $device = $deviceLoader->load('shv-e160s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/shv\-e370k/i', $useragent)) {
            $device = $deviceLoader->load('shv-e370k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/shw\-m480w/i', $useragent)) {
            $device = $deviceLoader->load('shw-m480w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/shw\-m180s/i', $useragent)) {
            $device = $deviceLoader->load('shw-m180s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sph\-m840/i', $useragent)) {
            $device = $deviceLoader->load('sph-m840', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sph\-m930/i', $useragent)) {
            $device = $deviceLoader->load('sph-m930', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sph\-l900/i', $useragent)) {
            $device = $deviceLoader->load('sph-l900', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sph\-l720/i', $useragent)) {
            $device = $deviceLoader->load('sph-l720', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sph\-l710/i', $useragent)) {
            $device = $deviceLoader->load('sph-l710', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nexus 10/i', $useragent)) {
            $device = $deviceLoader->load('nexus 10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Nexus S 4G/i', $useragent)) {
            $device = $deviceLoader->load('nexus s 4g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Nexus S/i', $useragent)) {
            $device = $deviceLoader->load('nexus s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/samsung galaxy s4/i', $useragent)) {
            $device = $deviceLoader->load('gt-i9500', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/gt\-9000/i', $useragent)) {
            $device = $deviceLoader->load('gt-9000', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Slate 17/i', $useragent)) {
            $device = $deviceLoader->load('slate 17', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/H345/i', $useragent)) {
            $device = $deviceLoader->load('h345', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/H340n/i', $useragent)) {
            $device = $deviceLoader->load('h340n', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/H320/i', $useragent)) {
            $device = $deviceLoader->load('h320', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/H850/i', $useragent)) {
            $device = $deviceLoader->load('h850', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D802TR/i', $useragent)) {
            $device = $deviceLoader->load('d802tr', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D802/i', $useragent)) {
            $device = $deviceLoader->load('d802', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D855/i', $useragent)) {
            $device = $deviceLoader->load('d855', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D856/i', $useragent)) {
            $device = $deviceLoader->load('d856', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D320/i', $useragent)) {
            $device = $deviceLoader->load('d320', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D325/i', $useragent)) {
            $device = $deviceLoader->load('d325', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D373/i', $useragent)) {
            $device = $deviceLoader->load('d373', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D290/i', $useragent)) {
            $device = $deviceLoader->load('d290', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D955/i', $useragent)) {
            $device = $deviceLoader->load('d955', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D958/i', $useragent)) {
            $device = $deviceLoader->load('d958', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D686/i', $useragent)) {
            $device = $deviceLoader->load('d686', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D682tr/i', $useragent)) {
            $device = $deviceLoader->load('d682tr', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D682/i', $useragent)) {
            $device = $deviceLoader->load('d682', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D690/i', $useragent)) {
            $device = $deviceLoader->load('d690', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D620/i', $useragent)) {
            $device = $deviceLoader->load('d620', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D415/i', $useragent)) {
            $device = $deviceLoader->load('d415', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-D410/i', $useragent)) {
            $device = $deviceLoader->load('d410', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-E425/i', $useragent)) {
            $device = $deviceLoader->load('e425', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-E612/i', $useragent)) {
            $device = $deviceLoader->load('e612', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-E610/i', $useragent)) {
            $device = $deviceLoader->load('e610', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-E615/i', $useragent)) {
            $device = $deviceLoader->load('e615', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-E460/i', $useragent)) {
            $device = $deviceLoader->load('e460', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-E988/i', $useragent)) {
            $device = $deviceLoader->load('e988', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-E989/i', $useragent)) {
            $device = $deviceLoader->load('e989', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-F240K/i', $useragent)) {
            $device = $deviceLoader->load('f240k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-F220K/i', $useragent)) {
            $device = $deviceLoader->load('f220k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-F200K/i', $useragent)) {
            $device = $deviceLoader->load('f200k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-V935/i', $useragent)) {
            $device = $deviceLoader->load('v935', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-V490/i', $useragent)) {
            $device = $deviceLoader->load('v490', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-X150/i', $useragent)) {
            $device = $deviceLoader->load('x150', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-P765/i', $useragent)) {
            $device = $deviceLoader->load('p765', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-P970/i', $useragent)) {
            $device = $deviceLoader->load('p970', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-H525n/i', $useragent)) {
            $device = $deviceLoader->load('h525n', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nexus 5x/i', $useragent)) {
            $device = $deviceLoader->load('nexus 5x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nexus ?5/i', $useragent)) {
            $device = $deviceLoader->load('nexus 5', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nexus ?4/i', $useragent)) {
            $device = $deviceLoader->load('nexus 4', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_E10316/i', $useragent)) {
            $device = $deviceLoader->load('lifetab e10316', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_E10312/i', $useragent)) {
            $device = $deviceLoader->load('lifetab e10312', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_E10320/i', $useragent)) {
            $device = $deviceLoader->load('lifetab e10320', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_E10310/i', $useragent)) {
            $device = $deviceLoader->load('lifetab e10310', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_E7312/i', $useragent)) {
            $device = $deviceLoader->load('lifetab e7312', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_E7316/i', $useragent)) {
            $device = $deviceLoader->load('lifetab e7316', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_E7313/i', $useragent)) {
            $device = $deviceLoader->load('lifetab e7313', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_E733X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab e733x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_E723X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab e723x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_P733X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab p733x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_P1034X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab p1034x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_P891X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab p891x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_P831X\.2/i', $useragent)) {
            $device = $deviceLoader->load('lifetab p831x.2', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_P831X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab p831x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_P9516/i', $useragent)) {
            $device = $deviceLoader->load('lifetab p9516', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_P9514/i', $useragent)) {
            $device = $deviceLoader->load('lifetab p9514', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_S1034X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab s1034x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_S1033X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab s1033x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_S1036X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab s1036x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_S9714/i', $useragent)) {
            $device = $deviceLoader->load('lifetab s9714', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_S9512/i', $useragent)) {
            $device = $deviceLoader->load('lifetab s9512', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_S831X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab s831x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_S785X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab s785x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LIFETAB_S732X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab s732x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/P4501/i', $useragent)) {
            $device = $deviceLoader->load('md 98428', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/P4502/i', $useragent)) {
            $device = $deviceLoader->load('life p4502', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/P4013/i', $useragent)) {
            $device = $deviceLoader->load('life p4013', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MEDION E5001/i', $useragent)) {
            $device = $deviceLoader->load('life e5001', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(MEDION|LIFE) E3501/i', $useragent)) {
            $device = $deviceLoader->load('life e3501', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MEDION E4502/i', $useragent)) {
            $device = $deviceLoader->load('life e4502', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MEDION E4504/i', $useragent)) {
            $device = $deviceLoader->load('life e4504', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MEDION E4506/i', $useragent)) {
            $device = $deviceLoader->load('life e4506', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MEDION E4503/i', $useragent)) {
            $device = $deviceLoader->load('life e4503', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MEDION E4005/i', $useragent)) {
            $device = $deviceLoader->load('life e4005', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MEDION X5004/i', $useragent)) {
            $device = $deviceLoader->load('x5004', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MEDION X5020/i', $useragent)) {
            $device = $deviceLoader->load('life x5020', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MEDION X4701/i', $useragent)) {
            $device = $deviceLoader->load('x4701', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MEDION P5001/i', $useragent)) {
            $device = $deviceLoader->load('life p5001', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MEDION P5004/i', $useragent)) {
            $device = $deviceLoader->load('life p5004', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MEDION P5005/i', $useragent)) {
            $device = $deviceLoader->load('life p5005', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MEDION S5004/i', $useragent)) {
            $device = $deviceLoader->load('life s5004', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/YUANDA50/i', $useragent)) {
            $device = $deviceLoader->load('50', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IQ4415/', $useragent)) {
            $device = $deviceLoader->load('iq4415', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IQ4490/', $useragent)) {
            $device = $deviceLoader->load('iq4490', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IQ449/', $useragent)) {
            $device = $deviceLoader->load('iq449', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IQ448/', $useragent)) {
            $device = $deviceLoader->load('iq448', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IQ444/', $useragent)) {
            $device = $deviceLoader->load('iq444', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IQ442/', $useragent)) {
            $device = $deviceLoader->load('iq442', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IQ436i/', $useragent)) {
            $device = $deviceLoader->load('iq436i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IQ434/', $useragent)) {
            $device = $deviceLoader->load('iq434', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IQ452/', $useragent)) {
            $device = $deviceLoader->load('iq452', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IQ456/', $useragent)) {
            $device = $deviceLoader->load('iq456', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IQ4502/', $useragent)) {
            $device = $deviceLoader->load('iq4502', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IQ4504/', $useragent)) {
            $device = $deviceLoader->load('iq4504', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IQ450/', $useragent)) {
            $device = $deviceLoader->load('iq450', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/(CX919|gxt_dongle_3188)/i', $useragent)) {
            $device = $deviceLoader->load('cx919', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PAP5000TDUO/i', $useragent)) {
            $device = $deviceLoader->load('pap5000tduo', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PAP5000DUO/i', $useragent)) {
            $device = $deviceLoader->load('pap5000duo', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PAP5044DUO/i', $useragent)) {
            $device = $deviceLoader->load('pap5044duo', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PAP7600DUO/i', $useragent)) {
            $device = $deviceLoader->load('pap7600duo', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PAP4500DUO/i', $useragent)) {
            $device = $deviceLoader->load('pap4500duo', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PAP4044DUO/i', $useragent)) {
            $device = $deviceLoader->load('pap4044duo', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PAP3350DUO/i', $useragent)) {
            $device = $deviceLoader->load('pap3350duo', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PAP5503/i', $useragent)) {
            $device = $deviceLoader->load('pap5503', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PMT3037_3G/i', $useragent)) {
            $device = $deviceLoader->load('pmt3037_3g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PMP7074B3GRU/i', $useragent)) {
            $device = $deviceLoader->load('pmp7074b3gru', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PMP3007C/i', $useragent)) {
            $device = $deviceLoader->load('pmp3007c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PMP3970B/i', $useragent)) {
            $device = $deviceLoader->load('pmp3970b', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/sprd\-B51\+/i', $useragent)) {
            $device = $deviceLoader->load('b51+', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/BlackBerry 9790/i', $useragent)) {
            $device = $deviceLoader->load('blackberry 9790', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/BlackBerry 9720/i', $useragent)) {
            $device = $deviceLoader->load('blackberry 9720', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/BB10; Kbd/i', $useragent)) {
            $device = $deviceLoader->load('kbd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/BB10; Touch/i', $useragent)) {
            $device = $deviceLoader->load('z10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/XT1068/i', $useragent)) {
            $device = $deviceLoader->load('xt1068', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/XT1039/i', $useragent)) {
            $device = $deviceLoader->load('xt1039', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/XT1032/i', $useragent)) {
            $device = $deviceLoader->load('xt1032', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/XT1080/i', $useragent)) {
            $device = $deviceLoader->load('xt1080', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/XT1021/i', $useragent)) {
            $device = $deviceLoader->load('xt1021', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MotoG3/i', $useragent)) {
            $device = $deviceLoader->load('motog3', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MB612/i', $useragent)) {
            $device = $deviceLoader->load('mb612', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nexus 6p/i', $useragent)) {
            $device = $deviceLoader->load('nexus 6p', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nexus 6/i', $useragent)) {
            $device = $deviceLoader->load('nexus 6', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ME302KL/i', $useragent)) {
            $device = $deviceLoader->load('me302kl', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/nexus[ _]?7/i', $useragent)) {
            $device = $deviceLoader->load('nexus 7', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/K013/i', $useragent)) {
            $device = $deviceLoader->load('k013', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/K01E/i', $useragent)) {
            $device = $deviceLoader->load('k01e', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Z00AD/i', $useragent)) {
            $device = $deviceLoader->load('z00ad', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/K012/i', $useragent)) {
            $device = $deviceLoader->load('k012', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ME302C/i', $useragent)) {
            $device = $deviceLoader->load('me302c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/T00N/i', $useragent)) {
            $device = $deviceLoader->load('t00n', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/T00J/i', $useragent)) {
            $device = $deviceLoader->load('t00j', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/P01Y/i', $useragent)) {
            $device = $deviceLoader->load('p01y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PadFone T004/i', $useragent)) {
            $device = $deviceLoader->load('padfone t004', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PadFone 2/i', $useragent)) {
            $device = $deviceLoader->load('a68', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PadFone/i', $useragent)) {
            $device = $deviceLoader->load('padfone', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TF300TG/i', $useragent)) {
            $device = $deviceLoader->load('tf300tg', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TF300TL/i', $useragent)) {
            $device = $deviceLoader->load('tf300tl', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TF300T/i', $useragent)) {
            $device = $deviceLoader->load('tf300t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/P1801\-T/i', $useragent)) {
            $device = $deviceLoader->load('p1801-t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/WIN HD W510u/i', $useragent)) {
            $device = $deviceLoader->load('win hd w510u', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/STUDIO 5\.5/i', $useragent)) {
            $device = $deviceLoader->load('studio 5.5', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/N9500/i', $useragent)) {
            $device = $deviceLoader->load('n9500', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/tolino tab 8\.9/i', $useragent)) {
            $device = $deviceLoader->load('tab 8.9', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/tolino tab 8/i', $useragent)) {
            $device = $deviceLoader->load('tab 8', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo S660/i', $useragent)) {
            $device = $deviceLoader->load('s660', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/S5000\-F/i', $useragent)) {
            $device = $deviceLoader->load('s5000-f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/S5000\-H/i', $useragent)) {
            $device = $deviceLoader->load('s5000-h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/A7600\-H/i', $useragent)) {
            $device = $deviceLoader->load('a7600-h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LG\-L160L/i', $useragent)) {
            $device = $deviceLoader->load('l160l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo S920/i', $useragent)) {
            $device = $deviceLoader->load('s920', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo S720/i', $useragent)) {
            $device = $deviceLoader->load('s720', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IdeaTab S6000\-H/i', $useragent)) {
            $device = $deviceLoader->load('s6000-h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IdeaTabS2110AH/i', $useragent)) {
            $device = $deviceLoader->load('s2110a-h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IdeaTabS2110AF/i', $useragent)) {
            $device = $deviceLoader->load('s2110a-f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IdeaTabS2109A\-F/i', $useragent)) {
            $device = $deviceLoader->load('s2109a-f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo A606/i', $useragent)) {
            $device = $deviceLoader->load('a606', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo A850\+/i', $useragent)) {
            $device = $deviceLoader->load('a850+', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo A766/i', $useragent)) {
            $device = $deviceLoader->load('a766', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo A536/i', $useragent)) {
            $device = $deviceLoader->load('a536', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SmartTabII10/i', $useragent)) {
            $device = $deviceLoader->load('smarttab ii 10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Vodafone Smart Tab III 10/i', $useragent)) {
            $device = $deviceLoader->load('smart tab iii 10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Smart Tab 4G/i', $useragent)) {
            $device = $deviceLoader->load('smart tab 4g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Smart Tab 4/i', $useragent)) {
            $device = $deviceLoader->load('smart tab 4', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Smart Tab 3G/i', $useragent)) {
            $device = $deviceLoader->load('smart tab 3g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/P1032X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab p1032x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/P1050X/i', $useragent)) {
            $device = $deviceLoader->load('lifetab p1050x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo A7000\-a/i', $useragent)) {
            $device = $deviceLoader->load('a7000-a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LenovoA3300\-GV/i', $useragent)) {
            $device = $deviceLoader->load('a3300-gv', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo B6000\-HV/i', $useragent)) {
            $device = $deviceLoader->load('b6000-hv', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo B6000\-H/i', $useragent)) {
            $device = $deviceLoader->load('b6000-h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo B6000\-F/i', $useragent)) {
            $device = $deviceLoader->load('b6000-f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo B8080\-H/i', $useragent)) {
            $device = $deviceLoader->load('b8080-h', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo TB2\-X30F/i', $useragent)) {
            $device = $deviceLoader->load('tb2-x30f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo YT3\-X50L/i', $useragent)) {
            $device = $deviceLoader->load('yt3-x50l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo K900/i', $useragent)) {
            $device = $deviceLoader->load('k900', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/AT1010\-T/', $useragent)) {
            $device = $deviceLoader->load('at1010-t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/AT10\-A/', $useragent)) {
            $device = $deviceLoader->load('at10-a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/A10\-70F/', $useragent)) {
            $device = $deviceLoader->load('a10-70f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo A1000L\-F/', $useragent)) {
            $device = $deviceLoader->load('a1000l-f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo A1000\-F/', $useragent)) {
            $device = $deviceLoader->load('a1000-f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Lenovo A1000/', $useragent)) {
            $device = $deviceLoader->load('a1000', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/YOGA Tablet 2 Pro\-1380L/', $useragent)) {
            $device = $deviceLoader->load('1380l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/YOGA Tablet 2 Pro\-1380F/', $useragent)) {
            $device = $deviceLoader->load('1380f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/YOGA Tablet 2\-1050L/', $useragent)) {
            $device = $deviceLoader->load('1050l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/YOGA Tablet 2\-1050F/', $useragent)) {
            $device = $deviceLoader->load('1050f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/YOGA Tablet 2\-830L/', $useragent)) {
            $device = $deviceLoader->load('830l', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/YOGA Tablet 2\-830F/', $useragent)) {
            $device = $deviceLoader->load('830f', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/S208/i', $useragent)) {
            $device = $deviceLoader->load('s208', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/306SH/', $useragent)) {
            $device = $deviceLoader->load('306sh', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/JERRY/', $useragent)) {
            $device = $deviceLoader->load('jerry', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/BLOOM/', $useragent)) {
            $device = $deviceLoader->load('bloom', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/DARKSIDE/', $useragent)) {
            $device = $deviceLoader->load('darkside', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SLIDE2/', $useragent)) {
            $device = $deviceLoader->load('slide 2', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ M3 /', $useragent)) {
            $device = $deviceLoader->load('m3', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/4034D/', $useragent)) {
            $device = $deviceLoader->load('ot-4034d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/4030D/', $useragent)) {
            $device = $deviceLoader->load('ot-4030d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/4030X/', $useragent)) {
            $device = $deviceLoader->load('ot-4030x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/4012X/', $useragent)) {
            $device = $deviceLoader->load('ot-4012x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/4012A/', $useragent)) {
            $device = $deviceLoader->load('ot-4012a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/4015D/', $useragent)) {
            $device = $deviceLoader->load('ot-4015d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/4015X/', $useragent)) {
            $device = $deviceLoader->load('ot-4015x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/7041D/', $useragent)) {
            $device = $deviceLoader->load('ot-7041d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/7041X/', $useragent)) {
            $device = $deviceLoader->load('ot-7041x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/7047D/', $useragent)) {
            $device = $deviceLoader->load('ot-7047d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/7049D/', $useragent)) {
            $device = $deviceLoader->load('ot-7049d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/7025D/', $useragent)) {
            $device = $deviceLoader->load('ot-7025d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/5020D/', $useragent)) {
            $device = $deviceLoader->load('ot-5020d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/5035D/', $useragent)) {
            $device = $deviceLoader->load('ot-5035d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/5036D/', $useragent)) {
            $device = $deviceLoader->load('ot-5036d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/4037T/', $useragent)) {
            $device = $deviceLoader->load('ot-4037t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/P310X/', $useragent)) {
            $device = $deviceLoader->load('ot-p310x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/P310A/', $useragent)) {
            $device = $deviceLoader->load('ot-p310a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/P320X/', $useragent)) {
            $device = $deviceLoader->load('ot-p320x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/6040D/', $useragent)) {
            $device = $deviceLoader->load('ot-6040d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/6043D/', $useragent)) {
            $device = $deviceLoader->load('ot-6043d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/6010D/', $useragent)) {
            $device = $deviceLoader->load('ot-6010d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/6010X/', $useragent)) {
            $device = $deviceLoader->load('ot-6010x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/6015X/', $useragent)) {
            $device = $deviceLoader->load('ot-6015x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/6035R/', $useragent)) {
            $device = $deviceLoader->load('ot-6035r', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/6034R/', $useragent)) {
            $device = $deviceLoader->load('ot-6034r', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/6032/', $useragent)) {
            $device = $deviceLoader->load('ot-6032', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/6036Y/', $useragent)) {
            $device = $deviceLoader->load('ot-6036y', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/6033X/', $useragent)) {
            $device = $deviceLoader->load('ot-6033x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/6030X/', $useragent)) {
            $device = $deviceLoader->load('ot-6030x', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/6030D/', $useragent)) {
            $device = $deviceLoader->load('ot-6030d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/6050A/', $useragent)) {
            $device = $deviceLoader->load('ot-6050a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/8008D/', $useragent)) {
            $device = $deviceLoader->load('ot-8008d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/8000D/', $useragent)) {
            $device = $deviceLoader->load('ot-8000d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/5042D/', $useragent)) {
            $device = $deviceLoader->load('ot-5042d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]995/i', $useragent)) {
            $device = $deviceLoader->load('ot-995', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]997d/i', $useragent)) {
            $device = $deviceLoader->load('ot-997d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]992d/i', $useragent)) {
            $device = $deviceLoader->load('ot-992d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]991t/i', $useragent)) {
            $device = $deviceLoader->load('ot-991t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]991d/i', $useragent)) {
            $device = $deviceLoader->load('ot-991d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]991/i', $useragent)) {
            $device = $deviceLoader->load('ot-991', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]903D/i', $useragent)) {
            $device = $deviceLoader->load('ot-903d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]980/i', $useragent)) {
            $device = $deviceLoader->load('ot-980', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]985d/i', $useragent)) {
            $device = $deviceLoader->load('ot-985d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]818/i', $useragent)) {
            $device = $deviceLoader->load('ot-818', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]tab[ _]7hd/i', $useragent)) {
            $device = $deviceLoader->load('ot-tab7hd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]tab[ _]8hd/i', $useragent)) {
            $device = $deviceLoader->load('ot-tab8hd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]p321/i', $useragent)) {
            $device = $deviceLoader->load('ot-p321', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/one[ _]touch[ _]Fierce/i', $useragent)) {
            $device = $deviceLoader->load('fierce', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Archos 50b Platinum/', $useragent)) {
            $device = $deviceLoader->load('50b platinum', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Archos 50 Platinum/', $useragent)) {
            $device = $deviceLoader->load('50 platinum', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Archos 50 Titanium/', $useragent)) {
            $device = $deviceLoader->load('50 titanium', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Archos 50 Oxygen Plus/', $useragent)) {
            $device = $deviceLoader->load('50 oxygen plus', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ARCHOS 101 XS 2/', $useragent)) {
            $device = $deviceLoader->load('101 xs 2', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Archos 101d Neon/', $useragent)) {
            $device = $deviceLoader->load('101d neon', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Archos 121 Neon/', $useragent)) {
            $device = $deviceLoader->load('121 neon', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Archos 101 Neon/', $useragent)) {
            $device = $deviceLoader->load('101 neon', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Archos 101 Copper/', $useragent)) {
            $device = $deviceLoader->load('101 copper', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ZTE Blade V6/', $useragent)) {
            $device = $deviceLoader->load('blade v6', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ZTE Blade L5 Plus/', $useragent)) {
            $device = $deviceLoader->load('blade l5 plus', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ZTE Blade L6/', $useragent)) {
            $device = $deviceLoader->load('blade l6', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ZTE Blade L2/', $useragent)) {
            $device = $deviceLoader->load('blade l2', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ZTE Blade L3/', $useragent)) {
            $device = $deviceLoader->load('blade l3', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ZTE N919/', $useragent)) {
            $device = $deviceLoader->load('n919', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Beeline Pro/', $useragent)) {
            $device = $deviceLoader->load('beeline pro', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SmartTab7/', $useragent)) {
            $device = $deviceLoader->load('smarttab7', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ZTE_V829/', $useragent)) {
            $device = $deviceLoader->load('v829', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ZTE Geek/', $useragent)) {
            $device = $deviceLoader->load('v975', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ZTE LEO Q2/', $useragent)) {
            $device = $deviceLoader->load('v769m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IEOS_QUAD_10_PRO/', $useragent)) {
            $device = $deviceLoader->load('ieos quad 10 pro', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IEOS_QUAD_W/', $useragent)) {
            $device = $deviceLoader->load('ieos quad w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MAVEN_10_PLUS/', $useragent)) {
            $device = $deviceLoader->load('maven 10 plus', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/CONNECT7PRO/', $useragent)) {
            $device = $deviceLoader->load('connect 7 pro', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SPACE10_PLUS_3G/', $useragent)) {
            $device = $deviceLoader->load('space 10 plus 3g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/AT300SE/', $useragent)) {
            $device = $deviceLoader->load('at300se', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/A3\-A11/', $useragent)) {
            $device = $deviceLoader->load('a3-a11', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/A3\-A10/', $useragent)) {
            $device = $deviceLoader->load('a3-a10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/A700/', $useragent)) {
            $device = $deviceLoader->load('a700', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/B1\-711/', $useragent)) {
            $device = $deviceLoader->load('b1-711', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/B1\-770/', $useragent)) {
            $device = $deviceLoader->load('b1-770', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MediPaD13/', $useragent)) {
            $device = $deviceLoader->load('medipad 13', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MediPaD/', $useragent)) {
            $device = $deviceLoader->load('medipad', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TechniPad[_ ]10\-3G/', $useragent)) {
            $device = $deviceLoader->load('technipad 10 3g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TechniPad[_ ]10/', $useragent)) {
            $device = $deviceLoader->load('technipad 10', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/AQIPAD[_ ]7G/', $useragent)) {
            $device = $deviceLoader->load('aqiston aqipad 7g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TechniPhone[_ ]5/', $useragent)) {
            $device = $deviceLoader->load('techniphone 5', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/M7T/', $useragent)) {
            $device = $deviceLoader->load('m7t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/M83g/', $useragent)) {
            $device = $deviceLoader->load('m8 3g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ M6 /', $useragent)) {
            $device = $deviceLoader->load('m6', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ORION7o/', $useragent)) {
            $device = $deviceLoader->load('orion 7o', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/GOCLEVER TAB A93\.2/', $useragent)) {
            $device = $deviceLoader->load('a93.2', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/QUANTUM 4/', $useragent)) {
            $device = $deviceLoader->load('quantum 4', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/QUANTUM_700m/', $useragent)) {
            $device = $deviceLoader->load('quantum 700m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NT\-1009T/', $useragent)) {
            $device = $deviceLoader->load('nt-1009t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/NT\-3702M/', $useragent)) {
            $device = $deviceLoader->load('nt-3702m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Philips W336/', $useragent)) {
            $device = $deviceLoader->load('w336', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/KianoIntelect7/', $useragent)) {
            $device = $deviceLoader->load('intelect 7 3g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SUPRA_M121G/', $useragent)) {
            $device = $deviceLoader->load('m121g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/HW\-W718/', $useragent)) {
            $device = $deviceLoader->load('w718', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Micromax A59/', $useragent)) {
            $device = $deviceLoader->load('a59', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/AX512/', $useragent)) {
            $device = $deviceLoader->load('ax512', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/AX540/', $useragent)) {
            $device = $deviceLoader->load('ax540', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/s4502m/i', $useragent)) {
            $device = $deviceLoader->load('s4502m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/s4502/i', $useragent)) {
            $device = $deviceLoader->load('s4502', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/s4501m/', $useragent)) {
            $device = $deviceLoader->load('s4501m', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/s4503q/', $useragent)) {
            $device = $deviceLoader->load('s4503q', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PULID F11/', $useragent)) {
            $device = $deviceLoader->load('f11', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PULID F15/', $useragent)) {
            $device = $deviceLoader->load('f15', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/thl_4400/i', $useragent)) {
            $device = $deviceLoader->load('4400', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/thl 2015/i', $useragent)) {
            $device = $deviceLoader->load('2015', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ThL W7/i', $useragent)) {
            $device = $deviceLoader->load('w7', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ThL W8/i', $useragent)) {
            $device = $deviceLoader->load('w8', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/iDxD4/', $useragent)) {
            $device = $deviceLoader->load('idxd4 3g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PS1043MG/', $useragent)) {
            $device = $deviceLoader->load('ps1043mg', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TT7026MW/', $useragent)) {
            $device = $deviceLoader->load('tt7026mw', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/i\-mobile IQX OKU/', $useragent)) {
            $device = $deviceLoader->load('iq x oku', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/i\-mobile IQ 6A/', $useragent)) {
            $device = $deviceLoader->load('iq 6a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/RMD\-757/', $useragent)) {
            $device = $deviceLoader->load('rmd-757', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/RMD\-1040/', $useragent)) {
            $device = $deviceLoader->load('rmd-1040', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/A400/', $useragent)) {
            $device = $deviceLoader->load('a400', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/T108/', $useragent)) {
            $device = $deviceLoader->load('t108', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/T118/', $useragent)) {
            $device = $deviceLoader->load('t118', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/N\-06E/', $useragent)) {
            $device = $deviceLoader->load('n-06e', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/OK999/', $useragent)) {
            $device = $deviceLoader->load('ok999', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/PICOpad_S1\(7_3G\)/', $useragent)) {
            $device = $deviceLoader->load('picopad s1', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ ACE /', $useragent)) {
            $device = $deviceLoader->load('gt-s5830', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (!preg_match('/trident/i', $useragent)
            && (preg_match('/Android/', $useragent) || preg_match('/UCWEB/', $useragent))
            && preg_match('/iphone[ ]?5c/i', $useragent)
        ) {
            $device = $deviceLoader->load('iphone 5c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (!preg_match('/trident/i', $useragent)
            && (preg_match('/Android/', $useragent) || preg_match('/UCWEB/', $useragent))
            && preg_match('/iphone[ ]?5/i', $useragent)
        ) {
            $device = $deviceLoader->load('iphone 5', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (!preg_match('/trident/i', $useragent)
            && preg_match('/Android/', $useragent)
            && preg_match('/iphone[ ]?6c/i', $useragent)
        ) {
            $device = $deviceLoader->load('iphone 6c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (!preg_match('/trident/i', $useragent)
            && preg_match('/Android/', $useragent)
            && preg_match('/iphone/i', $useragent)
        ) {
            $device = $deviceLoader->load('iphone', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/DG800/', $useragent)) {
            $device = $deviceLoader->load('dg800', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/DG330/', $useragent)) {
            $device = $deviceLoader->load('dg330', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/DG2014/', $useragent)) {
            $device = $deviceLoader->load('dg2014', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/F3_Pro/', $useragent)) {
            $device = $deviceLoader->load('f3 pro', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TAB785DUAL/', $useragent)) {
            $device = $deviceLoader->load('tab785 dual', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Norma 2/', $useragent)) {
            $device = $deviceLoader->load('norma 2', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Adi_5S/', $useragent)) {
            $device = $deviceLoader->load('adi5s', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/BRAVIS NP 844/', $useragent)) {
            $device = $deviceLoader->load('np 844', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/fnac 4\.5/', $useragent)) {
            $device = $deviceLoader->load('phablet 4.5', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/T880G/', $useragent)) {
            $device = $deviceLoader->load('t880g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TCL M2U/', $useragent)) {
            $device = $deviceLoader->load('m2u', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TCL S720T/', $useragent)) {
            $device = $deviceLoader->load('s720t', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/radxa rock/', $useragent)) {
            $device = $deviceLoader->load('rock', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/DM015K/', $useragent)) {
            $device = $deviceLoader->load('dm015k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/KC\-S701/', $useragent)) {
            $device = $deviceLoader->load('kc-s701', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/FP1U/', $useragent)) {
            $device = $deviceLoader->load('fp1u', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/FP1/', $useragent)) {
            $device = $deviceLoader->load('fp1', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ImPAD 0413/', $useragent)) {
            $device = $deviceLoader->load('impad 0413', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ImPAD6213M_v2/i', $useragent)) {
            $device = $deviceLoader->load('impad 6213m v2', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/KFASWI/', $useragent)) {
            $device = $deviceLoader->load('kfaswi', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/SD4930UR/', $useragent)) {
            $device = $deviceLoader->load('sd4930ur', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Art 3G/', $useragent)) {
            $device = $deviceLoader->load('art 3g', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/R815/', $useragent)) {
            $device = $deviceLoader->load('r815', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TAB\-970/', $useragent)) {
            $device = $deviceLoader->load('tab-970', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IM\-A900K/', $useragent)) {
            $device = $deviceLoader->load('im-a900k', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Pacific 800/i', $useragent)) {
            $device = $deviceLoader->load('pacific 800', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Pacific800i/i', $useragent)) {
            $device = $deviceLoader->load('pacific 800i', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TM\-7055HD/i', $useragent)) {
            $device = $deviceLoader->load('tm-7055hd', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TM\-5204/i', $useragent)) {
            $device = $deviceLoader->load('tm-5204', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/AP\-804/i', $useragent)) {
            $device = $deviceLoader->load('ap-804', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Atlantis 1010A/i', $useragent)) {
            $device = $deviceLoader->load('atlantis 1010a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/AC0732C/i', $useragent)) {
            $device = $deviceLoader->load('ac0732c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/RC9724C/i', $useragent)) {
            $device = $deviceLoader->load('rc9724c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/MT0739D/i', $useragent)) {
            $device = $deviceLoader->load('mt0739d', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/LC0720C/i', $useragent)) {
            $device = $deviceLoader->load('lc0720c', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TBD1083/i', $useragent)) {
            $device = $deviceLoader->load('tbd1083', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TBDC1093/i', $useragent)) {
            $device = $deviceLoader->load('tbdc1093', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/A66A/', $useragent)) {
            $device = $deviceLoader->load('a66a', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/IP1020/', $useragent)) {
            $device = $deviceLoader->load('ip1020', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Turbo Pad 500/', $useragent)) {
            $device = $deviceLoader->load('pad 500', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Turbo X6/i', $useragent)) {
            $device = $deviceLoader->load('turbo x6', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Novo7Fire/i', $useragent)) {
            $device = $deviceLoader->load('novo 7 fire', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/numy_note_9/i', $useragent)) {
            $device = $deviceLoader->load('numy note 9', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TX08/', $useragent)) {
            $device = $deviceLoader->load('tx08', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/TX18/', $useragent)) {
            $device = $deviceLoader->load('tx18', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/U25GT\-C4W/', $useragent)) {
            $device = $deviceLoader->load('u25gt-c4w', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/iPad/', $useragent) && !preg_match('/tripadvisor/', $useragent)) {
            $device = $deviceLoader->load('ipad', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/iPod/', $useragent) && !preg_match('/iPodder/', $useragent)) {
            $device = $deviceLoader->load('ipod touch', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (!preg_match('/trident/i', $useragent) && preg_match('/iPhone/i', $useragent)) {
            $device = $deviceLoader->load('iphone', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Puffin\/[\d\.]+IT/', $useragent)) {
            $device = $deviceLoader->load('ipad', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Puffin\/[\d\.]+IP/', $useragent)) {
            $device = $deviceLoader->load('iphone', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/Galaxy Nexus/i', $useragent)) {
            $device = $deviceLoader->load('galaxy nexus', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/galaxy tab 2 3g/i', $useragent)) {
            $device = $deviceLoader->load('gt-p3100', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/galaxy tab 2/i', $useragent)) {
            $device = $deviceLoader->load('gt-p3110', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/galaxy j5/i', $useragent)) {
            $device = $deviceLoader->load('sm-j500', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/OMNIA7/i', $useragent)) {
            $device = $deviceLoader->load('gt-i8700', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/CCE SK352/i', $useragent)) {
            $device = $deviceLoader->load('sk352', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/ONE/', $useragent)
            && !preg_match('/PodcastOne/i', $useragent)
            && !preg_match('/iOS/', $useragent)
        ) {
            $device = $deviceLoader->load('m7', $useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } elseif (preg_match('/CFNetwork/', $useragent)) {
            $device = (new \BrowserDetector\Factory\Device\DarwinFactory($cache, $deviceLoader))->detect($useragent);

            $deviceBrand       = $device->getBrand();
            $deviceCode        = $device->getDeviceName();
            $devicePointing    = $device->getPointingMethod();
            $deviceType        = $device->getType();
            $deviceMaker       = $device->getManufacturer();
            $deviceName        = $device->getMarketingName();
            $deviceOrientation = $device->getDualOrientation();
        } else {
            /** @var \UaResult\Result\Result $result */
            $result = $detector->getBrowser($useragent);

            $device = $result->getDevice();

            if ($deviceCode === $device->getDeviceName()) {
                $deviceBrand       = $device->getBrand();
                $devicePointing    = $device->getPointingMethod();
                $deviceType        = $device->getType();
                $deviceMaker       = $device->getManufacturer();
                $deviceName        = $device->getMarketingName();
                $deviceOrientation = $device->getDualOrientation();
            } elseif ('general Mobile Device' === $device->getDeviceName() && in_array(
                $deviceCode,
                ['general Mobile Phone', 'general Tablet']
            )
            ) {
                $deviceBrand       = $device->getBrand();
                $deviceCode        = $device->getDeviceName();
                $devicePointing    = $device->getPointingMethod();
                $deviceType        = $device->getType();
                $deviceMaker       = $device->getManufacturer();
                $deviceName        = $device->getMarketingName();
                $deviceOrientation = $device->getDualOrientation();
            } elseif ('Windows RT Tablet' === $device->getDeviceName() && $deviceCode === 'general Tablet') {
                $deviceBrand       = $device->getBrand();
                $deviceCode        = $device->getDeviceName();
                $devicePointing    = $device->getPointingMethod();
                $deviceType        = $device->getType();
                $deviceMaker       = $device->getManufacturer();
                $deviceName        = $device->getMarketingName();
                $deviceOrientation = $device->getDualOrientation();
            } elseif ('Windows Desktop' === $device->getDeviceName()
                && $deviceCode === 'unknown'
                && in_array(
                    $platform->getMarketingName(),
                    ['Windows 7', 'Windows 8', 'Windows 8.1', 'Windows 10', 'Windows XP', 'Windows Vista']
                )
            ) {
                $deviceBrand       = $device->getBrand();
                $deviceCode        = $device->getDeviceName();
                $devicePointing    = $device->getPointingMethod();
                $deviceType        = $device->getType();
                $deviceMaker       = $device->getManufacturer();
                $deviceName        = $device->getMarketingName();
                $deviceOrientation = $device->getDualOrientation();
            } elseif ('Linux Desktop' === $device->getDeviceName()
                && $deviceCode === 'unknown'
                && in_array($platform->getMarketingName(), ['Linux'])
            ) {
                $deviceBrand       = $device->getBrand();
                $deviceCode        = $device->getDeviceName();
                $devicePointing    = $device->getPointingMethod();
                $deviceType        = $device->getType();
                $deviceMaker       = $device->getManufacturer();
                $deviceName        = $device->getMarketingName();
                $deviceOrientation = $device->getDualOrientation();
            } elseif ('Macintosh' === $device->getDeviceName()
                && $deviceCode === 'unknown'
                && in_array($platform->getMarketingName(), ['Mac OS X', 'macOS'])
            ) {
                $deviceBrand       = $device->getBrand();
                $deviceCode        = $device->getDeviceName();
                $devicePointing    = $device->getPointingMethod();
                $deviceType        = $device->getType();
                $deviceMaker       = $device->getManufacturer();
                $deviceName        = $device->getMarketingName();
                $deviceOrientation = $device->getDualOrientation();
            }
        }

        if ($device instanceof \UaResult\Device\DeviceInterface) {
            if (null === $deviceBrand) {
                $deviceBrand       = $device->getBrand();
            }
            if (null === $deviceCode) {
                $deviceCode        = $device->getDeviceName();
            }
            if (null === $devicePointing) {
                $devicePointing    = $device->getPointingMethod();
            }
            if (null === $deviceType) {
                $deviceType        = $device->getType();
            }
            if (null === $deviceMaker) {
                $deviceMaker       = $device->getManufacturer();
            }
            if (null === $deviceName) {
                $deviceName        = $device->getMarketingName();
            }
            if (null === $deviceOrientation) {
                $deviceOrientation = $device->getDualOrientation();
            }
            if (null === $isTablet) {
                $isTablet = $device->getType()->isTablet();
            }
        } else {
            $deviceBrand       = 'unknown';
            $deviceCode        = 'unknown';
            $devicePointing    = null;
            $deviceType        = null;
            $deviceMaker       = 'unknown';
            $deviceName        = 'unknown';
            $deviceOrientation = false;
            $isTablet          = false;
        }

        return [
            $deviceName,
            $deviceMaker,
            $deviceType,
            $devicePointing,
            $deviceCode,
            $deviceBrand,
            $mobileDevice,
            $isTablet,
            $deviceOrientation,
            $device,
        ];
    }
}
