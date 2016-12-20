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

use BrowserDetector\Bits;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class Browser
{
    /**
     * @param string $ua
     *
     * @return array
     */
    public function detect($ua)
    {
        $browserNameBrowscap = 'Default Browser';
        $browserNameDetector = 'Default Browser';
        $browserType         = 'unknown';
        $browserMaker        = 'unknown';
        $browserVersion      = '0.0';

        $crawler      = false;
        $lite         = true;
        $browserModus = null;

        $chromeVersion = 0;

        if (false !== strpos($ua, 'Chrome')) {
            if (preg_match('/Chrome\/(\d+\.\d+)/', $ua, $matches)) {
                $chromeVersion = (float) $matches[1];
            }
        }

        $browserBits  = (new Bits\Browser($ua))->getBits();

        if (false !== strpos($ua, 'OPR') && false !== strpos($ua, 'Android')) {
            $browserNameBrowscap = 'Opera Mobile';
            $browserNameDetector = 'Opera Mobile';
            $browserType         = 'Browser';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/OPR\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Opera Mobi')) {
            $browserNameBrowscap = 'Opera Mobile';
            $browserNameDetector = 'Opera Mobile';
            $browserType         = 'Browser';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'OPR')) {
            $browserNameBrowscap = 'Opera';
            $browserNameDetector = 'Opera';
            $browserType         = 'Browser';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/OPR\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($ua, 'Opera')) {
            $browserNameBrowscap = 'Opera';
            $browserNameDetector = 'Opera';
            $browserType         = 'Browser';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            } elseif (preg_match('/Opera\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($ua, 'Coast')) {
            $browserNameBrowscap = 'Coast';
            $browserNameDetector = 'Coast';
            $browserType         = 'Application';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/Coast\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($ua, 'Mercury')) {
            $browserNameBrowscap = 'Mercury';
            $browserNameDetector = 'Mercury';
            $browserType         = 'Browser';
            $browserMaker        = 'iLegendSoft, Inc.';

            if (preg_match('/Mercury\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($ua, 'CommonCrawler Node')) {
            $browserNameBrowscap = 'CommonCrawler Node';
            $browserNameDetector = 'CommonCrawler Node';
            $browserType         = 'Bot/Crawler';
            $crawler             = true;
        } elseif (false !== strpos($ua, 'UCBrowser') || false !== strpos($ua, 'UC Browser')) {
            $browserNameBrowscap = 'UC Browser';
            $browserNameDetector = 'UC Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'UC Web';

            if (preg_match('/UCBrowser\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            } elseif (preg_match('/UC Browser(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'iCab')) {
            $browserNameBrowscap = 'iCab';
            $browserNameDetector = 'iCab';
            $browserType         = 'Browser';
            $browserMaker        = 'Alexander Clauss';

            if (preg_match('/iCab\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Lunascape')) {
            $browserNameBrowscap = 'Lunascape';
            $browserNameDetector = 'Lunascape';
            $browserType         = 'Browser';
            //$browserMaker = 'Alexander Clauss';

            if (preg_match('/Lunascape (\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== stripos($ua, 'midori')) {
            $browserNameBrowscap = 'Midori';
            $browserNameDetector = 'Midori';
            $browserType         = 'Browser';
            //$browserMaker = 'Alexander Clauss';

            if (preg_match('/Midori\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'OmniWeb')) {
            $browserNameBrowscap = 'OmniWeb';
            $browserNameDetector = 'Omniweb';
            $browserType         = 'Browser';
            //$browserMaker = 'Alexander Clauss';

            if (preg_match('/OmniWeb\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== stripos($ua, 'maxthon') || false !== strpos($ua, 'MyIE2')) {
            $browserNameBrowscap = 'Maxthon';
            $browserNameDetector = 'Maxthon';
            $browserType         = 'Browser';
            //$browserMaker = 'Alexander Clauss';

            if (preg_match('/maxthon (\d+\.\d+)/i', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'PhantomJS')) {
            $browserNameBrowscap = 'PhantomJS';
            $browserNameDetector = 'PhantomJS';
            $browserType         = 'Browser';
            $browserMaker        = 'phantomjs.org';

            if (preg_match('/PhantomJS\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'YaBrowser')) {
            $browserNameBrowscap = 'Yandex Browser';
            $browserNameDetector = 'Yandex Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Yandex';

            if (preg_match('/YaBrowser\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Kamelio')) {
            $browserNameBrowscap = 'Kamelio App';
            $browserNameDetector = 'Kamelio App';
            $browserType         = 'Application';
            $browserMaker        = 'Kamelio';

            $lite = false;
        } elseif (false !== strpos($ua, 'FBAV')) {
            $browserNameBrowscap = 'Facebook App';
            $browserNameDetector = 'Facebook App';
            $browserType         = 'Application';
            $browserMaker        = 'Facebook';

            if (preg_match('/FBAV\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'ACHEETAHI')) {
            $browserNameBrowscap = 'CM Browser';
            $browserNameDetector = 'CM Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Cheetah Mobile';

            $lite = false;
        } elseif (false !== strpos($ua, 'bdbrowser_i18n')) {
            $browserNameBrowscap = 'Baidu Browser';
            $browserNameDetector = 'Baidu Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Baidu';

            if (preg_match('/bdbrowser\_i18n\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'bdbrowserhd_i18n')) {
            $browserNameBrowscap = 'Baidu Browser HD';
            $browserNameDetector = 'Baidu Browser HD';
            $browserType         = 'Browser';
            $browserMaker        = 'Baidu';

            if (preg_match('/bdbrowserhd\_i18n\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'bdbrowser_mini')) {
            $browserNameBrowscap = 'Baidu Browser Mini';
            $browserNameDetector = 'Baidu Browser Mini';
            $browserType         = 'Browser';
            $browserMaker        = 'Baidu';

            if (preg_match('/bdbrowser\_mini\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Puffin')) {
            $browserNameBrowscap = 'Puffin';
            $browserNameDetector = 'Puffin';
            $browserType         = 'Browser';
            $browserMaker        = 'CloudMosa Inc.';

            if (preg_match('/Puffin\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'SamsungBrowser')) {
            $browserNameBrowscap = 'Samsung Browser';
            $browserNameDetector = 'Samsung Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Samsung';

            if (preg_match('/SamsungBrowser\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Silk')) {
            $browserNameBrowscap = 'Silk';
            $browserNameDetector = 'Silk';
            $browserType         = 'Browser';
            $browserMaker        = 'Amazon.com, Inc.';

            if (preg_match('/Silk\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;

            if (false === strpos($ua, 'Android')) {
                $browserModus = 'Desktop Mode';
            }
        } elseif (false !== strpos($ua, 'coc_coc_browser')) {
            $browserNameBrowscap = 'Coc Coc Browser';
            $browserNameDetector = 'Coc Coc Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Coc Coc Company Limited';

            if (preg_match('/coc_coc_browser\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'NaverMatome')) {
            $browserNameBrowscap = 'NaverMatome';
            $browserNameDetector = 'NaverMatome';
            $browserType         = 'Application';
            $browserMaker        = 'Naver';

            if (preg_match('/NaverMatome\-Android\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Flipboard')) {
            $browserNameBrowscap = 'Flipboard App';
            $browserNameDetector = 'Flipboard App';
            $browserType         = 'Application';
            $browserMaker        = 'Flipboard, Inc.';

            if (preg_match('/Flipboard\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Arora')) {
            $browserNameBrowscap = 'Arora';
            $browserNameDetector = 'Arora';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/Arora\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Acoo Browser')) {
            $browserNameBrowscap = 'Acoo Browser';
            $browserNameDetector = 'Acoo Browser';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/Acoo Browser\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'ABrowse')) {
            $browserNameBrowscap = 'ABrowse';
            $browserNameDetector = 'ABrowse';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/ABrowse\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'AmigaVoyager')) {
            $browserNameBrowscap = 'AmigaVoyager';
            $browserNameDetector = 'AmigaVoyager';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/AmigaVoyager\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Beonex')) {
            $browserNameBrowscap = 'Beonex';
            $browserNameDetector = 'Beonex';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/Beonex\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Stainless')) {
            $browserNameBrowscap = 'Stainless';
            $browserNameDetector = 'Stainless';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/Stainless\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Sundance')) {
            $browserNameBrowscap = 'Sundance';
            $browserNameDetector = 'Sundance';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/Sundance\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Sunrise')) {
            $browserNameBrowscap = 'Sunrise';
            $browserNameDetector = 'Sunrise';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/Sunrise\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'SunriseBrowser')) {
            $browserNameBrowscap = 'Sunrise';
            $browserNameDetector = 'Sunrise';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/SunriseBrowser\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Seznam.cz')) {
            $browserNameBrowscap = 'Seznam Browser';
            $browserNameDetector = 'Seznam Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Seznam.cz, a.s.';

            if (preg_match('/Seznam\.cz\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Aviator')) {
            $browserNameBrowscap = 'WhiteHat Aviator';
            $browserNameDetector = 'WhiteHat Aviator';
            $browserType         = 'Browser';
            $browserMaker        = 'WhiteHat Security';

            if (preg_match('/Aviator\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Dragon')) {
            $browserNameBrowscap = 'Dragon';
            $browserNameDetector = 'Dragon';
            $browserType         = 'Browser';
            $browserMaker        = 'Comodo Group Inc';

            if (preg_match('/Dragon\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Beamrise')) {
            $browserNameBrowscap = 'Beamrise';
            $browserNameDetector = 'Beamrise';
            $browserType         = 'Browser';
            $browserMaker        = 'Beamrise Team';

            if (preg_match('/Beamrise\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Diglo')) {
            $browserNameBrowscap = 'Diglo';
            $browserNameDetector = 'Diglo';
            $browserType         = 'Browser';
            $browserMaker        = 'Diglo Inc';

            if (preg_match('/Diglo\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'APUSBrowser')) {
            $browserNameBrowscap = 'APUSBrowser';
            $browserNameDetector = 'APUSBrowser';
            $browserType         = 'Browser';
            $browserMaker        = 'APUS-Group';

            if (preg_match('/APUSBrowser\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Chedot')) {
            $browserNameBrowscap = 'Chedot';
            $browserNameDetector = 'Chedot';
            $browserType         = 'Browser';
            $browserMaker        = 'Chedot.com';

            if (preg_match('/Chedot\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Qword')) {
            $browserNameBrowscap = 'Qword Browser';
            $browserNameDetector = 'Qword Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Qword Corporation';

            if (preg_match('/Qword\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Iridium')) {
            $browserNameBrowscap = 'Iridium Browser';
            $browserNameDetector = 'Iridium Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Iridium Browser Team';

            if (preg_match('/Iridium\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'MxNitro')) {
            $browserNameBrowscap = 'Maxthon Nitro';
            $browserNameDetector = 'Maxthon Nitro';
            $browserType         = 'Browser';
            $browserMaker        = 'Maxthon International Limited';

            if (preg_match('/MxNitro\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'MxBrowser')) {
            $browserNameBrowscap = 'Maxthon';
            $browserNameDetector = 'Maxthon';
            $browserType         = 'Browser';
            $browserMaker        = 'Maxthon International Limited';

            if (preg_match('/MxBrowser\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Maxthon')) {
            $browserNameBrowscap = 'Maxthon';
            $browserNameDetector = 'Maxthon';
            $browserType         = 'Browser';
            $browserMaker        = 'Maxthon International Limited';

            if (preg_match('/Maxthon\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Superbird') || false !== strpos($ua, 'SuperBird')) {
            $browserNameBrowscap = 'SuperBird';
            $browserNameDetector = 'SuperBird';
            $browserType         = 'Browser';
            $browserMaker        = 'superbird-browser.com';

            if (preg_match('/superbird\/(\d+\.\d+)/i', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'TinyBrowser')) {
            $browserNameBrowscap = 'TinyBrowser';
            $browserNameDetector = 'TinyBrowser';
            $browserType         = 'Browser';
            $browserMaker        = 'unknown';

            if (preg_match('/TinyBrowser\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Chrome') && false !== strpos($ua, 'Version')) {
            $browserNameBrowscap = 'Android WebView';
            $browserNameDetector = 'Android WebView';
            $browserType         = 'Browser';
            $browserMaker        = 'Google Inc';

            if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion <= 1) {
                $lite = false;
            }
        } elseif (false !== strpos($ua, 'Safari') && false !== strpos($ua, 'Version') && false !== strpos(
            $ua,
            'Tizen'
        )
        ) {
            $browserNameBrowscap = 'Samsung WebView';
            $browserNameDetector = 'Samsung WebView';
            $browserType         = 'Browser';
            $browserMaker        = 'Samsung';

            if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Chromium')) {
            $browserNameBrowscap = 'Chromium';
            $browserNameDetector = 'Chromium';
            $browserType         = 'Browser';
            $browserMaker        = 'Google Inc';

            if (preg_match('/Chromium\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Flock')) {
            $browserNameBrowscap = 'Flock';
            $browserNameDetector = 'Flock';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Flock\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Fluid')) {
            $browserNameBrowscap = 'Fluid';
            $browserNameDetector = 'Fluid';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Fluid\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'ChromePlus')) {
            $browserNameBrowscap = 'ChromePlus';
            $browserNameDetector = 'ChromePlus';
            $browserType         = 'Browser';
            //$browserMaker = 'Google Inc';

            if (preg_match('/ChromePlus\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'RockMelt')) {
            $browserNameBrowscap = 'RockMelt';
            $browserNameDetector = 'RockMelt';
            $browserType         = 'Browser';
            //$browserMaker = 'Google Inc';

            if (preg_match('/RockMelt\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Shiira')) {
            $browserNameBrowscap = 'Shiira';
            $browserNameDetector = 'Shiira';
            $browserType         = 'Browser';
            //$browserMaker = 'Google Inc';

            if (preg_match('/Shiira\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Iron')) {
            $browserNameBrowscap = 'Iron';
            $browserNameDetector = 'Iron';
            $browserType         = 'Browser';
            //$browserMaker = 'Google Inc';

            if (preg_match('/Iron\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Chrome')) {
            $browserNameBrowscap = 'Chrome';
            $browserNameDetector = 'Chrome';
            $browserType         = 'Browser';
            $browserMaker        = 'Google Inc';
            $browserVersion      = (string) $chromeVersion;

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($ua, 'CriOS')) {
            $browserNameBrowscap = 'Chrome';
            $browserNameDetector = 'Chrome';
            $browserType         = 'Browser';
            $browserMaker        = 'Google Inc';

            if (preg_match('/CriOS\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($ua, 'OPiOS')) {
            $browserNameBrowscap = 'Opera Mini';
            $browserNameDetector = 'Opera Mini';
            $browserType         = 'Browser';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/OPiOS\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($ua, 'Opera Mini')) {
            $browserNameBrowscap = 'Opera Mini';
            $browserNameDetector = 'Opera Mini';
            $browserType         = 'Browser';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/Opera Mini\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($ua, 'FlyFlow')) {
            $browserNameBrowscap = 'FlyFlow';
            $browserNameDetector = 'FlyFlow';
            $browserType         = 'Browser';
            $browserMaker        = 'Baidu';

            if (preg_match('/FlyFlow\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Epiphany') || false !== strpos($ua, 'epiphany')) {
            $browserNameBrowscap = 'Epiphany';
            $browserNameDetector = 'Epiphany';
            $browserType         = 'Browser';
            //$browserMaker = 'Baidu';

            if (preg_match('/Epiphany\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'GSA')) {
            $browserNameBrowscap = 'Google App';
            $browserNameDetector = 'Google App';
            $browserType         = 'Application';
            $browserMaker        = 'Google Inc';

            if (preg_match('/GSA\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Safari')
            && false !== strpos($ua, 'Version')
            && false !== strpos($ua, 'Android')
        ) {
            $browserNameBrowscap = 'Android';
            $browserNameDetector = 'Android';
            $browserType         = 'Browser';
            $browserMaker        = 'Google Inc';

            if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion !== '4.0') {
                $lite = false;
            }
        } elseif (false !== strpos($ua, 'BlackBerry') && false !== strpos($ua, 'Version')) {
            $browserNameBrowscap = 'BlackBerry';
            $browserNameDetector = 'BlackBerry';
            $browserType         = 'Browser';
            $browserMaker        = 'Research In Motion Limited';

            if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }
            $lite = false;
        } elseif (false !== strpos($ua, 'Safari') && false !== strpos($ua, 'Version')) {
            $browserNameBrowscap = 'Safari';
            $browserNameDetector = 'Safari';
            $browserType         = 'Browser';
            $browserMaker        = 'Apple Inc';

            if (preg_match('/Version\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($ua, 'PaleMoon')) {
            $browserNameBrowscap = 'PaleMoon';
            $browserNameDetector = 'PaleMoon';
            $browserType         = 'Browser';
            $browserMaker        = 'Moonchild Productions';

            if (preg_match('/PaleMoon\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Phoenix')) {
            $browserNameBrowscap = 'Phoenix';
            $browserNameDetector = 'Phoenix';
            $browserType         = 'Browser';
            //$browserMaker = 'www.waterfoxproject.org';

            if (preg_match('/Phoenix\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== stripos($ua, 'Prism')) {
            $browserNameBrowscap = 'Prism';
            $browserNameDetector = 'Prism';
            $browserType         = 'Browser';
            //$browserMaker = 'www.waterfoxproject.org';

            if (preg_match('/Prism\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== stripos($ua, 'QtWeb Internet Browser')) {
            $browserNameBrowscap = 'QtWeb Internet Browser';
            $browserNameDetector = 'QtWeb Internet Browser';
            $browserType         = 'Browser';
            //$browserMaker = 'www.waterfoxproject.org';

            if (preg_match('/QtWeb Internet Browser\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Waterfox')) {
            $browserNameBrowscap = 'Waterfox';
            $browserNameDetector = 'Waterfox';
            $browserType         = 'Browser';
            $browserMaker        = 'www.waterfoxproject.org';

            if (preg_match('/Waterfox\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'QupZilla')) {
            $browserNameBrowscap = 'QupZilla';
            $browserNameDetector = 'QupZilla';
            $browserType         = 'Browser';
            $browserMaker        = 'David Rosca and Community';

            if (preg_match('/QupZilla\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Thunderbird')) {
            $browserNameBrowscap = 'Thunderbird';
            $browserNameDetector = 'Thunderbird';
            $browserType         = 'Email Client';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Thunderbird\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'kontact')) {
            $browserNameBrowscap = 'Kontact';
            $browserNameDetector = 'Kontact';
            $browserType         = 'Email Client';
            $browserMaker        = 'KDE e.V.';

            if (preg_match('/kontact\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Fennec')) {
            $browserNameBrowscap = 'Fennec';
            $browserNameDetector = 'Fennec';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Fennec\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'myibrow')) {
            $browserNameBrowscap = 'My Internet Browser';
            $browserNameDetector = 'My Internet Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'unknown';

            if (preg_match('/myibrow\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Daumoa')) {
            $browserNameBrowscap = 'Daumoa';
            $browserNameDetector = 'Daumoa';
            $browserType         = 'Bot/Crawler';
            $browserMaker        = 'Daum Communications Corp';
            $crawler             = true;

            if (preg_match('/Daumoa (\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Camino')) {
            $browserNameBrowscap = 'Camino';
            $browserNameDetector = 'Camino';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Camino\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Cheshire')) {
            $browserNameBrowscap = 'Cheshire';
            $browserNameDetector = 'Cheshire';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Cheshire\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Classilla')) {
            $browserNameBrowscap = 'Classilla';
            $browserNameDetector = 'Classilla';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            //if (preg_match('/Classilla\/(\d+\.\d+)/', $ua, $matches)) {
            //    $browserVersion = $matches[1];
            //}

            $lite = false;
        } elseif (false !== strpos($ua, 'CometBird')) {
            $browserNameBrowscap = 'CometBird';
            $browserNameDetector = 'CometBird';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/CometBird\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'CometBird')) {
            $browserNameBrowscap = 'CometBird';
            $browserNameDetector = 'CometBird';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/CometBird\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'EnigmaFox')) {
            $browserNameBrowscap = 'EnigmaFox';
            $browserNameDetector = 'EnigmaFox';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/EnigmaFox\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'conkeror') || false !== strpos($ua, 'Conkeror')) {
            $browserNameBrowscap = 'Conkeror';
            $browserNameDetector = 'Conkeror';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/conkeror\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Galeon')) {
            $browserNameBrowscap = 'Galeon';
            $browserNameDetector = 'Galeon';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Galeon\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Hana')) {
            $browserNameBrowscap = 'Hana';
            $browserNameDetector = 'Hana';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Hana\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Iceape')) {
            $browserNameBrowscap = 'Iceape';
            $browserNameDetector = 'Iceape';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Iceape\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'IceCat')) {
            $browserNameBrowscap = 'IceCat';
            $browserNameDetector = 'IceCat';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/IceCat\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Iceweasel')) {
            $browserNameBrowscap = 'Iceweasel';
            $browserNameDetector = 'Iceweasel';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Iceweasel\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'K-Meleon')) {
            $browserNameBrowscap = 'K-Meleon';
            $browserNameDetector = 'K-Meleon';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/K\-Meleon\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'K-Ninja')) {
            $browserNameBrowscap = 'K-Ninja';
            $browserNameDetector = 'K-Ninja';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/K\-Ninja\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Kapiko')) {
            $browserNameBrowscap = 'Kapiko';
            $browserNameDetector = 'Kapiko';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Kapiko\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Kazehakase')) {
            $browserNameBrowscap = 'Kazehakase';
            $browserNameDetector = 'Kazehakaze';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Kazehakase\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'KMLite')) {
            $browserNameBrowscap = 'KMLite';
            $browserNameDetector = 'KNLite';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/KMLite\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'lolifox')) {
            $browserNameBrowscap = 'lolifox';
            $browserNameDetector = 'lolifox';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/lolifox\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Konqueror')) {
            $browserNameBrowscap = 'Konqueror';
            $browserNameDetector = 'Konqueror';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Konqueror\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Leechcraft')) {
            $browserNameBrowscap = 'Leechcraft';
            $browserNameDetector = 'Leechcraft';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            //if (preg_match('/Leechcraft\/(\d+\.\d+)/', $ua, $matches)) {
            //    $browserVersion = $matches[1];
            //}

            $lite = false;
        } elseif (false !== strpos($ua, 'Madfox')) {
            $browserNameBrowscap = 'Madfox';
            $browserNameDetector = 'Madfox';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Madfox\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'myibrow')) {
            $browserNameBrowscap = 'myibrow';
            $browserNameDetector = 'myibrow';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/myibrow\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Netscape6')) {
            $browserNameBrowscap = 'Netscape';
            $browserNameDetector = 'Netscape';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Netscape6\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Netscape')) {
            $browserNameBrowscap = 'Netscape';
            $browserNameDetector = 'Netscape';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Netscape\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Navigator')) {
            $browserNameBrowscap = 'Netscape Navigator';
            $browserNameDetector = 'Navigator';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Navigator\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Orca')) {
            $browserNameBrowscap = 'Orca';
            $browserNameDetector = 'Orca';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Orca\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Sylera')) {
            $browserNameBrowscap = 'Sylera';
            $browserNameDetector = 'Sylera';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Sylera\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'SeaMonkey')) {
            $browserNameBrowscap = 'SeaMonkey';
            $browserNameDetector = 'SeaMonkey';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/SeaMonkey\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Fennec')) {
            $browserNameBrowscap = 'Fennec';
            $browserNameDetector = 'Fennec';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Fennec\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'GoBrowser')) {
            $browserNameBrowscap = 'GoBrowser';
            $browserNameDetector = 'GoBrowser';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/GoBrowser\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Minimo')) {
            $browserNameBrowscap = 'Minimo';
            $browserNameDetector = 'Minimo';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Minimo\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'BonEcho')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/BonEcho\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($ua, 'Shiretoko')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Shiretoko\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($ua, 'Minefield')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Minefield\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($ua, 'Namoroka')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Namoroka\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($ua, 'GranParadiso')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/GranParadiso\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($ua, 'Firebird')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Firebird\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== stripos($ua, 'firefox')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Firefox\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($ua, 'FxiOS')) {
            $browserNameBrowscap = 'Firefox for iOS';
            $browserNameDetector = 'Firefox for iOS';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/FxiOS\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Browzar')) {
            $browserNameBrowscap = 'Browzar';
            $browserNameDetector = 'Browzar';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            $lite = false;
        } elseif (false !== strpos($ua, 'Crazy Browser')) {
            $browserNameBrowscap = 'Crazy Browser';
            $browserNameDetector = 'Crazy Browser';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Crazy Browser (\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'GreenBrowser')) {
            $browserNameBrowscap = 'GreenBrowser';
            $browserNameDetector = 'GreenBrowser';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            //if (preg_match('/Crazy Browser (\d+\.\d+)/', $ua, $matches)) {
            //    $browserVersion = $matches[1];
            //}

            $lite = false;
        } elseif (false !== strpos($ua, 'KKman')) {
            $browserNameBrowscap = 'KKman';
            $browserNameDetector = 'KKman';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/KKman(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Lobo')) {
            $browserNameBrowscap = 'Lobo';
            $browserNameDetector = 'Lobo';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Lobo\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Sleipnir')) {
            $browserNameBrowscap = 'Sleipnir';
            $browserNameDetector = 'Sleipnir';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Sleipnir\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'SlimBrowser')) {
            $browserNameBrowscap = 'SlimBrowser';
            $browserNameDetector = 'SlimBrowser';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/SlimBrowser\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'TencentTraveler')) {
            $browserNameBrowscap = 'TencentTraveler';
            $browserNameDetector = 'TencentTravaler';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/TencentTraveler (\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'TheWorld')) {
            $browserNameBrowscap = 'TheWorld';
            $browserNameDetector = 'TheWorld';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/TheWorld\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'MSIE')) {
            $browserNameBrowscap = 'IE';
            $browserNameDetector = 'Internet Explorer';
            $browserType         = 'Browser';
            $browserMaker        = 'Microsoft Corporation';

            if (preg_match('/MSIE (\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = true;
        } elseif (false !== strpos($ua, 'like Gecko') && false !== strpos($ua, 'rv:11.0')) {
            $browserNameBrowscap = 'IE';
            $browserNameDetector = 'Internet Explorer';
            $browserType         = 'Browser';
            $browserMaker        = 'Microsoft Corporation';

            if (preg_match('/rv:(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = true;
        } elseif (false !== strpos($ua, 'SMTBot')) {
            $browserNameBrowscap = 'SMTBot';
            $browserNameDetector = 'SMTBot';
            $browserType         = 'Bot/Crawler';
            $browserMaker        = 'SimilarTech Ltd.';
            $crawler             = true;

            if (preg_match('/SMTBot\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'gvfs')) {
            $browserNameBrowscap = 'gvfs';
            $browserNameDetector = 'gvfs';
            $browserType         = 'Tool';
            $browserMaker        = 'The GNOME Project';

            if (preg_match('/gvfs\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'luakit')) {
            $browserNameBrowscap = 'luakit';
            $browserNameDetector = 'luakit';
            $browserType         = 'Browser';
            $browserMaker        = 'Mason Larobina';

            if (preg_match('/WebKitGTK\+\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Cyberdog')) {
            $browserNameBrowscap = 'Cyberdog';
            $browserNameDetector = 'Cyberdog';
            $browserType         = 'Browser';
            //$browserMaker = 'Mason Larobina';

            if (preg_match('/Cyberdog\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'ELinks')) {
            $browserNameBrowscap = 'ELinks';
            $browserNameDetector = 'ELinks';
            $browserType         = 'Browser';
            //$browserMaker = 'Mason Larobina';

            //if (preg_match('/WebKitGTK\+\/(\d+\.\d+)/', $ua, $matches)) {
            //    $browserVersion = $matches[1];
            //}

            $lite = false;
        } elseif (false !== strpos($ua, 'Links')) {
            $browserNameBrowscap = 'Links';
            $browserNameDetector = 'Links';
            $browserType         = 'Browser';
            //$browserMaker = 'Mason Larobina';

            //if (preg_match('/WebKitGTK\+\/(\d+\.\d+)/', $ua, $matches)) {
            //    $browserVersion = $matches[1];
            //}

            $lite = false;
        } elseif (false !== strpos($ua, 'Galaxy')) {
            $browserNameBrowscap = 'Galaxy';
            $browserNameDetector = 'Galaxy';
            $browserType         = 'Browser';
            //$browserMaker = 'Mason Larobina';

            if (preg_match('/Galaxy\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'iNet Browser')) {
            $browserNameBrowscap = 'iNet Browser';
            $browserNameDetector = 'iNet Browser';
            $browserType         = 'Browser';
            //$browserMaker = 'Mason Larobina';

            if (preg_match('/iNet Browser (\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Dalvik')) {
            $browserNameBrowscap = 'Dalvik';
            $browserNameDetector = 'Dalvik';
            $browserType         = 'Application';
            $browserMaker        = 'Google Inc';

            if (preg_match('/Dalvik (\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            } elseif (preg_match('/Dalvik\/(\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($ua, 'Uzbl')) {
            $browserNameBrowscap = 'Uzbl';
            $browserNameDetector = 'Uzbl';
            $browserType         = 'Browser';
            //$browserMaker = 'Mason Larobina';

            if (preg_match('/Uzbl (\d+\.\d+)/', $ua, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        }

        return [
            $browserNameBrowscap,
            $browserType,
            $browserBits,
            $browserMaker,
            $browserModus,
            $browserVersion,
            $browserNameDetector,
            $lite,
            $crawler,
        ];
    }
}
