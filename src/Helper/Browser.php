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

use BrowserDetector\BrowserDetector;
use BrowserDetector\Loader\BrowserLoader;
use Psr\Cache\CacheItemPoolInterface;

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
     * @param string                            $browserName
     *
     * @return array
     */
    public function detect(
        CacheItemPoolInterface $cache,
        $useragent,
        BrowserDetector $detector,
        $browserName
    ) {
        $loader = new BrowserLoader($cache);

        $lite           = true;
        $chromeVersion  = 0;
        $browser        = null;
        $browserVersion = null;

        if (false !== strpos($useragent, 'Chrome')) {
            if (preg_match('/Chrome\/(\d+\.\d+)/', $useragent, $matches)) {
                $chromeVersion = (float) $matches[1];
            }
        }

        if (false !== strpos($useragent, 'OPR') && false !== strpos($useragent, 'Android')) {
            list($browser) = $loader->load('opera mobile', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Opera Mobi')) {
            list($browser) = $loader->load('opera mobile', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'OPR')) {
            list($browser) = $loader->load('opera', $useragent);
        } elseif (false !== strpos($useragent, 'Opera')) {
            list($browser) = $loader->load('opera', $useragent);
        } elseif (false !== strpos($useragent, 'Coast')) {
            list($browser) = $loader->load('coast', $useragent);
        } elseif (false !== strpos($useragent, 'Mercury')) {
            list($browser) = $loader->load('mercury', $useragent);
        } elseif (false !== strpos($useragent, 'CommonCrawler Node')) {
            list($browser) = $loader->load('commoncrawler node', $useragent);
        } elseif (false !== strpos($useragent, 'UCBrowser') || false !== strpos($useragent, 'UC Browser')) {
            list($browser) = $loader->load('ucbrowser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'iCab')) {
            list($browser) = $loader->load('icab', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Lunascape')) {
            list($browser) = $loader->load('lunascape', $useragent);
            $lite          = false;
        } elseif (false !== stripos($useragent, 'midori')) {
            list($browser) = $loader->load('midori', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'OmniWeb')) {
            list($browser) = $loader->load('omniweb', $useragent);
            $lite          = false;
        } elseif (false !== stripos($useragent, 'maxthon') || false !== strpos($useragent, 'MyIE2')) {
            list($browser) = $loader->load('maxthon', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'PhantomJS')) {
            list($browser) = $loader->load('phantomjs', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'YaBrowser')) {
            list($browser) = $loader->load('yabrowser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Kamelio')) {
            list($browser) = $loader->load('kamelio app', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'FBAV')) {
            list($browser) = $loader->load('facebook app', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'ACHEETAHI')) {
            list($browser) = $loader->load('cm browser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'bdbrowser_i18n')) {
            list($browser) = $loader->load('baidu browser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'bdbrowserhd_i18n')) {
            list($browser) = $loader->load('baidu browser hd', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'bdbrowser_mini')) {
            list($browser) = $loader->load('baidu browser mini', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Puffin')) {
            list($browser) = $loader->load('puffin', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'SamsungBrowser')) {
            list($browser) = $loader->load('samsungbrowser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Silk')) {
            list($browser) = $loader->load('silk', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'coc_coc_browser')) {
            list($browser) = $loader->load('coc_coc_browser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'NaverMatome')) {
            list($browser) = $loader->load('matome', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Flipboard')) {
            list($browser) = $loader->load('flipboard app', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Arora')) {
            list($browser) = $loader->load('arora', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Acoo Browser')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Acoo Browser
            $browser = null;
        } elseif (false !== strpos($useragent, 'ABrowse')) {
            list($browser) = $loader->load('abrowse', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'AmigaVoyager')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add AmigaVoyager
            $browser = null;
        } elseif (false !== strpos($useragent, 'Beonex')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Beonex
            $browser = null;
        } elseif (false !== strpos($useragent, 'Stainless')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Stainless
            $browser = null;
        } elseif (false !== strpos($useragent, 'Sundance')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Sundance
            $browser = null;
        } elseif (false !== strpos($useragent, 'Sunrise')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Sunrise
            $browser = null;
        } elseif (false !== strpos($useragent, 'SunriseBrowser')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add SunriseBrowser
            $browser = null;
        } elseif (false !== strpos($useragent, 'Seznam.cz')) {
            list($browser) = $loader->load('seznam browser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Aviator')) {
            list($browser) = $loader->load('aviator', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Dragon')) {
            list($browser) = $loader->load('dragon', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Beamrise')) {
            list($browser) = $loader->load('beamrise', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Diglo')) {
            list($browser) = $loader->load('diglo', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'APUSBrowser')) {
            list($browser) = $loader->load('apusbrowser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Chedot')) {
            list($browser) = $loader->load('chedot', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Qword')) {
            list($browser) = $loader->load('qword browser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Iridium')) {
            list($browser) = $loader->load('iridium browser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'MxNitro')) {
            list($browser) = $loader->load('maxthon nitro', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'MxBrowser')) {
            list($browser) = $loader->load('maxthon', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Maxthon')) {
            list($browser) = $loader->load('maxthon', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Superbird') || false !== strpos($useragent, 'SuperBird')) {
            list($browser) = $loader->load('superbird', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'TinyBrowser')) {
            list($browser) = $loader->load('tinybrowser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Chrome') && false !== strpos($useragent, 'Version')) {
            list($browser) = $loader->load('android webview', $useragent);
        } elseif (false !== strpos($useragent, 'Safari') && false !== strpos($useragent, 'Version') && false !== strpos(
            $useragent,
            'Tizen'
        )
        ) {
            list($browser) = $loader->load('samsung webview', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Chromium')) {
            list($browser) = $loader->load('chromium', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Flock')) {
            list($browser) = $loader->load('flock', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Fluid')) {
            list($browser) = $loader->load('fluid', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'ChromePlus')) {
            list($browser) = $loader->load('coolnovo chrome plus', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'RockMelt')) {
            list($browser) = $loader->load('rockmelt', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Shiira')) {
            list($browser) = $loader->load('shiira', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Iron')) {
            list($browser) = $loader->load('iron', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Chrome')) {
            list($browser)       = $loader->load('chrome', $useragent);
            $browserVersion      = (string) $chromeVersion;

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'CriOS')) {
            list($browser) = $loader->load('chrome', $useragent);

            if (preg_match('/CriOS\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'OPiOS')) {
            list($browser) = $loader->load('opera mini', $useragent);
        } elseif (false !== strpos($useragent, 'Opera Mini')) {
            list($browser) = $loader->load('opera mini', $useragent);
        } elseif (false !== strpos($useragent, 'FlyFlow')) {
            list($browser) = $loader->load('flyflow', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Epiphany') || false !== strpos($useragent, 'epiphany')) {
            list($browser) = $loader->load('epiphany', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'GSA')) {
            list($browser) = $loader->load('google app', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Safari')
            && false !== strpos($useragent, 'Version')
            && false !== strpos($useragent, 'Android')
        ) {
            list($browser) = $loader->load('android webkit', $useragent);

            if (preg_match('/Version\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion !== '4.0') {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'BlackBerry') && false !== strpos($useragent, 'Version')) {
            list($browser) = $loader->load('blackberry', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Safari') && false !== strpos($useragent, 'Version')) {
            list($browser) = $loader->load('safari', $useragent);
        } elseif (false !== strpos($useragent, 'PaleMoon')) {
            list($browser) = $loader->load('palemoon', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Phoenix')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Phoenix
            $browser = null;
        } elseif (false !== stripos($useragent, 'Prism')) {
            list($browser) = $loader->load('prism', $useragent);
            $lite          = false;
        } elseif (false !== stripos($useragent, 'QtWeb Internet Browser')) {
            list($browser) = $loader->load('qtweb internet browser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Waterfox')) {
            list($browser) = $loader->load('waterfox', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'QupZilla')) {
            list($browser) = $loader->load('qupzilla', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Thunderbird')) {
            list($browser) = $loader->load('thunderbird', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'kontact')) {
            list($browser) = $loader->load('kontact', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Fennec')) {
            list($browser) = $loader->load('fennec', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'myibrow')) {
            list($browser) = $loader->load('my internet browser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Daumoa')) {
            list($browser) = $loader->load('daumoa', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Camino')) {
            list($browser) = $loader->load('camino', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Cheshire')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Cheshire
            $browser = null;
        } elseif (false !== strpos($useragent, 'Classilla')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Classilla
            $browser = null;
        } elseif (false !== strpos($useragent, 'CometBird')) {
            list($browser) = $loader->load('cometbird', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'EnigmaFox')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add EnigmaFox
            $browser = null;
        } elseif (false !== strpos($useragent, 'conkeror') || false !== strpos($useragent, 'Conkeror')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Conkeror
            $browser = null;
        } elseif (false !== strpos($useragent, 'Galeon')) {
            list($browser) = $loader->load('galeon', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Hana')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Hana
            $browser = null;
        } elseif (false !== strpos($useragent, 'Iceape')) {
            list($browser) = $loader->load('iceape', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'IceCat')) {
            list($browser) = $loader->load('icecat', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Iceweasel')) {
            list($browser) = $loader->load('iceweasel', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'K-Meleon')) {
            list($browser) = $loader->load('k-meleon', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'K-Ninja')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add K-Ninja
            $browser = null;
        } elseif (false !== strpos($useragent, 'Kapiko')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Kapiko
            $browser = null;
        } elseif (false !== strpos($useragent, 'Kazehakase')) {
            list($browser) = $loader->load('kazehakase', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'KMLite')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add KMLite
            $browser = null;
        } elseif (false !== strpos($useragent, 'lolifox')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add lolifox
            $browser = null;
        } elseif (false !== strpos($useragent, 'Konqueror')) {
            list($browser) = $loader->load('konqueror', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Leechcraft')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Leechcraft
            $browser = null;
        } elseif (false !== strpos($useragent, 'Madfox')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Madfox
            $browser = null;
        } elseif (false !== strpos($useragent, 'Netscape6')) {
            list($browser) = $loader->load('netscape', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Netscape')) {
            list($browser) = $loader->load('netscape', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Navigator')) {
            list($browser) = $loader->load('netscape navigator', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Orca')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Orca
            $browser = null;
        } elseif (false !== strpos($useragent, 'Sylera')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Sylera
            $browser = null;
        } elseif (false !== strpos($useragent, 'SeaMonkey')) {
            list($browser) = $loader->load('seamonkey', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'GoBrowser')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add GoBrowser
            $browser = null;
        } elseif (false !== strpos($useragent, 'Minimo')) {
            list($browser) = $loader->load('minimo', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'BonEcho')) {
            list($browser) = $loader->load('firefox', $useragent);

            if (preg_match('/BonEcho\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'Shiretoko')) {
            list($browser) = $loader->load('firefox', $useragent);

            if (preg_match('/Shiretoko\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'Minefield')) {
            list($browser) = $loader->load('firefox', $useragent);

            if (preg_match('/Minefield\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'Namoroka')) {
            list($browser) = $loader->load('firefox', $useragent);

            if (preg_match('/Namoroka\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'GranParadiso')) {
            list($browser) = $loader->load('firefox', $useragent);

            if (preg_match('/GranParadiso\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'Firebird')) {
            list($browser) = $loader->load('firefox', $useragent);

            if (preg_match('/Firebird\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== stripos($useragent, 'firefox')) {
            list($browser) = $loader->load('firefox', $useragent);

            if (preg_match('/Firefox\/(\d+\.\d+)/', $useragent, $matches)) {
                $browserVersion = $matches[1];
            }

            if ($browserVersion < 30) {
                $lite = false;
            }
        } elseif (false !== strpos($useragent, 'FxiOS')) {
            list($browser) = $loader->load('firefox for ios', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Browzar')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Browzar
            $browser = null;
        } elseif (false !== strpos($useragent, 'Crazy Browser')) {
            list($browser) = $loader->load('crazy browser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'GreenBrowser')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add GreenBrowser
            $browser = null;
        } elseif (false !== strpos($useragent, 'KKman')) {
            list($browser) = $loader->load('kkman', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Lobo')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Lobo
            $browser = null;
        } elseif (false !== strpos($useragent, 'Sleipnir')) {
            list($browser) = $loader->load('sleipnir', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'SlimBrowser')) {
            list($browser) = $loader->load('slimbrowser', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'TencentTraveler')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add TencentTraveler
            $browser = null;
        } elseif (false !== strpos($useragent, 'TheWorld')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add TheWorld
            $browser = null;
        } elseif (false !== strpos($useragent, 'MSIE')) {
            list($browser) = $loader->load('internet explorer', $useragent);
        } elseif (false !== strpos($useragent, 'like Gecko') && false !== strpos($useragent, 'rv:11.0')) {
            list($browser) = $loader->load('internet explorer', $useragent);
        } elseif (false !== strpos($useragent, 'SMTBot')) {
            list($browser) = $loader->load('smtbot', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'gvfs')) {
            list($browser) = $loader->load('gvfs', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'luakit')) {
            list($browser) = $loader->load('luakit', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Cyberdog')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Cyberdog
            $browser = null;
        } elseif (false !== strpos($useragent, 'ELinks')) {
            list($browser) = $loader->load('elinks', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Links')) {
            list($browser) = $loader->load('links', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Galaxy')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Galaxy
            $browser = null;
        } elseif (false !== strpos($useragent, 'iNet Browser')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add iNet Browser
            $browser = null;
        } elseif (false !== strpos($useragent, 'Dalvik')) {
            list($browser) = $loader->load('dalvik', $useragent);
            $lite          = false;
        } elseif (false !== strpos($useragent, 'Uzbl')) {
            //list($browser) = $loader->load('opera mobile', $useragent);
            //$lite = false;
            //@todo add Uzbl
            $browser = null;
        } else {
            /** @var \UaResult\Result\Result $result */
            try {
                $result = $detector->getBrowser($useragent);

                $browser = $result->getBrowser();

                if ($browserName !== $browser->getName()) {
                    $browser = null;
                }
            } catch (\Exception $e) {
                $browser = null;
            }
        }

        if (null === $browser) {
            $browser = new \UaResult\Browser\Browser($browserName);
        }

        return [
            $browser,
            $lite,
        ];
    }
}
