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
class MakerMapper
{
    /**
     * maps the maker of the browser, os, engine or device
     *
     * @param string $maker
     *
     * @return string|null
     */
    public function mapMaker(string $maker): ?string
    {
        switch (mb_strtolower(trim($maker))) {
            case '':
            case 'unknown':
            case 'other':
            case 'bot':
            case 'various':
                $maker = null;

                break;
            case 'microsoft':
            case 'microsoft corporation.':
                $maker = 'Microsoft';

                break;
            case 'apple':
            case 'apple inc.':
            case 'apple computer, inc.':
                $maker = 'Apple';

                break;
            case 'google':
            case 'google inc.':
            case 'google, inc.':
                $maker = 'Google';

                break;
            case 'lunascape & co., ltd.':
                $maker = 'Lunascape';

                break;
            case 'opera software asa.':
                $maker = 'Opera';

                break;
            case 'sun microsystems, inc.':
                $maker = 'Oracle';

                break;
            case 'postbox, inc.':
                $maker = 'Postbox';

                break;
            case 'comodo group, inc.':
                $maker = 'Comodo';

                break;
            case 'canonical ltd.':
                $maker = 'Canonical';

                break;
            case 'gentoo foundation, inc.':
                $maker = 'Gentoo';

                break;
            case 'omni development, inc.':
                $maker = 'OmniDevelopment';

                break;
            case 'slackware linux, inc.':
                $maker = 'Slackware';

                break;
            case 'red hat, inc.':
                $maker = 'Redhat';

                break;
            case 'rim':
                $maker = 'Rim';

                break;
            case 'mozilla':
                $maker = 'MozillaFoundation';

                break;
            case 'majestic-12':
                $maker = 'Majestic12';

                break;
            case 'zum internet':
                $maker = 'ZuminternetCorp';

                break;
            case 'mojeek ltd.':
                $maker = 'OnlineMediaGroup';

                break;
            case 'online media group, inc.':
                $maker = 'OnlineMediaGroup';

                break;
            case 'hp':
                $maker = 'Hp';

                break;
            case 'goclever':
                $maker = 'GoClever';

                break;
            case 'dns':
                $maker = 'Dns';

                break;
            case 'cat':
                $maker = 'CatSound';

                break;
            case 'bq':
                $maker = 'Bq';

                break;
            case 'barnes & noble':
                $maker = 'BarnesNoble';

                break;
            case '3q':
                $maker = 'TriQ';

                break;
            case 'texet':
                $maker = 'Texet';

                break;
            case 'vastking':
                $maker = 'VastKing';

                break;
            case 'nextbook':
                $maker = 'Nextbook';

                break;
            case 'msi':
                $maker = 'Msi';

                break;
            case 'mpman':
                $maker = 'MPMan';

                break;
            case 'lg':
                $maker = 'Lg';

                break;
            case 'jay-tech':
                $maker = 'Jaytech';

                break;
            case 'garmin-asus':
                $maker = 'GarminAsus';

                break;
            case 'cubot':
                $maker = 'Cubot';

                break;
            case 'zte':
                $maker = 'Zte';

                break;
            case 'xiaomi':
                $maker = 'XiaomiTech';

                break;
            case 't-mobile':
                $maker = 'Tmobile';

                break;
            case 'tcl':
                $maker = 'Tcl';

                break;
            case 'tecno mobile':
                $maker = 'Tecno';

                break;
            case 'sony ericsson':
                $maker = 'SonyEricsson';

                break;
            case 'qmobile':
                $maker = 'Qmobile';

                break;
            case 'pulid':
                $maker = 'Pulid';

                break;
            case 'oppo':
                $maker = 'Oppo';

                break;
            case 'oneplus':
                $maker = 'Oneplus';

                break;
            case 'ngm':
                $maker = 'Ngm';

                break;
            case 'coby kyros':
                $maker = 'Coby';

                break;
            case 'point of view':
                $maker = 'PointOfView';

                break;
            case 'thl':
                $maker = 'Thl';

                break;
            case 'micromax':
                $maker = 'Micromax';

                break;
            case 'k-touch':
                $maker = 'Ktouch';

                break;
            case 'i-mobile':
                $maker = 'Imobile';

                break;
            case 'htc':
                $maker = 'Htc';

                break;
            case 'the internet archive':
                $maker = 'ArchiveOrg';

                break;
            case 'wordpress.org':
                $maker = 'WordPress';

                break;
            case 'reddit inc.':
                $maker = 'Reddit';

                break;
            case 'yandex llc':
                $maker = 'Yandex';

                break;
            case 'ahrefs pte ltd':
                $maker = 'Ahrefs';

                break;
            case 'w3c':
                $maker = 'W3c';

                break;
            case 'seznam.cz, a.s.':
                $maker = 'Seznam';

                break;
            case 'semrush':
                $maker = 'Semrush';

                break;
            case 'sistrix gmbh':
                $maker = 'Sistrix';

                break;
            case 'seomoz, inc.':
                $maker = 'SeoMoz';

                break;
            case 'the apache software foundation':
                $maker = 'Apache';

                break;
            case 'daum communications corp.':
                $maker = 'DaumCorporation';

                break;
            default:
                // nothing to do here
                break;
        }

        return $maker;
    }
}
