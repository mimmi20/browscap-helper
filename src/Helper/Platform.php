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

use BrowserDetector\Bits\Os;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class Platform
{
    /**
     * @param string $ua
     *
     * @return array
     */
    public function detect($ua)
    {
        $platformNameBrowscap           = 'unknown';
        $platformCodenameDetector       = 'unknown';
        $platformMarketingnameDetector  = 'unknown';
        $platformVersionBrowscap        = 'unknown';
        $platformVersionDetector        = 'unknown';
        $platformMakerBrowscap          = 'unknown';
        $platformMakerNameDetector      = 'unknown';
        $platformMakerBrandnameDetector = 'unknown';
        $platformDescriptionBrowscap    = 'unknown';

        $platformBits = (new Os($ua))->getBits();

        $win16    = false;
        $win32    = false;
        $win64    = false;
        $standard = true;

        if (false !== strpos($ua, 'Windows Phone')) {
            $platformNameBrowscap           = 'WinPhone';
            $platformCodenameDetector       = 'Windows Phone OS';
            $platformMarketingnameDetector  = 'Windows Phone OS';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== strpos($ua, 'wds')) {
            $platformNameBrowscap           = 'Windows Phone OS';
            $platformCodenameDetector       = 'Windows Phone OS';
            $platformMarketingnameDetector  = 'Windows Phone OS';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if (preg_match('/wds (\d+\.\d+)/', $ua, $matches)) {
                $platformVersionBrowscap = $matches[1];
                $platformVersionDetector = $matches[1];
            }
        } elseif (false !== stripos($ua, 'wpdesktop')) {
            $platformNameBrowscap           = 'WinPhone';
            $platformCodenameDetector       = 'Windows Phone OS';
            $platformMarketingnameDetector  = 'Windows Phone OS';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== strpos($ua, 'Tizen')) {
            $platformNameBrowscap           = 'Tizen';
            $platformCodenameDetector       = 'Tizen';
            $platformMarketingnameDetector  = 'Tizen';
            $platformMakerBrowscap          = 'unknown';
            $platformMakerNameDetector      = 'unknown';
            $platformMakerBrandnameDetector = 'unknown';
            

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== strpos($ua, 'Windows CE')) {
            $platformNameBrowscap           = 'WinCE';
            $platformCodenameDetector       = 'Windows CE';
            $platformMarketingnameDetector  = 'Windows CE';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'Windows CE';
        } elseif (false !== strpos($ua, 'Linux; Android')) {
            $platformNameBrowscap           = 'Android';
            $platformCodenameDetector       = 'Android';
            $platformMarketingnameDetector  = 'Android';
            $platformMakerBrowscap          = 'Google Inc';
            $platformMakerNameDetector      = 'Google Inc';
            $platformMakerBrandnameDetector = 'Google';
            

            $platformDescriptionBrowscap = 'Android OS';

            if (preg_match('/Linux; Android (\d+\.\d+)/', $ua, $matches)) {
                $platformVersionBrowscap = $matches[1];
                $platformVersionDetector = $matches[1];
            }
        } elseif (false !== strpos($ua, 'Linux; U; Android')) {
            $platformNameBrowscap           = 'Android';
            $platformCodenameDetector       = 'Android';
            $platformMarketingnameDetector  = 'Android';
            $platformMakerBrowscap          = 'Google Inc';
            $platformMakerNameDetector      = 'Google Inc';
            $platformMakerBrandnameDetector = 'Google';
            

            $platformDescriptionBrowscap = 'Android OS';

            if (preg_match('/Linux; U; Android (\d+\.\d+)/', $ua, $matches)) {
                $platformVersionBrowscap = $matches[1];
                $platformVersionDetector = $matches[1];
            }
        } elseif (false !== strpos($ua, 'U; Adr')) {
            $platformNameBrowscap           = 'Android';
            $platformCodenameDetector       = 'Android';
            $platformMarketingnameDetector  = 'Android';
            $platformMakerBrowscap          = 'Google Inc';
            $platformMakerNameDetector      = 'Google Inc';
            $platformMakerBrandnameDetector = 'Google';
            

            $platformDescriptionBrowscap = 'Android OS';

            if (preg_match('/U; Adr (\d+\.\d+)/', $ua, $matches)) {
                $platformVersionBrowscap = $matches[1];
                $platformVersionDetector = $matches[1];
            }
        } elseif (false !== strpos($ua, 'Android') || false !== strpos($ua, 'MTK')) {
            $platformNameBrowscap           = 'Android';
            $platformCodenameDetector       = 'Android';
            $platformMarketingnameDetector  = 'Android';
            $platformMakerBrowscap          = 'Google Inc';
            $platformMakerNameDetector      = 'Google Inc';
            $platformMakerBrandnameDetector = 'Google';
            

            $platformDescriptionBrowscap = 'Android OS';
        } elseif (false !== strpos($ua, 'OpenBSD')) {
            $platformNameBrowscap          = 'OpenBSD';
            $platformCodenameDetector      = 'OpenBSD';
            $platformMarketingnameDetector = 'OpenBSD';

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== strpos($ua, 'Symbian') || false !== strpos($ua, 'Series 60')) {
            $platformNameBrowscap           = 'SymbianOS';
            $platformCodenameDetector       = 'Symbian OS';
            $platformMarketingnameDetector  = 'Symbian OS';
            $platformMakerBrowscap          = 'Symbian Foundation';
            $platformMakerNameDetector      = 'Symbian Foundation';
            $platformMakerBrandnameDetector = 'Symbian';
            

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== strpos($ua, 'MIDP')) {
            $platformNameBrowscap           = 'JAVA';
            $platformCodenameDetector       = 'Java';
            $platformMarketingnameDetector  = 'Java';
            $platformMakerBrowscap          = 'Oracle';
            $platformMakerNameDetector      = 'Oracle';
            $platformMakerBrandnameDetector = 'Oracle';
            

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== strpos($ua, 'Windows NT 10.0')) {
            $platformNameBrowscap           = 'Win10';
            $platformCodenameDetector       = 'Windows NT 10.0';
            $platformMarketingnameDetector  = 'Windows 10';
            $platformVersionBrowscap        = '10.0';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== strpos($ua, 'Windows NT 6.4')) {
            $platformNameBrowscap           = 'Win10';
            $platformCodenameDetector       = 'Windows NT 6.4';
            $platformMarketingnameDetector  = 'Windows 10';
            $platformVersionBrowscap        = '6.4';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== strpos($ua, 'Windows NT 6.3') && false !== strpos($ua, 'ARM')) {
            $platformNameBrowscap           = 'Win8.1';
            $platformCodenameDetector       = 'Windows RT 8.1';
            $platformMarketingnameDetector  = 'Windows RT 8.1';
            $platformVersionBrowscap        = '6.3';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== strpos($ua, 'Windows NT 6.3')) {
            $platformNameBrowscap           = 'Win8.1';
            $platformCodenameDetector       = 'Windows NT 6.3';
            $platformMarketingnameDetector  = 'Windows 8.1';
            $platformVersionBrowscap        = '6.3';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== strpos($ua, 'Windows NT 6.2') && false !== strpos($ua, 'ARM')) {
            $platformNameBrowscap           = 'Win8';
            $platformCodenameDetector       = 'Windows RT 8';
            $platformMarketingnameDetector  = 'Windows RT 8';
            $platformVersionBrowscap        = '6.2';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== strpos($ua, 'Windows NT 6.2')) {
            $platformNameBrowscap           = 'Win8';
            $platformCodenameDetector       = 'Windows NT 6.2';
            $platformMarketingnameDetector  = 'Windows 8';
            $platformVersionBrowscap        = '6.2';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== strpos($ua, 'Windows NT 6.1')) {
            $platformNameBrowscap           = 'Win7';
            $platformCodenameDetector       = 'Windows NT 6.1';
            $platformMarketingnameDetector  = 'Windows 7';
            $platformVersionBrowscap        = '6.1';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== strpos($ua, 'Windows NT 6.0')) {
            $platformNameBrowscap           = 'WinVista';
            $platformCodenameDetector       = 'Windows NT 6.0';
            $platformMarketingnameDetector  = 'Windows Vista';
            $platformVersionBrowscap        = '6.0';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== strpos($ua, 'Windows NT 5.3')) {
            $platformNameBrowscap           = 'WinXP';
            $platformCodenameDetector       = 'Windows NT 5.3';
            $platformMarketingnameDetector  = 'Windows XP';
            $platformVersionBrowscap        = '5.3';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== strpos($ua, 'Windows NT 5.2')) {
            $platformNameBrowscap           = 'WinXP';
            $platformCodenameDetector       = 'Windows NT 5.2';
            $platformMarketingnameDetector  = 'Windows XP';
            $platformVersionBrowscap        = '5.2';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== strpos($ua, 'Windows NT 5.1')) {
            $platformNameBrowscap           = 'WinXP';
            $platformCodenameDetector       = 'Windows NT 5.1';
            $platformMarketingnameDetector  = 'Windows XP';
            $platformVersionBrowscap        = '5.1';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }
        } elseif (false !== strpos($ua, 'Windows NT 5.01')) {
            $platformNameBrowscap           = 'Win2000';
            $platformCodenameDetector       = 'Windows NT 5.01';
            $platformMarketingnameDetector  = 'Windows 2000';
            $platformVersionBrowscap        = '5.0';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== strpos($ua, 'Windows NT 5.0')) {
            $platformNameBrowscap           = 'Win2000';
            $platformCodenameDetector       = 'Windows NT 5.0';
            $platformMarketingnameDetector  = 'Windows 2000';
            $platformVersionBrowscap        = '5.0';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== strpos($ua, 'Windows NT 4.1')) {
            $platformNameBrowscap           = 'WinNT';
            $platformCodenameDetector       = 'Windows NT 4.1';
            $platformMarketingnameDetector  = 'Windows NT';
            $platformVersionBrowscap        = '4.1';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== strpos($ua, 'Windows NT 4.0')) {
            $platformNameBrowscap           = 'WinNT';
            $platformCodenameDetector       = 'Windows NT 4.0';
            $platformMarketingnameDetector  = 'Windows NT';
            $platformVersionBrowscap        = '4.0';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== strpos($ua, 'Windows NT 3.5')) {
            $platformNameBrowscap           = 'WinNT';
            $platformCodenameDetector       = 'Windows NT 3.5';
            $platformMarketingnameDetector  = 'Windows NT';
            $platformVersionBrowscap        = '3.5';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== strpos($ua, 'Windows NT 3.1')) {
            $platformNameBrowscap           = 'WinNT';
            $platformCodenameDetector       = 'Windows NT 3.1';
            $platformMarketingnameDetector  = 'Windows NT';
            $platformVersionBrowscap        = '3.1';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== strpos($ua, 'Windows NT')) {
            $platformNameBrowscap           = 'WinNT';
            $platformCodenameDetector       = 'Windows NT';
            $platformMarketingnameDetector  = 'Windows NT';
            $platformVersionBrowscap        = 'unknown';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== stripos($ua, 'cygwin')) {
            $platformNameBrowscap           = 'Cygwin';
            $platformCodenameDetector       = 'Cygwin';
            $platformMarketingnameDetector  = 'Cygwin';
            $platformVersionBrowscap        = 'unknown';
            $platformVersionDetector        = '0.0.0';
            $platformMakerBrowscap          = 'Microsoft Corporation';
            $platformMakerNameDetector      = 'Microsoft Corporation';
            $platformMakerBrandnameDetector = 'Microsoft';
            

            $platformDescriptionBrowscap = 'unknown';

            if ($platformBits === 64) {
                $win64 = true;
            } elseif ($platformBits === 32) {
                $win32 = true;
            } elseif ($platformBits === 16) {
                $win16 = true;
            }

            $standard = false;
        } elseif (false !== strpos($ua, 'CPU OS')) {
            $platformNameBrowscap           = 'iOS';
            $platformCodenameDetector       = 'iOS';
            $platformMarketingnameDetector  = 'iOS';
            $platformMakerBrowscap          = 'Apple Inc';
            $platformMakerNameDetector      = 'Apple Inc';
            $platformMakerBrandnameDetector = 'Apple';
            

            $platformDescriptionBrowscap = 'iPod, iPhone & iPad';

            if (preg_match('/CPU OS (\d+\_\d+)/', $ua, $matches)) {
                $platformVersionBrowscap = str_replace('_', '.', $matches[1]);
                $platformVersionDetector = str_replace('_', '.', $matches[1]);
            }
        } elseif (false !== strpos($ua, 'CPU iPhone OS')) {
            $platformNameBrowscap           = 'iOS';
            $platformCodenameDetector       = 'iOS';
            $platformMarketingnameDetector  = 'iOS';
            $platformMakerBrowscap          = 'Apple Inc';
            $platformMakerNameDetector      = 'Apple Inc';
            $platformMakerBrandnameDetector = 'Apple';
            

            $platformDescriptionBrowscap = 'iPod, iPhone & iPad';

            if (preg_match('/CPU iPhone OS (\d+\_\d+)/', $ua, $matches)) {
                $platformVersionBrowscap = str_replace('_', '.', $matches[1]);
                $platformVersionDetector = str_replace('_', '.', $matches[1]);
            }
        } elseif (false !== strpos($ua, 'CPU like Mac OS X')) {
            $platformNameBrowscap           = 'iOS';
            $platformCodenameDetector       = 'iOS';
            $platformMarketingnameDetector  = 'iOS';
            $platformMakerBrowscap          = 'Apple Inc';
            $platformMakerNameDetector      = 'Apple Inc';
            $platformMakerBrandnameDetector = 'Apple';

            $platformDescriptionBrowscap = 'iPod, iPhone & iPad';

            if (preg_match('/CPU like Mac OS X (\d+\_\d+)/', $ua, $matches)) {
                $platformVersionBrowscap = str_replace('_', '.', $matches[1]);
                $platformVersionDetector = str_replace('_', '.', $matches[1]);
            }
        } elseif (false !== strpos($ua, 'iOS')) {
            $platformNameBrowscap           = 'iOS';
            $platformCodenameDetector       = 'iOS';
            $platformMarketingnameDetector  = 'iOS';
            $platformMakerBrowscap          = 'Apple Inc';
            $platformMakerNameDetector      = 'Apple Inc';
            $platformMakerBrandnameDetector = 'Apple';

            $platformDescriptionBrowscap = 'iPod, iPhone & iPad';
        } elseif (false !== strpos($ua, 'Mac OS X')) {
            $platformMakerBrowscap          = 'Apple Inc';
            $platformMakerNameDetector      = 'Apple Inc';
            $platformMakerBrandnameDetector = 'Apple';

            $platformDescriptionBrowscap = 'Mac OS X';

            if (preg_match('/Mac OS X (\d+[\_\.]\d+)/', $ua, $matches)) {
                $platformVersionBrowscap = str_replace('_', '.', $matches[1]);
                $platformVersionDetector = str_replace('_', '.', $matches[1]);
            }

            if (version_compare((float) $platformVersionBrowscap, 10.12, '>=')) {
                $platformNameBrowscap = 'macOS';
            } else {
                $platformNameBrowscap = 'MacOSX';
            }

            if (version_compare((float) $platformVersionDetector, 10.12, '>=')) {
                $platformCodenameDetector      = 'macOS';
                $platformMarketingnameDetector = 'macOS';
            } else {
                $platformCodenameDetector      = 'Mac OS X';
                $platformMarketingnameDetector = 'Mac OS X';
            }
        } elseif (false !== stripos($ua, 'kubuntu')) {
            $platformNameBrowscap           = 'Ubuntu';
            $platformCodenameDetector       = 'Kubuntu';
            $platformMarketingnameDetector  = 'Kubuntu';
            $platformMakerBrowscap          = 'Canonical Foundation';
            $platformMakerNameDetector      = 'Canonical Foundation';
            $platformMakerBrandnameDetector = 'Canonical';

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== stripos($ua, 'ubuntu')) {
            $platformNameBrowscap           = 'Ubuntu';
            $platformCodenameDetector       = 'Ubuntu';
            $platformMarketingnameDetector  = 'Ubuntu';
            $platformMakerBrowscap          = 'Canonical Foundation';
            $platformMakerNameDetector      = 'Canonical Foundation';
            $platformMakerBrandnameDetector = 'Canonical';

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== stripos($ua, 'fedora')) {
            $platformNameBrowscap           = 'Linux';
            $platformCodenameDetector       = 'Fedora Linux';
            $platformMarketingnameDetector  = 'Fedora Linux';
            $platformMakerBrowscap          = 'Linux Foundation';
            $platformMakerNameDetector      = 'Red Hat Inc';
            $platformMakerBrandnameDetector = 'Red Hat';

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== stripos($ua, 'suse')) {
            $platformNameBrowscap           = 'Linux';
            $platformCodenameDetector       = 'Suse Linux';
            $platformMarketingnameDetector  = 'Suse Linux';
            $platformMakerBrowscap          = 'Linux Foundation';
            $platformMakerNameDetector      = 'Suse';
            $platformMakerBrandnameDetector = 'Suse';

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== stripos($ua, 'mandriva')) {
            $platformNameBrowscap           = 'Linux';
            $platformCodenameDetector       = 'Mandriva Linux';
            $platformMarketingnameDetector  = 'Mandriva Linux';
            $platformMakerBrowscap          = 'Linux Foundation';
            $platformMakerNameDetector      = 'Mandriva';
            $platformMakerBrandnameDetector = 'Mandriva';
            

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== stripos($ua, 'gentoo')) {
            $platformNameBrowscap           = 'Linux';
            $platformCodenameDetector       = 'Gentoo Linux';
            $platformMarketingnameDetector  = 'Gentoo Linux';
            $platformMakerBrowscap          = 'Linux Foundation';
            $platformMakerNameDetector      = 'Gentoo Foundation Inc';
            $platformMakerBrandnameDetector = 'Gentoo';

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== stripos($ua, 'slackware')) {
            $platformNameBrowscap           = 'Linux';
            $platformCodenameDetector       = 'Slackware Linux';
            $platformMarketingnameDetector  = 'Slackware Linux';
            $platformMakerBrowscap          = 'Linux Foundation';
            $platformMakerNameDetector      = 'Slackware Linux Inc';
            $platformMakerBrandnameDetector = 'Slackware';

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== strpos($ua, 'CrOS')) {
            $platformNameBrowscap           = 'ChromeOS';
            $platformCodenameDetector       = 'ChromeOS';
            $platformMarketingnameDetector  = 'ChromeOS';
            $platformMakerBrowscap          = 'Google Inc';
            $platformMakerNameDetector      = 'Google Inc';
            $platformMakerBrandnameDetector = 'Google';

            $platformDescriptionBrowscap = 'unknown';
        } elseif (false !== strpos($ua, 'Linux')) {
            $platformNameBrowscap           = 'Linux';
            $platformCodenameDetector       = 'Linux';
            $platformMarketingnameDetector  = 'Linux';
            $platformMakerBrowscap          = 'Linux Foundation';
            $platformMakerNameDetector      = 'Linux Foundation';
            $platformMakerBrandnameDetector = 'Linux Foundation';

            $platformDescriptionBrowscap = 'Linux';
        } elseif (false !== strpos($ua, 'SymbOS')) {
            $platformNameBrowscap           = 'SymbianOS';
            $platformCodenameDetector       = 'Symbian OS';
            $platformMarketingnameDetector  = 'Symbian OS';
            $platformMakerBrowscap          = 'Symbian Foundation';
            $platformMakerNameDetector      = 'Symbian Foundation';
            $platformMakerBrandnameDetector = 'Symbian';

            $platformDescriptionBrowscap = 'Symbian OS';
        } elseif (false !== strpos($ua, 'hpwOS')) {
            $platformNameBrowscap           = 'webOS';
            $platformCodenameDetector       = 'webOS';
            $platformMarketingnameDetector  = 'webOS';
            $platformMakerBrowscap          = 'HP';
            $platformMakerNameDetector      = 'HP';
            $platformMakerBrandnameDetector = 'HP';

            $platformDescriptionBrowscap = 'webOS';
        }

        if (false !== strpos($ua, 'Silk') && false === strpos($ua, 'Android')) {
            $platformNameBrowscap      = 'Android';
            $platformCodenameDetector  = 'Android';
            $platformMakerBrowscap     = 'Google Inc';
            $platformMakerNameDetector = 'Google Inc';

            $platformDescriptionBrowscap = 'Android OS';
        } elseif (false !== strpos($ua, 'Safari')
            && false !== strpos($ua, 'Version')
            && false !== strpos($ua, 'Android')
        ) {
            $platformDescriptionBrowscap = 'Android OS';
        }

        $platformBits = (new Os($ua))->getBits();
        
        return [
            $platformNameBrowscap,
            $platformMakerBrowscap,
            $platformDescriptionBrowscap,
            $platformVersionBrowscap,
            $win64,
            $win32,
            $win16,
            $platformCodenameDetector,
            $platformMarketingnameDetector,
            $platformMakerNameDetector,
            $platformMakerBrandnameDetector,
            $platformVersionDetector,
            $standard,
            $platformBits
        ];
    }
}
