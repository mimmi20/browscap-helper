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
class EngineNameMapper
{
    /**
     * maps the name of the operating system
     *
     * @param string $engineName
     *
     * @return string|null
     */
    public function mapEngineName(string $engineName): ?string
    {
        switch (mb_strtolower($engineName)) {
            case '':
            case 'unknown':
            case 'other':
                $engineName = null;

                break;
            default:
                // nothing to do here
                break;
        }

        return $engineName;
    }
}
