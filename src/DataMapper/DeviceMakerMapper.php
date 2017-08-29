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
class DeviceMakerMapper
{
    /**
     * maps the maker of a device
     *
     * @param string      $deviceMaker
     * @param string|null $deviceName
     *
     * @return string|null
     */
    public function mapDeviceMaker($deviceMaker, $deviceName = null)
    {
        if (null === $deviceName) {
            return;
        }

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
            case 'mozilla firefox for android':
            case 'firefox for android tablet':
            case 'firefox for android':
                $deviceMaker = null;
                break;
            // Motorola
            case 'motomz616':
            case 'motoxt610':
            case 'motxt912b':
                $deviceMaker = 'Motorola';
                break;
            // LG
            case 'lg/c550/v1.0':
                $deviceMaker = 'LG';
                break;
            // Samsung
            case 'gt s8500':
            case 'gp-p6810':
            case 'gt-i8350':
            case 'gt-i9001':
            case 'gt-i9100':
            case 'gt-i9300':
            case 'samsung gt-i9300/i9300xxdlih':
            case 'gt i7500':
            case 'gt-p5110':
            case 'gt s5620':
            case 'sch-i699':
            case 'sgh-i917':
            case 'sgh-i957':
            case 'sgh-i900v':
            case 'sgh i900':
            case 'sph-930':
                $deviceMaker = 'Samsung';
                break;
            // Acer
            case 'acer e310':
            case 'acer e320':
                $deviceMaker = 'Acer';
                break;
            // HTC
            case 'sensationxe beats z715e':
            case 's510b':
            case 'htc desire sv':
                $deviceMaker = 'HTC';
                break;
            // Asus
            case 'asus-padfone':
                $deviceMaker = 'Asus';
                break;
            // Creative
            case 'creative ziio7':
                $deviceMaker = 'Creative';
                break;
            // HP
            case 'touchpad':
                $deviceMaker = 'HP';
                break;
            // Huawei
            case 'u8800':
                $deviceMaker = 'Huawei';
                break;
            // Amazon
            case 'd01400':
                $deviceMaker = 'Amazon';
                break;
            // Nokia
            case 'nokia asha 201':
            case 'nokia unrecognized ovi browser':
                $deviceMaker = 'Nokia';
                break;
            // Medion
            case 'p9514':
            case 'lifetab p9514':
            case 'lifetab s9512':
                $deviceMaker = 'Medion';
                break;
            // Apple
            case 'ipad':
            case 'iphone':
                $deviceMaker = 'Apple Inc';
                break;
            default:
                $deviceMaker = (new MakerMapper())->mapMaker($deviceMaker);
                break;
        }

        return $deviceMaker;
    }
}
