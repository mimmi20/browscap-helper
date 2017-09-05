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
class BrowserNameMapper
{
    /**
     * mapps the browser
     *
     * @param string|null $browserInput
     *
     * @throws \UnexpectedValueException
     *
     * @return string|null
     */
    public function mapBrowserName(string $browserInput): ?string
    {
        $browserName = $browserInput;

        switch (mb_strtolower($browserInput)) {
            case 'unknown':
            case 'other':
            case 'default browser':
            case 'generic':
            case 'misc crawler':
            case 'generic bot':
            case 'http library':
                $browserName = null;
                break;
            case 'ie':
            case 'msie':
                $browserName = 'Internet Explorer';
                break;
            case 'iceweasel':
                $browserName = 'Iceweasel';
                break;
            case 'mobile safari':
                $browserName = 'Safari';
                break;
            case 'chrome mobile':
            case 'chrome mobile ios':
            case 'chrome frame':
                $browserName = 'Chrome';
                break;
            case 'android':
            case 'android browser':
                $browserName = 'Android Webkit';
                break;
            case 'googlebot':
                $browserName = 'Google Bot';
                break;
            case 'bingbot':
                $browserName = 'BingBot';
                break;
            case 'bingpreview':
                $browserName = 'Bing Preview';
                break;
            case 'jakarta commons-httpclient':
                $browserName = 'Jakarta Commons HttpClient';
                break;
            case 'adsbot-google':
                $browserName = 'AdsBot Google';
                break;
            case 'seokicks-robot':
                $browserName = 'SEOkicks Robot';
                break;
            case 'gomeza':
            case 'gomezagent':
                $browserName = 'Gomez Site Monitor';
                break;
            case 'yandex.browser':
                $browserName = 'Yandex Browser';
                break;
            case 'ie mobile':
                $browserName = 'IEMobile';
                break;
            case 'ovi browser':
                $browserName = 'Nokia Proxy Browser';
                break;
            case 'firefox mobile':
            case 'mobile firefox mobile':
            case 'mobile firefox tablet':
            case 'mobile firefox':
                $browserName = 'Firefox';
                break;
            case 'dolfin/jasmine webkit':
            case 'dolphin':
                $browserName = 'Dolfin';
                break;
            case 'facebookexternalhit':
            case 'facebook external hit':
            case 'facebookbot':
                $browserName = 'FaceBook Bot';
                break;
            case 'java':
                $browserName = 'Java Standard Library';
                break;
            case 'nokia web browser':
                $browserName = 'Nokia Browser';
                break;
            case 'applemail':
                $browserName = 'Apple Mail';
                break;
            case 'sistrix':
                $browserName = 'Sistrix Crawler';
                break;
            case 'blackberry webkit':
            case 'blackberry browser':
                $browserName = 'BlackBerry';
                break;
            case 'microsoft outlook':
                $browserName = 'Outlook';
                break;
            case 'outlook express':
            case 'microsoft outlook express':
                $browserName = 'Windows Live Mail';
                break;
            case 'microsoft office':
                $browserName = 'Office';
                break;
            case 'mj12 bot':
                $browserName = 'MJ12bot';
                break;
            case 'mobile silk':
            case 'amazon silk':
                $browserName = 'Silk';
                break;
            case 'genieo web filter':
                $browserName = 'Genieo Web Filter';
                break;
            case 'yahoo! slurp':
                $browserName = 'Slurp';
                break;
            case 'yandex bot':
                $browserName = 'YandexBot';
                break;
            case 'nutch-based bot':
            case 'apache nutch':
                $browserName = 'Nutch';
                break;
            case 'baidu spider':
                $browserName = 'Baiduspider';
                break;
            case 'semrush bot':
                $browserName = 'SemrushBot';
                break;
            case 'python urllib':
                $browserName = 'Python-urllib';
                break;
            case 'mail.ru bot':
                $browserName = 'Mail.Ru';
                break;
            case 'nokia/s40ovi':
            case 'nokia ovi browser':
                $browserName = 'Nokia Proxy Browser';
                break;
            case 'sistrix crawler':
                $browserName = 'Sistrix Crawler';
                break;
            case 'exabot':
                $browserName = 'Exabot';
                break;
            case 'curl':
                $browserName = 'cURL';
                break;
            case 'pale moon (firefox variant)':
            case 'pale moon':
                $browserName = 'PaleMoon';
                break;
            case 'opera next':
                $browserName = 'Opera';
                break;
            case 'yeti/naverbot':
                $browserName = 'NaverBot';
                break;
            case 'ahrefs bot':
                $browserName = 'AhrefsBot';
                break;
            case 'picsearch bot':
                $browserName = 'Picsearch Bot';
                break;
            case 'androiddownloadmanager':
                $browserName = 'Android Download Manager';
                break;
            case 'elinks':
                $browserName = 'ELinks';
                break;
            case 'whitehat aviator':
                $browserName = 'Aviator';
                break;
            case 'fake browser':
            case 'fake ie':
            case 'fake chrome':
            case 'fake safari':
            case 'fake firefox':
            case 'fake android':
                $browserName = 'Fake Browser';
                break;
            case 'wdg html validator':
                $browserName = 'HTML Validator';
                break;
            case 'blekkobot':
                $browserName = 'BlekkoBot';
                break;
            case 'tweetmemebot':
            case 'tweetmeme bot':
                $browserName = 'Tweetmeme Bot';
                break;
            case 'coremedia':
            case 'applecoremedia':
                $browserName = 'CoreMedia';
                break;
            case 'mediapartners-google':
            case 'google mediapartners':
                $browserName = 'AdSense Bot';
                break;
            case 'wordpress.com':
                $browserName = 'WordPress';
                break;
            case 'up.browser':
            case 'au by kddi':
                $browserName = 'Openwave Mobile Browser';
                break;
            case 'qqbrowser':
                $browserName = 'QQ Browser';
                break;
            case 'wosbrowser':
            case 'webkit/webos':
                $browserName = 'webOS Browser';
                break;
            default:
                // nothing to do here
                break;
        }

        return $browserName;
    }
}
