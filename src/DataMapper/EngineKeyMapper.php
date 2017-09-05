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
class EngineKeyMapper
{
    /**
     * maps the name of the operating system
     *
     * @param string $engineName
     *
     * @return string
     */
    public function mapEngineKey(string $engineName): string
    {
        switch (mb_strtolower($engineName)) {
            case '':
            case 'unknown':
            case 'other':
                return 'unknown';
                break;
            case 'webkit':
                return 'webkit';
                break;
            case 'gecko':
                return 'gecko';
                break;
            case 'trident':
                return 'trident';
                break;
            case 'edge':
                return 'edge';
                break;
            case 'presto':
                return 'presto';
                break;
            case 'netfront':
                return 'netfront';
                break;
            case 't5':
                return 't5';
                break;
            case 'tasman':
                return 'tasman';
                break;
            case 'khtml':
                return 'khtml';
                break;
            case 'u2':
                return 'u2';
                break;
            case 'u3':
                return 'u3';
                break;
            case 'blink':
                return 'blink';
                break;
            case 'goanna':
                return 'goanna';
                break;
            case 'clecko':
                return 'clecko';
                break;
            default:
                // nothing to do here
                break;
        }

        return 'unknown';
    }
}
