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

use BrowserDetector\Bits\Os;
use BrowserDetector\Detector;
use BrowserDetector\Factory;
use BrowserDetector\Loader\PlatformLoader;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class Platform
 *
 * @category   Browscap Helper
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 */
class Platform
{
    /**
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param string                            $useragent
     * @param \BrowserDetector\Detector         $detector
     * @param string                            $platformCodenameDetector
     * @param string                            $platformMarketingnameDetector
     * @param string                            $platformMakerNameDetector
     * @param string                            $platformVersionDetector
     *
     * @throws \BrowserDetector\Loader\NotFoundException
     * @throws \UnexpectedValueException
     *
     * @return array
     */
    public function detect(
        CacheItemPoolInterface $cache,
        $useragent,
        Detector $detector,
        $platformCodenameDetector,
        $platformMarketingnameDetector = 'unknown',
        $platformMakerNameDetector = 'unknown',
        $platformVersionDetector = 'unknown'
    ) {
        $platformNameBrowscap           = 'unknown';
        $platformVersionBrowscap        = 'unknown';
        $platformMakerBrowscap          = 'unknown';
        $platformDescriptionBrowscap    = 'unknown';

        $platformLoader = new PlatformLoader($cache);
        $platformBits   = (new Os($useragent))->getBits();

        $win16    = false;
        $win32    = false;
        $win64    = false;
        $standard = true;
        $platform = null;

        if (preg_match('/windows phone/i', $useragent)) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformNameBrowscap        = 'WinPhone';
            $platformMakerBrowscap       = 'Microsoft Corporation';
            $platformDescriptionBrowscap = 'unknown';
        } elseif (preg_match('/Puffin\/[\d\.]+I(T|P)/', $useragent)) {
            $platform = $platformLoader->load('ios', $useragent);

            $platformNameBrowscap           = 'iOS';
            $platformMakerBrowscap          = 'Apple Inc';
            $platformDescriptionBrowscap    = 'iPod, iPhone & iPad';
        } elseif (preg_match('/Puffin\/[\d\.]+A(T|P)/', $useragent)) {
            $platform = $platformLoader->load('android', $useragent);

            $platformNameBrowscap           = 'Android';
            $platformMakerBrowscap          = 'Google Inc';
            $platformDescriptionBrowscap    = 'Android OS';
        } elseif (preg_match('/Puffin\/[\d\.]+W(T|P)/', $useragent)) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformNameBrowscap        = 'Ubuntu';
            $platformMakerBrowscap       = 'Canonical Foundation';
            $platformDescriptionBrowscap = 'Ubuntu Linux';
        } elseif (false !== mb_strpos($useragent, 'Windows Phone')) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformNameBrowscap        = 'WinPhone';
            $platformMakerBrowscap       = 'Microsoft Corporation';
            $platformDescriptionBrowscap = 'unknown';
        } elseif (preg_match('/Windows Mobile; WCE/', $useragent)) {
            $platform = $platformLoader->load('windows ce', $useragent);

            $platformNameBrowscap        = 'WinCE';
            $platformMakerBrowscap       = 'Microsoft Corporation';
            $platformDescriptionBrowscap = 'Windows CE';
        } elseif (preg_match('/windows ce/i', $useragent)) {
            $platform = $platformLoader->load('windows ce', $useragent);

            $platformNameBrowscap        = 'WinCE';
            $platformMakerBrowscap       = 'Microsoft Corporation';
            $platformDescriptionBrowscap = 'Windows CE';
        } elseif (false !== mb_strpos($useragent, 'wds')) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformNameBrowscap        = 'WinPhone';
            $platformMakerBrowscap       = 'Microsoft Corporation';
            $platformDescriptionBrowscap = 'unknown';

            if (preg_match('/wds (\d+\.\d+)/', $useragent, $matches)) {
                $platformVersionBrowscap = $matches[1];
            }
        } elseif (false !== mb_stripos($useragent, 'wpdesktop')) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformNameBrowscap        = 'WinPhone';
            $platformMakerBrowscap       = 'Microsoft Corporation';
            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== mb_stripos($useragent, 'xblwp7')) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformNameBrowscap        = 'WinPhone';
            $platformMakerBrowscap       = 'Microsoft Corporation';
            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== mb_stripos($useragent, 'zunewp7')) {
            $platform = $platformLoader->load('windows phone', $useragent);

            $platformNameBrowscap        = 'WinPhone';
            $platformMakerBrowscap       = 'Microsoft Corporation';
            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== mb_strpos($useragent, 'Tizen')) {
            $platform = $platformLoader->load('tizen', $useragent);

            $platformNameBrowscap           = 'Tizen';
            $platformMakerBrowscap          = 'unknown';
            $platformDescriptionBrowscap    = 'unknown';
        } elseif (preg_match('/MIUI/', $useragent)) {
            $platform = $platformLoader->load('miui os', $useragent);

            $platformNameBrowscap        = 'Miui OS';
            $platformMakerBrowscap       = 'Xiaomi Tech';
            $platformDescriptionBrowscap = 'a fork of Android OS by Xiaomi';
        } elseif (false !== mb_strpos($useragent, 'Linux; Android')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformNameBrowscap           = 'Android';
            $platformMakerBrowscap          = 'Google Inc';
            $platformDescriptionBrowscap    = 'Android OS';

            if (preg_match('/Linux; Android (\d+\.\d+)/', $useragent, $matches)) {
                $platformVersionBrowscap = $matches[1];
            }
        } elseif (false !== mb_strpos($useragent, 'Linux; U; Android')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformNameBrowscap           = 'Android';
            $platformMakerBrowscap          = 'Google Inc';
            $platformDescriptionBrowscap    = 'Android OS';

            if (preg_match('/Linux; U; Android (\d+\.\d+)/', $useragent, $matches)) {
                $platformVersionBrowscap = $matches[1];
            }
        } elseif (false !== mb_strpos($useragent, 'U; Adr')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformNameBrowscap           = 'Android';
            $platformMakerBrowscap          = 'Google Inc';
            $platformDescriptionBrowscap    = 'Android OS';

            if (preg_match('/U; Adr (\d+\.\d+)/', $useragent, $matches)) {
                $platformVersionBrowscap = $matches[1];
            }
        } elseif (false !== mb_strpos($useragent, 'Android') || false !== mb_strpos($useragent, 'MTK')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformNameBrowscap           = 'Android';
            $platformMakerBrowscap          = 'Google Inc';
            $platformDescriptionBrowscap    = 'Android OS';
        } elseif (false !== mb_strpos($useragent, 'UCWEB/2.0 (Linux; U; Opera Mini')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformNameBrowscap           = 'Android';
            $platformMakerBrowscap          = 'Google Inc';
            $platformDescriptionBrowscap    = 'Android OS';
        } elseif (false !== mb_strpos($useragent, 'Linux; GoogleTV')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformNameBrowscap           = 'Android';
            $platformMakerBrowscap          = 'Google Inc';
            $platformDescriptionBrowscap    = 'Android OS';
        } elseif (false !== mb_strpos($useragent, 'OpenBSD')) {
            $platform = $platformLoader->load('openbsd', $useragent);

            $platformNameBrowscap          = 'OpenBSD';
            $platformDescriptionBrowscap   = 'unknown';
        } elseif (false !== mb_strpos($useragent, 'Symbian') || false !== mb_strpos($useragent, 'Series 60')) {
            $platform = $platformLoader->load('symbian', $useragent);

            $platformNameBrowscap           = 'SymbianOS';
            $platformMakerBrowscap          = 'Symbian Foundation';
            $platformDescriptionBrowscap    = 'unknown';
        } elseif (false !== mb_strpos($useragent, 'MIDP')) {
            $platform = $platformLoader->load('java', $useragent);

            $platformNameBrowscap           = 'JAVA';
            $platformMakerBrowscap          = 'Oracle';
            $platformDescriptionBrowscap    = 'unknown';
        } elseif (false !== mb_strpos($useragent, 'Windows NT 10.0')) {
            $platform = $platformLoader->load('windows nt 10.0', $useragent);

            $platformNameBrowscap           = 'Win10';
            $platformVersionBrowscap        = '10.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== mb_strpos($useragent, 'Windows NT 6.4')) {
            $platform = $platformLoader->load('windows nt 6.4', $useragent);

            $platformNameBrowscap           = 'Win10';
            $platformVersionBrowscap        = '6.4';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== mb_strpos($useragent, 'Windows NT 6.3') && false !== mb_strpos($useragent, 'ARM')) {
            $platform = $platformLoader->load('windows nt 6.3; arm', $useragent);

            $platformNameBrowscap           = 'Win8.1';
            $platformVersionBrowscap        = '6.3';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== mb_strpos($useragent, 'Windows NT 6.3')) {
            $platform = $platformLoader->load('windows nt 6.3', $useragent);

            $platformNameBrowscap           = 'Win8.1';
            $platformVersionBrowscap        = '6.3';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== mb_strpos($useragent, 'Windows NT 6.2') && false !== mb_strpos($useragent, 'ARM')) {
            $platform = $platformLoader->load('windows nt 6.2; arm', $useragent);

            $platformNameBrowscap           = 'Win8';
            $platformVersionBrowscap        = '6.2';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== mb_strpos($useragent, 'Windows NT 6.2')) {
            $platform = $platformLoader->load('windows nt 6.2', $useragent);

            $platformNameBrowscap           = 'Win8';
            $platformVersionBrowscap        = '6.2';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== mb_strpos($useragent, 'Windows NT 6.1')) {
            $platform = $platformLoader->load('windows nt 6.1', $useragent);

            $platformNameBrowscap           = 'Win7';
            $platformVersionBrowscap        = '6.1';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== mb_strpos($useragent, 'Windows NT 6')) {
            $platform = $platformLoader->load('windows nt 6.0', $useragent);

            $platformNameBrowscap           = 'WinVista';
            $platformVersionBrowscap        = '6.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== mb_strpos($useragent, 'Windows NT 5.3')) {
            $platform = $platformLoader->load('windows nt 5.3', $useragent);

            $platformNameBrowscap           = 'WinXP';
            $platformVersionBrowscap        = '5.3';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== mb_strpos($useragent, 'Windows NT 5.2')) {
            $platform = $platformLoader->load('windows nt 5.2', $useragent);

            $platformNameBrowscap           = 'WinXP';
            $platformVersionBrowscap        = '5.2';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== mb_strpos($useragent, 'Windows NT 5.1')) {
            $platform = $platformLoader->load('windows nt 5.1', $useragent);

            $platformNameBrowscap           = 'WinXP';
            $platformVersionBrowscap        = '5.1';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== mb_strpos($useragent, 'Windows NT 5.01')) {
            $platform = $platformLoader->load('windows nt 5.01', $useragent);

            $platformNameBrowscap           = 'Win2000';
            $platformVersionBrowscap        = '5.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== mb_strpos($useragent, 'Windows NT 5.0')) {
            $platform = $platformLoader->load('windows nt 5.0', $useragent);

            $platformNameBrowscap           = 'Win2000';
            $platformVersionBrowscap        = '5.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== mb_strpos($useragent, 'Windows NT 4.10')) {
            $platform = $platformLoader->load('windows nt 4.10', $useragent);

            $platformNameBrowscap           = 'WinNT';
            $platformVersionBrowscap        = '4.1';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== mb_strpos($useragent, 'Windows NT 4.1')) {
            $platform = $platformLoader->load('windows nt 4.1', $useragent);

            $platformNameBrowscap           = 'WinNT';
            $platformVersionBrowscap        = '4.1';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== mb_strpos($useragent, 'Windows NT 4.0')) {
            $platform = $platformLoader->load('windows nt 4.0', $useragent);

            $platformNameBrowscap           = 'WinNT';
            $platformVersionBrowscap        = '4.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== mb_strpos($useragent, 'Windows NT 3.5')) {
            $platform = $platformLoader->load('windows nt 3.5', $useragent);

            $platformNameBrowscap           = 'WinNT';
            $platformVersionBrowscap        = '3.5';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== mb_strpos($useragent, 'Windows NT 3.1')) {
            $platform = $platformLoader->load('windows nt 3.1', $useragent);

            $platformNameBrowscap           = 'WinNT';
            $platformVersionBrowscap        = '3.1';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== mb_strpos($useragent, 'Windows[ \-]NT')) {
            $platform = $platformLoader->load('windows nt', $useragent);

            $platformNameBrowscap           = 'WinNT';
            $platformVersionBrowscap        = 'unknown';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== mb_stripos($useragent, 'cygwin')) {
            $platform = $platformLoader->load('cygwin', $useragent);

            $platformNameBrowscap           = 'CygWin';
            $platformVersionBrowscap        = 'unknown';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformDescriptionBrowscap    = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== mb_strpos($useragent, 'CPU OS')) {
            $platform = $platformLoader->load('ios', $useragent);

            $platformNameBrowscap           = 'iOS';
            $platformMakerBrowscap          = 'Apple Inc';
            $platformDescriptionBrowscap    = 'iPod, iPhone & iPad';

            if (preg_match('/CPU OS (\d+\_\d+)/', $useragent, $matches)) {
                $platformVersionBrowscap = str_replace('_', '.', $matches[1]);
            }
        } elseif (false !== mb_strpos($useragent, 'CPU iPhone OS')) {
            $platform = $platformLoader->load('ios', $useragent);

            $platformNameBrowscap           = 'iOS';
            $platformMakerBrowscap          = 'Apple Inc';
            $platformDescriptionBrowscap    = 'iPod, iPhone & iPad';

            if (preg_match('/CPU iPhone OS (\d+\_\d+)/', $useragent, $matches)) {
                $platformVersionBrowscap = str_replace('_', '.', $matches[1]);
            }
        } elseif (false !== mb_strpos($useragent, 'CPU like Mac OS X')) {
            $platform = $platformLoader->load('ios', $useragent);

            $platformNameBrowscap           = 'iOS';
            $platformMakerBrowscap          = 'Apple Inc';
            $platformDescriptionBrowscap    = 'iPod, iPhone & iPad';

            if (preg_match('/CPU like Mac OS X (\d+\_\d+)/', $useragent, $matches)) {
                $platformVersionBrowscap = str_replace('_', '.', $matches[1]);
            }
        } elseif (false !== mb_strpos($useragent, 'iOS')) {
            $platform = $platformLoader->load('ios', $useragent);

            $platformNameBrowscap           = 'iOS';
            $platformMakerBrowscap          = 'Apple Inc';
            $platformDescriptionBrowscap    = 'iPod, iPhone & iPad';
        } elseif (false !== mb_strpos($useragent, 'Mac OS X')) {
            $platform = $platformLoader->load('mac os x', $useragent);

            $platformMakerBrowscap          = 'Apple Inc';

            $platformDescriptionBrowscap = 'Mac OS X';

            if (preg_match('/Mac OS X (\d+[\_\.]\d+)/', $useragent, $matches)) {
                $platformVersionBrowscap = str_replace('_', '.', $matches[1]);
            }

            $platformBits = $platform->getBits();

            if (version_compare((float) $platformVersionBrowscap, 10.12, '>=')) {
                $platformNameBrowscap = 'macOS';
            } else {
                $platformNameBrowscap = 'MacOSX';
            }
        } elseif (false !== mb_stripos($useragent, 'kubuntu')) {
            $platform = $platformLoader->load('kubuntu', $useragent);

            $platformNameBrowscap        = 'Ubuntu';
            $platformMakerBrowscap       = 'Canonical Foundation';
            $platformDescriptionBrowscap = 'Ubuntu Linux';
        } elseif (false !== mb_stripos($useragent, 'ubuntu')) {
            $platform = $platformLoader->load('ubuntu', $useragent);

            $platformNameBrowscap        = 'Ubuntu';
            $platformMakerBrowscap       = 'Canonical Foundation';
            $platformDescriptionBrowscap = 'Ubuntu Linux';
        } elseif (false !== mb_stripos($useragent, 'android; linux arm')) {
            $platform = $platformLoader->load('android', $useragent);

            $platformNameBrowscap           = 'Android';
            $platformMakerBrowscap          = 'Google Inc';
            $platformDescriptionBrowscap    = 'Android OS';
        } elseif (preg_match('/(maemo|like android|linux\/x2\/r1|linux arm)/i', $useragent)) {
            $platform = $platformLoader->load('linux smartphone os (maemo)', $useragent);

            $platformNameBrowscap        = 'Ubuntu';
            $platformMakerBrowscap       = 'Canonical Foundation';
            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== mb_stripos($useragent, 'moblin')) {
            $platform = $platformLoader->load('moblin', $useragent);

            $platformNameBrowscap        = 'Linux';
            $platformMakerBrowscap       = 'Linux Foundation';
            $platformDescriptionBrowscap = 'Linux';
        } elseif (false !== mb_stripos($useragent, 'infegyatlas') || false !== mb_stripos($useragent, 'jobboerse')) {
            $platform = $platformLoader->load('unknown', $useragent);

            $platformNameBrowscap        = 'Ubuntu';
            $platformMakerBrowscap       = 'Canonical Foundation';
            $platformDescriptionBrowscap = 'Ubuntu';
        } elseif (preg_match('/linux arm/i', $useragent)) {
            $platform = $platformLoader->load('linux smartphone os (maemo)', $useragent);

            $platformNameBrowscap        = 'Ubuntu';
            $platformMakerBrowscap       = 'Canonical Foundation';
            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== mb_stripos($useragent, 'fedora')) {
            $platform = $platformLoader->load('fedora linux', $useragent);

            $platformNameBrowscap        = 'Linux';
            $platformMakerBrowscap       = 'Linux Foundation';
            $platformDescriptionBrowscap = 'Linux';
        } elseif (preg_match('/(red hat|redhat)/i', $useragent)) {
            $platform = $platformLoader->load('redhat linux', $useragent);

            $platformNameBrowscap        = 'Linux';
            $platformMakerBrowscap       = 'Linux Foundation';
            $platformDescriptionBrowscap = 'Linux';
        } elseif (false !== mb_stripos($useragent, 'suse')) {
            $platform = $platformLoader->load('suse linux', $useragent);

            $platformNameBrowscap        = 'Linux';
            $platformMakerBrowscap       = 'Linux Foundation';
            $platformDescriptionBrowscap = 'Linux';
        } elseif (false !== mb_stripos($useragent, 'centos')) {
            $platform = $platformLoader->load('cent os linux', $useragent);

            $platformNameBrowscap        = 'Linux';
            $platformMakerBrowscap       = 'Linux Foundation';
            $platformDescriptionBrowscap = 'Linux';
        } elseif (false !== mb_stripos($useragent, 'mandriva')) {
            $platform = $platformLoader->load('mandriva linux', $useragent);

            $platformNameBrowscap        = 'Linux';
            $platformMakerBrowscap       = 'Linux Foundation';
            $platformDescriptionBrowscap = 'Linux';
        } elseif (false !== mb_stripos($useragent, 'gentoo')) {
            $platform = $platformLoader->load('gentoo linux', $useragent);

            $platformNameBrowscap        = 'Linux';
            $platformMakerBrowscap       = 'Linux Foundation';
            $platformDescriptionBrowscap = 'Linux';
        } elseif (false !== mb_stripos($useragent, 'slackware')) {
            $platform = $platformLoader->load('slackware linux', $useragent);

            $platformNameBrowscap        = 'Linux';
            $platformMakerBrowscap       = 'Linux Foundation';
            $platformDescriptionBrowscap = 'Linux';
        } elseif (false !== mb_strpos($useragent, 'CrOS')) {
            $platform = $platformLoader->load('chromeos', $useragent);

            $platformNameBrowscap           = 'ChromeOS';
            $platformMakerBrowscap          = 'Google Inc';
            $platformDescriptionBrowscap    = 'unknown';
        } elseif (false !== mb_stripos($useragent, 'linux mint')) {
            $platform = $platformLoader->load('linux mint', $useragent);

            $platformNameBrowscap        = 'Linux';
            $platformMakerBrowscap       = 'Linux Foundation';
            $platformDescriptionBrowscap = 'Linux';
        } elseif (false !== mb_stripos($useragent, 'kubuntu')) {
            $platform = $platformLoader->load('kubuntu', $useragent);

            $platformNameBrowscap        = 'Ubuntu';
            $platformMakerBrowscap       = 'Canonical Foundation';
            $platformDescriptionBrowscap = 'Ubuntu Linux';
        } elseif (false !== mb_stripos($useragent, 'ubuntu')) {
            $platform = $platformLoader->load('ubuntu', $useragent);

            $platformNameBrowscap        = 'Ubuntu';
            $platformMakerBrowscap       = 'Canonical Foundation';
            $platformDescriptionBrowscap = 'Ubuntu Linux';
        } elseif (false !== mb_strpos($useragent, 'Debian APT-HTTP')) {
            $platform = $platformLoader->load('debian', $useragent);

            $platformNameBrowscap        = 'Linux';
            $platformMakerBrowscap       = 'Linux Foundation';
            $platformDescriptionBrowscap = 'Linux';
        } elseif (false !== mb_stripos($useragent, 'debian')) {
            $platform = $platformLoader->load('debian', $useragent);

            $platformNameBrowscap        = 'Linux';
            $platformMakerBrowscap       = 'Linux Foundation';
            $platformDescriptionBrowscap = 'Linux';
        } elseif (false !== mb_strpos($useragent, 'Linux')) {
            $platform = $platformLoader->load('linux', $useragent);

            $platformNameBrowscap        = 'Linux';
            $platformMakerBrowscap       = 'Linux Foundation';
            $platformDescriptionBrowscap = 'Linux';
        } elseif (false !== mb_strpos($useragent, 'SymbOS')) {
            $platform = $platformLoader->load('symbian', $useragent);

            $platformNameBrowscap        = 'SymbianOS';
            $platformMakerBrowscap       = 'Symbian Foundation';
            $platformDescriptionBrowscap = 'Symbian OS';
        } elseif (false !== mb_strpos($useragent, 'hpwOS')) {
            $platform = $platformLoader->load('webos', $useragent);

            $platformNameBrowscap        = 'webOS';
            $platformMakerBrowscap       = 'HP';
            $platformDescriptionBrowscap = 'webOS';
        } elseif (preg_match('/CFNetwork/', $useragent)) {
            $platform = (new Factory\Platform\DarwinFactory($cache, $platformLoader))->detect($useragent);

            $platformNameBrowscap        = 'Darwin';
            $platformMakerBrowscap       = 'Apple Inc';
            $platformDescriptionBrowscap = 'Darwin is a Core Component of MacOSX and iOS';
        } elseif (preg_match('/HP\-UX/', $useragent)) {
            $platform = $platformLoader->load('hp-ux', $useragent);

            $platformNameBrowscap        = 'HP-UX';
            $platformMakerBrowscap       = 'HP';
            $platformDescriptionBrowscap = 'HP-UX';
        } else {
            /* @var \UaResult\Result\Result $result */
            try {
                $result = $detector->getBrowser($useragent);

                $platform = $result->getOs();

                if ($platformCodenameDetector === $platform->getName()) {
                    $platformBits = $platform->getBits();
                } else {
                    $platform = null;
                }
            } catch (\Exception $e) {
                $platform = null;
            }
        }

        if (false !== mb_strpos($useragent, 'Silk') && false === mb_strpos($useragent, 'Android')) {
            $platformNameBrowscap      = 'Android';
            $platformMakerBrowscap     = 'Google Inc';

            $platformDescriptionBrowscap = 'Android OS';
        } elseif (false !== mb_strpos($useragent, 'Safari')
            && false !== mb_strpos($useragent, 'Version')
            && false !== mb_strpos($useragent, 'Android')
        ) {
            $platformDescriptionBrowscap = 'Android OS';
        }

        if (null === $platform) {
            $platform = new \UaResult\Os\Os(
                $platformCodenameDetector,
                $platformMarketingnameDetector
            );
        }

        return [
            $platformNameBrowscap,
            $platformMakerBrowscap,
            $platformDescriptionBrowscap,
            $platformVersionBrowscap,
            $win64,
            $win32,
            $win16,
            $standard,
            $platformBits,
            $platform,
        ];
    }
}
