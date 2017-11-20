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

use UaBrowserType\TypeInterface;
use UaBrowserType\TypeLoader;

/**
 * class with caching and update capabilities
 *
 * @category  ua-data-mapper
 *
 * @author    Thomas Mueller <mimmi20@live.de>
 * @copyright 2015-2017 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
class BrowserTypeMapper
{
    /**
     * maps the browser type
     *
     * @param string $browserType
     *
     * @return \UaBrowserType\TypeInterface
     */
    public function mapBrowserType(string $browserType): TypeInterface
    {
        switch (mb_strtolower($browserType)) {
            case 'browser':
            case 'mobile browser':
                $typeKey = 'browser';

                break;
            case 'bot':
            case 'robot':
            case 'bot/crawler':
            case 'library':
                $typeKey = 'bot';

                break;
            case 'emailclient':
            case 'email client':
                $typeKey = 'email-client';

                break;
            case 'pim':
                $typeKey = 'pim';

                break;
            case 'feedreader':
            case 'feed reader':
                $typeKey = 'feed-reader';

                break;
            case 'multimediaplayer':
            case 'mediaplayer':
            case 'multimedia player':
                $typeKey = 'multimedia-player';

                break;
            case 'offlinebrowser':
            case 'offline browser':
                $typeKey = 'offline-browser';

                break;
            case 'useragentanonymizer':
            case 'useragent anonymizer':
                $typeKey = 'useragent-anonymizer';

                break;
            case 'wapbrowser':
            case 'wap browser':
                $typeKey = 'wap-browser';

                break;
            case 'application':
            case 'mobile app':
                $typeKey = 'application';

                break;
            case 'tool':
                $typeKey = 'tool';

                break;
            default:
                $typeKey = 'unknown';

                break;
        }

        return TypeLoader::getInstance()->load($typeKey);
    }
}
