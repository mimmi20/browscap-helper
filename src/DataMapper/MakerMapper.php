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
                $maker = 'Microsoft Corporation';

                break;
            case 'apple':
            case 'apple inc.':
            case 'apple computer, inc.':
                $maker = 'Apple Inc';

                break;
            case 'google':
            case 'google inc.':
            case 'google, inc.':
                $maker = 'Google Inc';

                break;
            case 'lunascape & co., ltd.':
                $maker = 'Lunascape Corporation';

                break;
            case 'opera software asa.':
                $maker = 'Opera Software ASA';

                break;
            case 'sun microsystems, inc.':
                $maker = 'Oracle';

                break;
            case 'postbox, inc.':
                $maker = 'Postbox Inc';

                break;
            case 'comodo group, inc.':
                $maker = 'Comodo Group Inc';

                break;
            case 'canonical ltd.':
                $maker = 'Canonical Ltd';

                break;
            case 'gentoo foundation, inc.':
                $maker = 'Gentoo Foundation Inc';

                break;
            case 'omni development, inc.':
                $maker = 'Omni Development Inc';

                break;
            case 'slackware linux, inc.':
                $maker = 'Slackware Linux Inc';

                break;
            case 'red hat, inc.':
                $maker = 'Red Hat Inc';

                break;
            case 'rim':
                $maker = 'Research In Motion Limited';

                break;
            case 'mozilla':
                $maker = 'Mozilla Foundation';

                break;
            case 'majestic-12':
                $maker = 'Majestic-12 Ltd';

                break;
            case 'zum internet':
                $maker = 'ZUMinternet Corp';

                break;
            case 'mojeek ltd.':
                $maker = 'Linkdex Limited';

                break;
            default:
                // nothing to do here
                break;
        }

        return $maker;
    }
}
