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

use BrowserDetector\Version\Version;
use BrowserDetector\Version\VersionFactory;
use BrowserDetector\Version\VersionInterface;

/**
 * class with caching and update capabilities
 *
 * @category  ua-data-mapper
 *
 * @author    Thomas Mueller <mimmi20@live.de>
 * @copyright 2015-2017 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
class BrowserVersionMapper
{
    /**
     * maps the browser version
     *
     * @param string|null $browserVersion
     * @param string|null $browserName
     *
     * @return \BrowserDetector\Version\VersionInterface
     */
    public function mapBrowserVersion(?string $browserVersion = null, ?string $browserName = null): VersionInterface
    {
        if (null === $browserVersion) {
            return new Version('0');
        }

        switch (mb_strtolower($browserVersion)) {
            case '':
            case 'unknown':
            case 'other':
                return new Version('0');
                break;
            default:
                if (null === $browserName) {
                    return new Version('0');
                }

                switch (mb_strtolower($browserName)) {
                    case '':
                    case 'unknown':
                    case 'other':
                        return new Version('0');
                        break;
                    default:
                        // nothing to do here
                        break;
                }
                break;
        }

        return VersionFactory::set($browserVersion);
    }
}
