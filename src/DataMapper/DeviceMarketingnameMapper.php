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

/**
 * class with caching and update capabilities
 *
 * @category  ua-data-mapper
 *
 * @author    Thomas Mueller <mimmi20@live.de>
 * @copyright 2015-2017 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
class DeviceMarketingnameMapper
{
    /**
     * maps the marketing name of a device
     *
     * @param string|null $marketingName
     *
     * @return string|null
     */
    public function mapDeviceMarketingName(?string $marketingName = null): ?string
    {
        if (null === $marketingName) {
            return null;
        }

        switch (mb_strtolower($marketingName)) {
            case '':
            case 'unknown':
            case 'other':
            case 'various':
                $marketingName = null;
                break;
            case 'lg optimus chat':
                $marketingName = 'Optimus Chat';
                break;
            case 't mobile move balance':
                $marketingName = 'T-Mobile Move Balance';
                break;
            case 'xperia arc so-01c for docomo':
                $marketingName = 'Xperia Arc SO-01C for DoCoMo';
                break;
            case 'galaxy sii':
                $marketingName = 'Galaxy S II';
                break;
            case 'galaxy sii plus':
                $marketingName = 'Galaxy S II Plus';
                break;
            case 'galaxy siii':
            case 'galaxy s3':
                $marketingName = 'Galaxy S III';
                break;
            case 'galaxy s3 lte international':
                $marketingName = 'Galaxy S III LTE International';
                break;
            case 'lifetab':
                $marketingName = 'LifeTab';
                break;
            case 'galaxy sii epic 4g touch':
                $marketingName = 'Galaxy S II Epic 4G Touch';
                break;
            case 'prestigio multipad':
                $marketingName = 'Multipad';
                break;
            case 'samsung galaxy tab 7.0 plus':
                $marketingName = 'Galaxy Tab 7.0 Plus';
                break;
            case 'one touch m\'pop':
                $marketingName = 'One Touch MPop';
                break;
            case 'people\'s tablet':
                $marketingName = 'Peoples Tablet';
                break;
            case 'lumia 530 dual sim':
                $marketingName = 'Lumia 530';
                break;
            case 'droid razr i':
                $marketingName = 'RAZR i';
                break;
            case 'nokia asha 300':
            case '300':
                $marketingName = 'Asha 300';
                break;
            case 'galaxy tabpro 10.1" wifi':
                $marketingName = 'Galaxy Tab Pro 10.1 WiFi';
                break;
            case 'galaxy tab 3 10.1" wifi':
                $marketingName = 'Galaxy Tab 3 10.1 3G WiFi';
                break;
            case 'galaxy tab 4 10.1" wifi':
                $marketingName = 'Galaxy Tab 4 10.1 WiFi';
                break;
            case 'galaxy tab 4 10.1" lte':
                $marketingName = 'Galaxy Tab 4 10.1 LTE';
                break;
            case 'galaxy tab 2 10.1" wifi':
                $marketingName = 'Galaxy Tab 2 10.1 WiFi';
                break;
            case 'galaxy s5':
                $marketingName = 'Galaxy S5';
                break;
            case 'gt-i9515':
                $marketingName = 'Galaxy S4';
                break;
            case 'galaxy tab 3 10.1"':
            case 'galaxy tab 3 (10.1)':
                $marketingName = 'Galaxy Tab 3 10.1 3G';
                break;
            case 'kindle fire hdx 7" wifi':
                $marketingName = 'Kindle Fire HDX 7 WiFi';
                break;
            case 'ideatab b8080-f':
                $marketingName = 'Yoga Tab 10 HD+';
                break;
            case 'galaxy note 4':
                $marketingName = 'Galaxy Note 4';
                break;
            case 'b8000-f':
                $marketingName = 'Yoga B8000-F';
                break;
            case 'iconia a3':
                $marketingName = 'Iconia Tab A3';
                break;
            case 'galaxy trend lite':
                $marketingName = 'Galaxy Trend Lite';
                break;
            case 'one touch 6030x':
                $marketingName = 'One Touch Idol';
                break;
            case 'one mini2':
                $marketingName = 'One Mini 2';
                break;
            case 's iii mini':
                $marketingName = 'Galaxy S III Mini';
                break;
            default:
                // nothing to do here
                break;
        }

        return $marketingName;
    }

    /**
     * maps the marketing name of a device from the device name
     *
     * @param string|null $deviceName
     *
     * @return string|null
     */
    public function mapDeviceName(?string $deviceName = null): ?string
    {
        if (null === $deviceName) {
            return null;
        }

        $marketingName = null;

        switch (mb_strtolower($deviceName)) {
            case '':
            case 'unknown':
            case 'other':
            case 'various':
            case 'android 1.6':
            case 'android 2.0':
            case 'android 2.1':
            case 'android 2.2':
            case 'android 2.3':
            case 'android 3.0':
            case 'android 3.1':
            case 'android 3.2':
            case 'android 4.0':
            case 'android 4.1':
            case 'android 4.2':
            case 'android 4.3':
            case 'android 4.4':
            case 'android 5.0':
            case 'android 2.2 tablet':
            case 'android 4 tablet':
            case 'android 4.1 tablet':
            case 'android 4.2 tablet':
            case 'android 4.3 tablet':
            case 'android 4.4 tablet':
            case 'android 5.0 tablet':
            case 'disguised as macintosh':
            case 'mini 1':
            case 'mini 4':
            case 'mini 5':
            case 'windows mobile 6.5':
            case 'windows mobile 7':
            case 'windows mobile 7.5':
            case 'windows phone 7':
            case 'windows phone 8':
            case 'fennec tablet':
            case 'tablet on android':
            case 'fennec':
            case 'opera for series 60':
            case 'opera mini for s60':
            case 'windows mobile (opera)':
            case 'mobi for android':
            case 'nokia unrecognized ovi browser':
            case 'mozilla firefox for android':
            case 'firefox for android tablet':
            case 'firefox for android':
                $marketingName = null;
                break;
            // Acer
            case 'acer e320':
                $marketingName = 'Liquid Express';
                break;
            // HP
            case 'touchpad':
                $marketingName = 'Touchpad';
                break;
            // Medion
            case 'p9514':
            case 'lifetab p9514':
                $marketingName = 'LifeTab P9514';
                break;
            case 'lifetab s9512':
                $marketingName = 'LifeTab S9512';
                break;
            // HTC
            case 'htc desire sv':
                $marketingName = 'Desire SV';
                break;
            // Apple
            case 'ipad':
                $marketingName = 'iPad';
                break;
            case 'iphone':
                $marketingName = 'iPhone';
                break;
            case 'e610':
                $marketingName = 'Optimus L5';
                break;
            default:
                // nothing to do here
                break;
        }

        return $marketingName;
    }
}
