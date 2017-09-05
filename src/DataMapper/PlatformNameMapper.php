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
class PlatformNameMapper
{
    /**
     * maps the name of the operating system
     *
     * @param string $osName
     *
     * @return string|null
     */
    public function mapOsName(string $osName): ?string
    {
        switch (mb_strtolower($osName)) {
            case '':
            case 'unknown':
            case 'other':
                $osName = null;
                break;
            case 'winxp':
            case 'win7':
            case 'win8':
            case 'win8.1':
            case 'win9':
            case 'win10':
            case 'winvista':
            case 'win2000':
            case 'win2003':
            case 'win98':
            case 'win95':
            case 'win31':
            case 'win32':
            case 'winnt':
            case 'winme':
            case 'windows 98':
            case 'windows 2000':
            case 'windows xp':
            case 'windows vista':
            case 'windows 7':
            case 'windows 8':
            case 'windows 8.1':
            case 'windows 10':
            case 'windows server':
            case 'windows unknown ver':
                $osName = 'Windows';
                break;
            case 'winphone7':
            case 'winphone7.5':
            case 'winphone8':
            case 'winphone8.1':
            case 'windows phone':
            case 'windows phone 7':
                $osName = 'Windows Phone OS';
                break;
            case 'winrt8':
            case 'winrt8.1':
                $osName = 'Windows RT';
                break;
            case 'winmobile':
                $osName = 'Windows Mobile OS';
                break;
            case 'wince':
                $osName = 'Windows CE';
                break;
            case 'blackberry os':
                $osName = 'RIM OS';
                break;
            case 'mac':
            case 'macosx':
            case 'os x':
            case 'mac osx':
                $osName = 'Mac OS X';
                break;
            case 'jvm':
            case 'java':
                $osName = 'Java';
                break;
            case 'bada os':
                $osName = 'Bada';
                break;
            case 'symbianos':
            case 'nokia series 40':
            case 'symbian os series 40':
                $osName = 'Symbian OS';
                break;
            case 'gnu/linux':
                $osName = 'Linux';
                break;
            case 'chrome os':
                $osName = 'ChromeOS';
                break;
            case 'mint':
                $osName = 'Linux Mint';
                break;
            default:
                // nothing to do here
                break;
        }

        return $osName;
    }
}
