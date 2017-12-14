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
            case 'microsoft corporation':
                $maker = 'Microsoft';

                break;
            case 'apple':
            case 'apple inc.':
            case 'apple computer, inc.':
            case 'apple inc':
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
                $maker = 'Mojeek';

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
            case 'aboundex.com':
                $maker = 'Aboundex';

                break;
            case 'acoon gmbh':
                $maker = 'Acoon';

                break;
            case 'clearspring technologies, inc.':
                $maker = 'Clearspring';

                break;
            case 'alexa internet':
                $maker = 'AlexaInternet';

                break;
            case 'amorank':
                $maker = 'Amorank';

                break;
            case 'analytics seo':
                $maker = 'AnalyticsSEO';

                break;
            case 'webmeup':
                $maker = 'WebmeupCrawlerCom';

                break;
            case 'the laboratory for web algorithmics (law)':
                $maker = 'LAW';

                break;
            case 'mediagreen medienservice':
                $maker = 'Mediagreen';

                break;
            case '2.0promotion gbr':
                $maker = '2.0Promotion';

                break;
            case 'baidu':
                $maker = 'Baidu';

                break;
            case 'bitly, inc.':
                $maker = 'Bitly';

                break;
            case 'blekko':
                $maker = 'BlekkoCom';

                break;
            case 'blogtrottr ltd':
                $maker = 'Blogtrottr';

                break;
            case 'bountii inc.':
                $maker = 'Bountii';

                break;
            case 'browsershots.org':
                $maker = 'Browsershots';

                break;
            case 'topsy labs':
                $maker = 'TopsyLabs';

                break;
            case 'career-x gmbh':
                $maker = 'Careerx';

                break;
            case 'supertop':
                $maker = 'Supertop';

                break;
            case '10betterpages gmbh':
                $maker = 'Tenbetterpages';

                break;
            case 'cloudflare':
                $maker = 'CloudFlare';

                break;
            case 'cốc cốc':
                $maker = 'CocCocCompany';

                break;
            case 'datadog':
                $maker = 'Datadog';

                break;
            case 'dataprovider b.v.':
                $maker = 'Dataprovider';

                break;
            case 'dazoo.fr':
                $maker = 'DAZOO.FR';

                break;
            case 'discovery engine':
                $maker = 'DiscoveryEngine';

                break;
            case 'domain re-animator, llc':
                $maker = 'Domain Re-Animator';

                break;
            case 'duckduckgo':
                $maker = 'DuckDuckGo';

                break;
            case 'easou icp':
                $maker = 'Easou';

                break;
            case 'dassault systèmes':
                $maker = 'DassaultSystemes';

                break;
            case 'jayde online, inc.':
                $maker = 'JaydeOnline';

                break;
            case 'facebook':
                $maker = 'Facebook';

                break;
            case 'david smith & developing perspective, llc':
                $maker = 'DavidSmith';

                break;
            case 'flipboard':
                $maker = 'Flipboard';

                break;
            case 'genieo':
                $maker = 'Genieo';

                break;
            case 'matt wells':
                $maker = 'MattWells';

                break;
            case 'ntt resonant':
                $maker = 'NttResonant';

                break;
            case 'grapeshot':
                $maker = 'GrapeshotLimited';

                break;
            case 'towards gmbh':
                $maker = 'towards';

                break;
            case 'heureka.cz, a.s.':
                $maker = 'Heureka';

                break;
            case 'hubpages':
                $maker = 'HubPages Inc.';

                break;
            case 'let\'s encrypt':
                $maker = 'LetsEncrypt';

                break;
            case 'linkedin':
                $maker = 'LinkedIn';

                break;
            case 'brandwatch':
                $maker = 'Brandwatch';

                break;
            case 'mail.ru group':
                $maker = 'MailRu';

                break;
            case 'meanpath':
                $maker = 'Meanpath';

                break;
            case 'metajob':
                $maker = 'MetaJob';

                break;
            case 'lavtech.com corp.':
                $maker = 'Lavtech';

                break;
            case 'monitor.us':
                $maker = 'MonitorUs';

                break;
            case 'munin':
                $maker = 'Munin';

                break;
            case 'northern light':
                $maker = 'Northern Light';

                break;
            case 'jaroslav kuboš':
                $maker = 'JaroslavKubos';

                break;
            case 'netcraft':
                $maker = 'Netcraft Ltd.';

                break;
            case 'nmap':
                $maker = 'Nmap';

                break;
            case 'omgili':
                $maker = 'Omgili';

                break;
            case 'axandra gmbh':
                $maker = 'Axandra';

                break;
            case 'openwebspider lab':
                $maker = 'OpenWebSpider';

                break;
            case 'openindex b.v.':
                $maker = 'Openindex';

                break;
            case 'orange':
                $maker = 'Orange';

                break;
            case 'outbrain':
                $maker = 'Outbrain';

                break;
            case 'php server monitor':
                $maker = 'PHP Server Monitor';

                break;
            case 'smallrivers sa':
                $maker = 'Smallrivers';

                break;
            case 'picsearch':
                $maker = 'Picsearch';

                break;
            case 'pingdom ab':
                $maker = 'Pingdom';

                break;
            case 'pinterest':
                $maker = 'Pinterest';

                break;
            case 'pocket':
                $maker = 'Pocket';

                break;
            case 'bitlove':
                $maker = 'Bitlove';

                break;
            case 'queryeye inc.':
                $maker = 'QueryEye';

                break;
            case 'qwant corporation':
                $maker = 'Qwant';

                break;
            case 'roihunter a.s.':
                $maker = 'Roihunter';

                break;
            case 'seo engine':
                $maker = 'SEO Engine';

                break;
            case 'seokicks':
                $maker = 'SEOkicks';

                break;
            case 'ssl labs':
                $maker = 'SSL Labs';

                break;
            case 'safedns, inc.':
                $maker = 'SafeDNS';

                break;
            case 'screaming frog ltd':
                $maker = 'Screaming Frog';

                break;
            case 'sensika':
                $maker = 'Sensika';

                break;
            case 'visual meta':
                $maker = 'Visual Meta';

                break;
            case 'shopwiki corp.':
                $maker = 'ShopWiki';

                break;
            case 'site24x7':
                $maker = 'Site24x7';

                break;
            case 'manuel kasper':
                $maker = 'Manuel Kasper';

                break;
            case 'skype communications s.à.r.l.':
                $maker = 'Skype';

                break;
            case 'slack technologies':
                $maker = 'Slack';

                break;
            case 'sohu, inc.':
                $maker = 'Sohu';

                break;
            case 'tencent holdings':
                $maker = 'Tencent';

                break;
            case 'tailrank inc':
                $maker = 'Tailrank';

                break;
            case 'superfeedr':
                $maker = 'Superfeedr';

                break;
            case 'domain tools':
                $maker = 'Domain Tools';

                break;
            case 'venafi trustnet':
                $maker = 'Venafi TrustNet';

                break;
            case 'idée inc.':
                $maker = 'Idee';

                break;
            case 'talkwalker inc.':
                $maker = 'Talkwalker';

                break;
            case 'iparadigms, llc.':
                $maker = 'Iparadigms';

                break;
            case 'tweetedtimes':
                $maker = 'TweetedTimes';

                break;
            case 'mediasift':
                $maker = 'Mediasift';

                break;
            case 'twitter':
                $maker = 'Twitter';

                break;
            case 'profound networks':
                $maker = 'ProfoundNetworks';

                break;
            case 'kurt mckee':
                $maker = 'Kurt McKee';

                break;
            case 'uptime robot':
                $maker = 'Uptime Robot';

                break;
            case 'uptime':
                $maker = 'Uptime';

                break;
            case 'wiseguys':
                $maker = 'WiseGuysNl';

                break;
            case 'alentum software ltd.':
                $maker = 'Alentum Software';

                break;
            case 'aliasio':
                $maker = 'AliasIO';

                break;
            case 'wesee ltd':
                $maker = 'Wesee';

                break;
            case 'websitepulse':
                $maker = 'WebSitePulse';

                break;
            case 'steve webb':
                $maker = 'Steve Webb';

                break;
            case 'wotbox':
                $maker = 'Wotbox';

                break;
            case 'yacy':
                $maker = 'YaCy';

                break;
            case 'yahoo! inc.':
                $maker = 'Yahoo';

                break;
            default:
                // nothing to do here
                break;
        }

        return $maker;
    }
}
