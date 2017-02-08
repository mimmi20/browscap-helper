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
use BrowserDetector\BrowserDetector;
use Psr\Cache\CacheItemPoolInterface;
use UaResult\Engine\EngineInterface;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class Browser
{
    /**
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param string                            $useragent
     * @param \BrowserDetector\BrowserDetector  $detector
     * @param \UaResult\Engine\EngineInterface  $engine
     * @param                                   $browserNameDetector
     * @param null                              $browserType
     * @param string                            $browserMaker
     * @param string                            $browserVersion
     *
     * @return array
     */
    public function detect(
        CacheItemPoolInterface $cache,
        $useragent,
        BrowserDetector $detector,
        EngineInterface $engine,
        $browserNameDetector,
        $browserType = null,
        $browserMaker = 'unknown',
        $browserVersion = '0.0'
    ) {
        $browserNameBrowscap = 'Default Browser';
        $browserType         = 'unknown';

        $crawler      = false;
        $lite         = true;
        $browserModus = null;

        $chromeVersion = 0;

        if (false !== strpos($useragent, 'Chrome')) {
            if (preg_match('/Chrome\/(\d+\.\d+)/', $useragent, $matches)) {
                $chromeVersion = (float) $matches[1];
            }
        }

        $browserBits  = (new Bits\Browser($useragent))->getBits();

        if (false !== strpos($useragent, 'OPR') && false !== strpos($useragent, 'Android')) {
            $browserNameBrowscap = 'Opera Mobile';
            $browserNameDetector = 'Opera Mobile';
            $browserType         = 'Browser';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/OPR\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Opera Mobi')) {
            $browserNameBrowscap = 'Opera Mobile';
            $browserNameDetector = 'Opera Mobile';
            $browserType         = 'Browser';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/Version\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'OPR')) {
            $browserNameBrowscap = 'Opera';
            $browserNameDetector = 'Opera';
            $browserType         = 'Browser';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/OPR\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($useragent, 'Opera')) {
            $browserNameBrowscap = 'Opera';
            $browserNameDetector = 'Opera';
            $browserType         = 'Browser';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/Version\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            } elseif (preg_match('/Opera\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($useragent, 'Coast')) {
            $browserNameBrowscap = 'Coast';
            $browserNameDetector = 'Coast';
            $browserType         = 'Application';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/Coast\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($useragent, 'Mercury')) {
            $browserNameBrowscap = 'Mercury';
            $browserNameDetector = 'Mercury';
            $browserType         = 'Browser';
            $browserMaker        = 'iLegendSoft, Inc.';

            if (preg_match('/Mercury\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($useragent, 'CommonCrawler Node')) {
            $browserNameBrowscap = 'CommonCrawler Node';
            $browserNameDetector = 'CommonCrawler Node';
            $browserType         = 'Bot/Crawler';
            $crawler             = true;
        } elseif (false !== strpos($useragent, 'UCBrowser') || false !== strpos($useragent, 'UC Browser')) {
            $browserNameBrowscap = 'UC Browser';
            $browserNameDetector = 'UC Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'UC Web';

            if (preg_match('/UCBrowser\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            } elseif (preg_match('/UC Browser(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'iCab')) {
            $browserNameBrowscap = 'iCab';
            $browserNameDetector = 'iCab';
            $browserType         = 'Browser';
            $browserMaker        = 'Alexander Clauss';

            if (preg_match('/iCab\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Lunascape')) {
            $browserNameBrowscap = 'Lunascape';
            $browserNameDetector = 'Lunascape';
            $browserType         = 'Browser';
            //$browserMaker = 'Alexander Clauss';

            if (preg_match('/Lunascape (\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== stripos($useragent, 'midori')) {
            $browserNameBrowscap = 'Midori';
            $browserNameDetector = 'Midori';
            $browserType         = 'Browser';
            //$browserMaker = 'Alexander Clauss';

            if (preg_match('/Midori\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'OmniWeb')) {
            $browserNameBrowscap = 'OmniWeb';
            $browserNameDetector = 'Omniweb';
            $browserType         = 'Browser';
            //$browserMaker = 'Alexander Clauss';

            if (preg_match('/OmniWeb\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== stripos($useragent, 'maxthon') || false !== strpos($useragent, 'MyIE2')) {
            $browserNameBrowscap = 'Maxthon';
            $browserNameDetector = 'Maxthon';
            $browserType         = 'Browser';
            //$browserMaker = 'Alexander Clauss';

            if (preg_match('/maxthon (\d+\.\d+)/i', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'PhantomJS')) {
            $browserNameBrowscap = 'PhantomJS';
            $browserNameDetector = 'PhantomJS';
            $browserType         = 'Browser';
            $browserMaker        = 'phantomjs.org';

            if (preg_match('/PhantomJS\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'YaBrowser')) {
            $browserNameBrowscap = 'Yandex Browser';
            $browserNameDetector = 'Yandex Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Yandex';

            if (preg_match('/YaBrowser\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Kamelio')) {
            $browserNameBrowscap = 'Kamelio App';
            $browserNameDetector = 'Kamelio App';
            $browserType         = 'Application';
            $browserMaker        = 'Kamelio';

            $lite = false;
        } elseif (false !== strpos($useragent, 'FBAV')) {
            $browserNameBrowscap = 'Facebook App';
            $browserNameDetector = 'Facebook App';
            $browserType         = 'Application';
            $browserMaker        = 'Facebook';

            if (preg_match('/FBAV\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'ACHEETAHI')) {
            $browserNameBrowscap = 'CM Browser';
            $browserNameDetector = 'CM Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Cheetah Mobile';

            $lite = false;
        } elseif (false !== strpos($useragent, 'bdbrowser_i18n')) {
            $browserNameBrowscap = 'Baidu Browser';
            $browserNameDetector = 'Baidu Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Baidu';

            if (preg_match('/bdbrowser\_i18n\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'bdbrowserhd_i18n')) {
            $browserNameBrowscap = 'Baidu Browser HD';
            $browserNameDetector = 'Baidu Browser HD';
            $browserType         = 'Browser';
            $browserMaker        = 'Baidu';

            if (preg_match('/bdbrowserhd\_i18n\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'bdbrowser_mini')) {
            $browserNameBrowscap = 'Baidu Browser Mini';
            $browserNameDetector = 'Baidu Browser Mini';
            $browserType         = 'Browser';
            $browserMaker        = 'Baidu';

            if (preg_match('/bdbrowser\_mini\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Puffin')) {
            $browserNameBrowscap = 'Puffin';
            $browserNameDetector = 'Puffin';
            $browserType         = 'Browser';
            $browserMaker        = 'CloudMosa Inc.';

            if (preg_match('/Puffin\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'SamsungBrowser')) {
            $browserNameBrowscap = 'Samsung Browser';
            $browserNameDetector = 'Samsung Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Samsung';

            if (preg_match('/SamsungBrowser\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Silk')) {
            $browserNameBrowscap = 'Silk';
            $browserNameDetector = 'Silk';
            $browserType         = 'Browser';
            $browserMaker        = 'Amazon.com, Inc.';

            if (preg_match('/Silk\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;

            if (false === strpos($useragent, 'Android')) {
                $browserModus = 'Desktop Mode';
            }
        } elseif (false !== strpos($useragent, 'coc_coc_browser')) {
            $browserNameBrowscap = 'Coc Coc Browser';
            $browserNameDetector = 'Coc Coc Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Coc Coc Company Limited';

            if (preg_match('/coc_coc_browser\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'NaverMatome')) {
            $browserNameBrowscap = 'NaverMatome';
            $browserNameDetector = 'NaverMatome';
            $browserType         = 'Application';
            $browserMaker        = 'Naver';

            if (preg_match('/NaverMatome\-Android\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Flipboard')) {
            $browserNameBrowscap = 'Flipboard App';
            $browserNameDetector = 'Flipboard App';
            $browserType         = 'Application';
            $browserMaker        = 'Flipboard, Inc.';

            if (preg_match('/Flipboard\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Arora')) {
            $browserNameBrowscap = 'Arora';
            $browserNameDetector = 'Arora';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/Arora\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Acoo Browser')) {
            $browserNameBrowscap = 'Acoo Browser';
            $browserNameDetector = 'Acoo Browser';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/Acoo Browser\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'ABrowse')) {
            $browserNameBrowscap = 'ABrowse';
            $browserNameDetector = 'ABrowse';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/ABrowse\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'AmigaVoyager')) {
            $browserNameBrowscap = 'AmigaVoyager';
            $browserNameDetector = 'AmigaVoyager';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/AmigaVoyager\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Beonex')) {
            $browserNameBrowscap = 'Beonex';
            $browserNameDetector = 'Beonex';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/Beonex\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Stainless')) {
            $browserNameBrowscap = 'Stainless';
            $browserNameDetector = 'Stainless';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/Stainless\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Sundance')) {
            $browserNameBrowscap = 'Sundance';
            $browserNameDetector = 'Sundance';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/Sundance\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Sunrise')) {
            $browserNameBrowscap = 'Sunrise';
            $browserNameDetector = 'Sunrise';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/Sunrise\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'SunriseBrowser')) {
            $browserNameBrowscap = 'Sunrise';
            $browserNameDetector = 'Sunrise';
            $browserType         = 'Browser';
            //$browserMaker = 'Flipboard, Inc.';

            if (preg_match('/SunriseBrowser\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Seznam.cz')) {
            $browserNameBrowscap = 'Seznam Browser';
            $browserNameDetector = 'Seznam Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Seznam.cz, a.s.';

            if (preg_match('/Seznam\.cz\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Aviator')) {
            $browserNameBrowscap = 'WhiteHat Aviator';
            $browserNameDetector = 'WhiteHat Aviator';
            $browserType         = 'Browser';
            $browserMaker        = 'WhiteHat Security';

            if (preg_match('/Aviator\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Dragon')) {
            $browserNameBrowscap = 'Dragon';
            $browserNameDetector = 'Dragon';
            $browserType         = 'Browser';
            $browserMaker        = 'Comodo Group Inc';

            if (preg_match('/Dragon\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Beamrise')) {
            $browserNameBrowscap = 'Beamrise';
            $browserNameDetector = 'Beamrise';
            $browserType         = 'Browser';
            $browserMaker        = 'Beamrise Team';

            if (preg_match('/Beamrise\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Diglo')) {
            $browserNameBrowscap = 'Diglo';
            $browserNameDetector = 'Diglo';
            $browserType         = 'Browser';
            $browserMaker        = 'Diglo Inc';

            if (preg_match('/Diglo\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'APUSBrowser')) {
            $browserNameBrowscap = 'APUSBrowser';
            $browserNameDetector = 'APUSBrowser';
            $browserType         = 'Browser';
            $browserMaker        = 'APUS-Group';

            if (preg_match('/APUSBrowser\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Chedot')) {
            $browserNameBrowscap = 'Chedot';
            $browserNameDetector = 'Chedot';
            $browserType         = 'Browser';
            $browserMaker        = 'Chedot.com';

            if (preg_match('/Chedot\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Qword')) {
            $browserNameBrowscap = 'Qword Browser';
            $browserNameDetector = 'Qword Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Qword Corporation';

            if (preg_match('/Qword\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Iridium')) {
            $browserNameBrowscap = 'Iridium Browser';
            $browserNameDetector = 'Iridium Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'Iridium Browser Team';

            if (preg_match('/Iridium\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'MxNitro')) {
            $browserNameBrowscap = 'Maxthon Nitro';
            $browserNameDetector = 'Maxthon Nitro';
            $browserType         = 'Browser';
            $browserMaker        = 'Maxthon International Limited';

            if (preg_match('/MxNitro\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'MxBrowser')) {
            $browserNameBrowscap = 'Maxthon';
            $browserNameDetector = 'Maxthon';
            $browserType         = 'Browser';
            $browserMaker        = 'Maxthon International Limited';

            if (preg_match('/MxBrowser\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Maxthon')) {
            $browserNameBrowscap = 'Maxthon';
            $browserNameDetector = 'Maxthon';
            $browserType         = 'Browser';
            $browserMaker        = 'Maxthon International Limited';

            if (preg_match('/Maxthon\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Superbird') || false !== strpos($useragent, 'SuperBird')) {
            $browserNameBrowscap = 'SuperBird';
            $browserNameDetector = 'SuperBird';
            $browserType         = 'Browser';
            $browserMaker        = 'superbird-browser.com';

            if (preg_match('/superbird\/(\d+\.\d+)/i', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'TinyBrowser')) {
            $browserNameBrowscap = 'TinyBrowser';
            $browserNameDetector = 'TinyBrowser';
            $browserType         = 'Browser';
            $browserMaker        = 'unknown';

            if (preg_match('/TinyBrowser\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Chrome') && false !== strpos($useragent, 'Version')) {
            $browserNameBrowscap = 'Android WebView';
            $browserNameDetector = 'Android WebView';
            $browserType         = 'Browser';
            $browserMaker        = 'Google Inc';

            if (preg_match('/Version\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion <= 1) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'Safari') && false !== strpos($useragent, 'Version') && false !== strpos(
            $useragent,
            'Tizen'
        )
        ) {
            $browserNameBrowscap = 'Samsung WebView';
            $browserNameDetector = 'Samsung WebView';
            $browserType         = 'Browser';
            $browserMaker        = 'Samsung';

            if (preg_match('/Version\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Chromium')) {
            $browserNameBrowscap = 'Chromium';
            $browserNameDetector = 'Chromium';
            $browserType         = 'Browser';
            $browserMaker        = 'Google Inc';

            if (preg_match('/Chromium\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Flock')) {
            $browserNameBrowscap = 'Flock';
            $browserNameDetector = 'Flock';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Flock\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Fluid')) {
            $browserNameBrowscap = 'Fluid';
            $browserNameDetector = 'Fluid';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Fluid\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'ChromePlus')) {
            $browserNameBrowscap = 'ChromePlus';
            $browserNameDetector = 'ChromePlus';
            $browserType         = 'Browser';
            //$browserMaker = 'Google Inc';

            if (preg_match('/ChromePlus\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'RockMelt')) {
            $browserNameBrowscap = 'RockMelt';
            $browserNameDetector = 'RockMelt';
            $browserType         = 'Browser';
            //$browserMaker = 'Google Inc';

            if (preg_match('/RockMelt\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Shiira')) {
            $browserNameBrowscap = 'Shiira';
            $browserNameDetector = 'Shiira';
            $browserType         = 'Browser';
            //$browserMaker = 'Google Inc';

            if (preg_match('/Shiira\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Iron')) {
            $browserNameBrowscap = 'Iron';
            $browserNameDetector = 'Iron';
            $browserType         = 'Browser';
            //$browserMaker = 'Google Inc';

            if (preg_match('/Iron\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Chrome')) {
            $browserNameBrowscap = 'Chrome';
            $browserNameDetector = 'Chrome';
            $browserType         = 'Browser';
            $browserMaker        = 'Google Inc';
            $browserVersion      = (string) $chromeVersion;

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'CriOS')) {
            $browserNameBrowscap = 'Chrome';
            $browserNameDetector = 'Chrome';
            $browserType         = 'Browser';
            $browserMaker        = 'Google Inc';

            if (preg_match('/CriOS\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'OPiOS')) {
            $browserNameBrowscap = 'Opera Mini';
            $browserNameDetector = 'Opera Mini';
            $browserType         = 'Browser';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/OPiOS\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($useragent, 'Opera Mini')) {
            $browserNameBrowscap = 'Opera Mini';
            $browserNameDetector = 'Opera Mini';
            $browserType         = 'Browser';
            $browserMaker        = 'Opera Software ASA';

            if (preg_match('/Opera Mini\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($useragent, 'FlyFlow')) {
            $browserNameBrowscap = 'FlyFlow';
            $browserNameDetector = 'FlyFlow';
            $browserType         = 'Browser';
            $browserMaker        = 'Baidu';

            if (preg_match('/FlyFlow\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Epiphany') || false !== strpos($useragent, 'epiphany')) {
            $browserNameBrowscap = 'Epiphany';
            $browserNameDetector = 'Epiphany';
            $browserType         = 'Browser';
            //$browserMaker = 'Baidu';

            if (preg_match('/Epiphany\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'GSA')) {
            $browserNameBrowscap = 'Google App';
            $browserNameDetector = 'Google App';
            $browserType         = 'Application';
            $browserMaker        = 'Google Inc';

            if (preg_match('/GSA\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Safari')
            && false !== strpos($useragent, 'Version')
            && false !== strpos($useragent, 'Android')
        ) {
            $browserNameBrowscap = 'Android';
            $browserNameDetector = 'Android';
            $browserType         = 'Browser';
            $browserMaker        = 'Google Inc';

            if (preg_match('/Version\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion !== '4.0') {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'BlackBerry') && false !== strpos($useragent, 'Version')) {
            $browserNameBrowscap = 'BlackBerry';
            $browserNameDetector = 'BlackBerry';
            $browserType         = 'Browser';
            $browserMaker        = 'Research In Motion Limited';

            if (preg_match('/Version\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }
            $lite = false;
        } elseif (false !== strpos($useragent, 'Safari') && false !== strpos($useragent, 'Version')) {
            $browserNameBrowscap = 'Safari';
            $browserNameDetector = 'Safari';
            $browserType         = 'Browser';
            $browserMaker        = 'Apple Inc';

            if (preg_match('/Version\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }
        } elseif (false !== strpos($useragent, 'PaleMoon')) {
            $browserNameBrowscap = 'PaleMoon';
            $browserNameDetector = 'PaleMoon';
            $browserType         = 'Browser';
            $browserMaker        = 'Moonchild Productions';

            if (preg_match('/PaleMoon\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Phoenix')) {
            $browserNameBrowscap = 'Phoenix';
            $browserNameDetector = 'Phoenix';
            $browserType         = 'Browser';
            //$browserMaker = 'www.waterfoxproject.org';

            if (preg_match('/Phoenix\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== stripos($useragent, 'Prism')) {
            $browserNameBrowscap = 'Prism';
            $browserNameDetector = 'Prism';
            $browserType         = 'Browser';
            //$browserMaker = 'www.waterfoxproject.org';

            if (preg_match('/Prism\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== stripos($useragent, 'QtWeb Internet Browser')) {
            $browserNameBrowscap = 'QtWeb Internet Browser';
            $browserNameDetector = 'QtWeb Internet Browser';
            $browserType         = 'Browser';
            //$browserMaker = 'www.waterfoxproject.org';

            if (preg_match('/QtWeb Internet Browser\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Waterfox')) {
            $browserNameBrowscap = 'Waterfox';
            $browserNameDetector = 'Waterfox';
            $browserType         = 'Browser';
            $browserMaker        = 'www.waterfoxproject.org';

            if (preg_match('/Waterfox\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'QupZilla')) {
            $browserNameBrowscap = 'QupZilla';
            $browserNameDetector = 'QupZilla';
            $browserType         = 'Browser';
            $browserMaker        = 'David Rosca and Community';

            if (preg_match('/QupZilla\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Thunderbird')) {
            $browserNameBrowscap = 'Thunderbird';
            $browserNameDetector = 'Thunderbird';
            $browserType         = 'Email Client';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Thunderbird\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'kontact')) {
            $browserNameBrowscap = 'Kontact';
            $browserNameDetector = 'Kontact';
            $browserType         = 'Email Client';
            $browserMaker        = 'KDE e.V.';

            if (preg_match('/kontact\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Fennec')) {
            $browserNameBrowscap = 'Fennec';
            $browserNameDetector = 'Fennec';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Fennec\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'myibrow')) {
            $browserNameBrowscap = 'My Internet Browser';
            $browserNameDetector = 'My Internet Browser';
            $browserType         = 'Browser';
            $browserMaker        = 'unknown';

            if (preg_match('/myibrow\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Daumoa')) {
            $browserNameBrowscap = 'Daumoa';
            $browserNameDetector = 'Daumoa';
            $browserType         = 'Bot/Crawler';
            $browserMaker        = 'Daum Communications Corp';
            $crawler             = true;

            if (preg_match('/Daumoa (\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Camino')) {
            $browserNameBrowscap = 'Camino';
            $browserNameDetector = 'Camino';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Camino\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Cheshire')) {
            $browserNameBrowscap = 'Cheshire';
            $browserNameDetector = 'Cheshire';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Cheshire\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Classilla')) {
            $browserNameBrowscap = 'Classilla';
            $browserNameDetector = 'Classilla';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            //if (preg_match('/Classilla\/(\d+\.\d+)/', $ua, $matches)) {
            //    $browserVersion = $matches[1];
            //}

            $lite = false;
        } elseif (false !== strpos($useragent, 'CometBird')) {
            $browserNameBrowscap = 'CometBird';
            $browserNameDetector = 'CometBird';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/CometBird\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'CometBird')) {
            $browserNameBrowscap = 'CometBird';
            $browserNameDetector = 'CometBird';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/CometBird\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'EnigmaFox')) {
            $browserNameBrowscap = 'EnigmaFox';
            $browserNameDetector = 'EnigmaFox';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/EnigmaFox\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'conkeror') || false !== strpos($useragent, 'Conkeror')) {
            $browserNameBrowscap = 'Conkeror';
            $browserNameDetector = 'Conkeror';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/conkeror\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Galeon')) {
            $browserNameBrowscap = 'Galeon';
            $browserNameDetector = 'Galeon';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Galeon\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Hana')) {
            $browserNameBrowscap = 'Hana';
            $browserNameDetector = 'Hana';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Hana\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Iceape')) {
            $browserNameBrowscap = 'Iceape';
            $browserNameDetector = 'Iceape';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Iceape\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'IceCat')) {
            $browserNameBrowscap = 'IceCat';
            $browserNameDetector = 'IceCat';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/IceCat\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Iceweasel')) {
            $browserNameBrowscap = 'Iceweasel';
            $browserNameDetector = 'Iceweasel';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Iceweasel\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'K-Meleon')) {
            $browserNameBrowscap = 'K-Meleon';
            $browserNameDetector = 'K-Meleon';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/K\-Meleon\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'K-Ninja')) {
            $browserNameBrowscap = 'K-Ninja';
            $browserNameDetector = 'K-Ninja';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/K\-Ninja\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Kapiko')) {
            $browserNameBrowscap = 'Kapiko';
            $browserNameDetector = 'Kapiko';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Kapiko\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Kazehakase')) {
            $browserNameBrowscap = 'Kazehakase';
            $browserNameDetector = 'Kazehakaze';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Kazehakase\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'KMLite')) {
            $browserNameBrowscap = 'KMLite';
            $browserNameDetector = 'KNLite';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/KMLite\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'lolifox')) {
            $browserNameBrowscap = 'lolifox';
            $browserNameDetector = 'lolifox';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/lolifox\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Konqueror')) {
            $browserNameBrowscap = 'Konqueror';
            $browserNameDetector = 'Konqueror';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Konqueror\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Leechcraft')) {
            $browserNameBrowscap = 'Leechcraft';
            $browserNameDetector = 'Leechcraft';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            //if (preg_match('/Leechcraft\/(\d+\.\d+)/', $ua, $matches)) {
            //    $browserVersion = $matches[1];
            //}

            $lite = false;
        } elseif (false !== strpos($useragent, 'Madfox')) {
            $browserNameBrowscap = 'Madfox';
            $browserNameDetector = 'Madfox';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Madfox\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'myibrow')) {
            $browserNameBrowscap = 'myibrow';
            $browserNameDetector = 'myibrow';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/myibrow\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Netscape6')) {
            $browserNameBrowscap = 'Netscape';
            $browserNameDetector = 'Netscape';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Netscape6\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Netscape')) {
            $browserNameBrowscap = 'Netscape';
            $browserNameDetector = 'Netscape';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Netscape\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Navigator')) {
            $browserNameBrowscap = 'Netscape Navigator';
            $browserNameDetector = 'Navigator';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Navigator\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Orca')) {
            $browserNameBrowscap = 'Orca';
            $browserNameDetector = 'Orca';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Orca\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Sylera')) {
            $browserNameBrowscap = 'Sylera';
            $browserNameDetector = 'Sylera';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Sylera\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'SeaMonkey')) {
            $browserNameBrowscap = 'SeaMonkey';
            $browserNameDetector = 'SeaMonkey';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/SeaMonkey\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Fennec')) {
            $browserNameBrowscap = 'Fennec';
            $browserNameDetector = 'Fennec';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Fennec\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'GoBrowser')) {
            $browserNameBrowscap = 'GoBrowser';
            $browserNameDetector = 'GoBrowser';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/GoBrowser\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Minimo')) {
            $browserNameBrowscap = 'Minimo';
            $browserNameDetector = 'Minimo';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Minimo\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'BonEcho')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/BonEcho\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'Shiretoko')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Shiretoko\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'Minefield')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Minefield\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'Namoroka')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Namoroka\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'GranParadiso')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/GranParadiso\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'Firebird')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Firebird\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== stripos($useragent, 'firefox')) {
            $browserNameBrowscap = 'Firefox';
            $browserNameDetector = 'Firefox';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/Firefox\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'FxiOS')) {
            $browserNameBrowscap = 'Firefox for iOS';
            $browserNameDetector = 'Firefox for iOS';
            $browserType         = 'Browser';
            $browserMaker        = 'Mozilla Foundation';

            if (preg_match('/FxiOS\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Browzar')) {
            $browserNameBrowscap = 'Browzar';
            $browserNameDetector = 'Browzar';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            $lite = false;
        } elseif (false !== strpos($useragent, 'Crazy Browser')) {
            $browserNameBrowscap = 'Crazy Browser';
            $browserNameDetector = 'Crazy Browser';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Crazy Browser (\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'GreenBrowser')) {
            $browserNameBrowscap = 'GreenBrowser';
            $browserNameDetector = 'GreenBrowser';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            //if (preg_match('/Crazy Browser (\d+\.\d+)/', $ua, $matches)) {
            //    $browserVersion = $matches[1];
            //}

            $lite = false;
        } elseif (false !== strpos($useragent, 'KKman')) {
            $browserNameBrowscap = 'KKman';
            $browserNameDetector = 'KKman';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/KKman(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Lobo')) {
            $browserNameBrowscap = 'Lobo';
            $browserNameDetector = 'Lobo';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Lobo\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Sleipnir')) {
            $browserNameBrowscap = 'Sleipnir';
            $browserNameDetector = 'Sleipnir';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/Sleipnir\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'SlimBrowser')) {
            $browserNameBrowscap = 'SlimBrowser';
            $browserNameDetector = 'SlimBrowser';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/SlimBrowser\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'TencentTraveler')) {
            $browserNameBrowscap = 'TencentTraveler';
            $browserNameDetector = 'TencentTravaler';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/TencentTraveler (\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'TheWorld')) {
            $browserNameBrowscap = 'TheWorld';
            $browserNameDetector = 'TheWorld';
            $browserType         = 'Browser';
            //$browserMaker = 'Mozilla Foundation';

            if (preg_match('/TheWorld\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'MSIE')) {
            $browserNameBrowscap = 'IE';
            $browserNameDetector = 'Internet Explorer';
            $browserType         = 'Browser';
            $browserMaker        = 'Microsoft Corporation';

            if (preg_match('/MSIE (\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = true;
        } elseif (false !== strpos($useragent, 'like Gecko') && false !== strpos($useragent, 'rv:11.0')) {
            $browserNameBrowscap = 'IE';
            $browserNameDetector = 'Internet Explorer';
            $browserType         = 'Browser';
            $browserMaker        = 'Microsoft Corporation';

            if (preg_match('/rv:(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = true;
        } elseif (false !== strpos($useragent, 'SMTBot')) {
            $browserNameBrowscap = 'SMTBot';
            $browserNameDetector = 'SMTBot';
            $browserType         = 'Bot/Crawler';
            $browserMaker        = 'SimilarTech Ltd.';
            $crawler             = true;

            if (preg_match('/SMTBot\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'gvfs')) {
            $browserNameBrowscap = 'gvfs';
            $browserNameDetector = 'gvfs';
            $browserType         = 'Tool';
            $browserMaker        = 'The GNOME Project';

            if (preg_match('/gvfs\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'luakit')) {
            $browserNameBrowscap = 'luakit';
            $browserNameDetector = 'luakit';
            $browserType         = 'Browser';
            $browserMaker        = 'Mason Larobina';

            if (preg_match('/WebKitGTK\+\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Cyberdog')) {
            $browserNameBrowscap = 'Cyberdog';
            $browserNameDetector = 'Cyberdog';
            $browserType         = 'Browser';
            //$browserMaker = 'Mason Larobina';

            if (preg_match('/Cyberdog\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'ELinks')) {
            $browserNameBrowscap = 'ELinks';
            $browserNameDetector = 'ELinks';
            $browserType         = 'Browser';
            //$browserMaker = 'Mason Larobina';

            //if (preg_match('/WebKitGTK\+\/(\d+\.\d+)/', $ua, $matches)) {
            //    $browserVersion = $matches[1];
            //}

            $lite = false;
        } elseif (false !== strpos($useragent, 'Links')) {
            $browserNameBrowscap = 'Links';
            $browserNameDetector = 'Links';
            $browserType         = 'Browser';
            //$browserMaker = 'Mason Larobina';

            //if (preg_match('/WebKitGTK\+\/(\d+\.\d+)/', $ua, $matches)) {
            //    $browserVersion = $matches[1];
            //}

            $lite = false;
        } elseif (false !== strpos($useragent, 'Galaxy')) {
            $browserNameBrowscap = 'Galaxy';
            $browserNameDetector = 'Galaxy';
            $browserType         = 'Browser';
            //$browserMaker = 'Mason Larobina';

            if (preg_match('/Galaxy\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'iNet Browser')) {
            $browserNameBrowscap = 'iNet Browser';
            $browserNameDetector = 'iNet Browser';
            $browserType         = 'Browser';
            //$browserMaker = 'Mason Larobina';

            if (preg_match('/iNet Browser (\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Dalvik')) {
            $browserNameBrowscap = 'Dalvik';
            $browserNameDetector = 'Dalvik';
            $browserType         = 'Application';
            $browserMaker        = 'Google Inc';

            if (preg_match('/Dalvik (\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            } elseif (preg_match('/Dalvik\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            $lite = false;
        } elseif (false !== strpos($useragent, 'Uzbl')) {
            $browserNameBrowscap = 'Uzbl';
            $browserNameDetector = 'Uzbl';
            $browserType         = 'Browser';
            //$browserMaker = 'Mason Larobina';

            if (preg_match('/Uzbl (\d+\.\d+)/', $useragent, $matches)) {
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
