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

use BrowserDetector\Detector;
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
     * @param \BrowserDetector\Detector         $detector
     * @param string                            $engineName
     *
     * @return array
     */
    public function detect(
        CacheItemPoolInterface $cache,
        $useragent,
        Detector $detector,
        $engineName
    ) {
        $loader = new EngineLoader($cache);

        $applets = false;
        $activex = false;
        $engine  = null;

        $chromeVersion = 0;

        if (false !== mb_strpos($useragent, 'Chrome')) {
            if (preg_match('/Chrome\/(\d+\.\d+)/', $useragent, $matches)) {
                $chromeVersion = (float) $matches[1];
            }
        }

        if (false !== mb_strpos($useragent, ' U3/')) {
            $engine = $loader->load('u3', $useragent);
        } elseif (false !== mb_strpos($useragent, ' U2/')) {
            $engine = $loader->load('u2', $useragent);
        } elseif (false !== mb_strpos($useragent, ' T5/')) {
            $engine = $loader->load('t5', $useragent);
        } elseif (false !== mb_strpos($useragent, 'AppleWebKit')) {
            if ($chromeVersion >= 28.0) {
                $engine = $loader->load('blink', $useragent);
            } else {
                $engine      = $loader->load('webkit', $useragent);
                $applets     = true;
            }
        } elseif (false !== mb_strpos($useragent, 'Presto')) {
            $engine = $loader->load('presto', $useragent);
        } elseif (false !== mb_strpos($useragent, 'Trident')) {
            $engine      = $loader->load('trident', $useragent);
            $applets     = true;
            $activex     = true;
        } elseif (false !== mb_strpos($useragent, 'Gecko')) {
            $engine      = $loader->load('gecko', $useragent);
            $applets     = true;
        } else {
            /* @var \UaResult\Result\Result $result */
            try {
                $result = $detector->getBrowser($useragent);
                $engine = $result->getEngine();

                if ($engineName !== $engine->getName()) {
                    $engine = null;
                }
            } catch (\Exception $e) {
                $engine = null;
            }
        }

        if (null === $engine) {
            $engine = $loader->load('unknown', $useragent);
        }

        return [
            $engine,
            $applets,
            $activex,
        ];
    }
}
