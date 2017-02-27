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
namespace BrowscapHelper\Helper;

use BrowserDetector\Loader\EngineLoader;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class Engine
 *
 * @category   Browscap Helper
 *
 * @author     Thomas Mueller <mimmi20@live.de>
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

        if (false !== mb_strpos($useragent, 'Chrome')) {
            if (preg_match('/Chrome\/(\d+\.\d+)/', $useragent, $matches)) {
                $chromeVersion = (float) $matches[1];
            }
        }

        if (false !== mb_strpos($useragent, ' U3/')) {
            $engine = $loader->load('u3');
        } elseif (false !== mb_strpos($useragent, ' U2/')) {
            $engine = $loader->load('u2');
        } elseif (false !== mb_strpos($useragent, ' T5/')) {
            $engine = $loader->load('t5');
        } elseif (false !== mb_strpos($useragent, 'AppleWebKit')) {
            if ($chromeVersion >= 28.0) {
                $engine = $loader->load('blink');
            } else {
                $engine      = $loader->load('webkit');
                $applets     = true;
            }
        } elseif (false !== mb_strpos($useragent, 'Presto')) {
            $engine = $loader->load('presto');
        } elseif (false !== mb_strpos($useragent, 'Trident')) {
            $engine      = $loader->load('trident');
            $applets     = true;
            $activex     = true;
        } elseif (false !== mb_strpos($useragent, 'Gecko')) {
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
