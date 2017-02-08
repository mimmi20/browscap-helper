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

use BrowserDetector\Loader\EngineLoader;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class Engine
{
    /**
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param string                            $useragent
     *
     * @return array
     */
    public function detect(
        CacheItemPoolInterface $cache,
        $useragent
    ) {
        $loader = new EngineLoader($cache);

        $applets = false;
        $activex = false;

        $chromeVersion = 0;

        if (false !== strpos($useragent, 'Chrome')) {
            if (preg_match('/Chrome\/(\d+\.\d+)/', $useragent, $matches)) {
                $chromeVersion = (float) $matches[1];
            }
        }

        if (false !== strpos($useragent, ' U3/')) {
            $engine = $loader->load('u3');
        } elseif (false !== strpos($useragent, ' U2/')) {
            $engine = $loader->load('u2');
        } elseif (false !== strpos($useragent, ' T5/')) {
            $engine = $loader->load('t5');
        } elseif (false !== strpos($useragent, 'AppleWebKit')) {
            if ($chromeVersion >= 28.0) {
                $engine = $loader->load('blink');
            } else {
                $engine      = $loader->load('webkit');
                $applets     = true;
            }
        } elseif (false !== strpos($useragent, 'Presto')) {
            $engine = $loader->load('presto');
        } elseif (false !== strpos($useragent, 'Trident')) {
            $engine      = $loader->load('trident');
            $applets     = true;
            $activex     = true;
        } elseif (false !== strpos($useragent, 'Gecko')) {
            $engine      = $loader->load('gecko');
            $applets     = true;
        } else {
            $engine = $loader->load('unknown');
        }

        return [
            $engine,
            $applets,
            $activex,
        ];
    }
}
