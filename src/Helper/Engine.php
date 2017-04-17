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

use BrowserDetector\Factory\EngineFactory;
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
     * @return \UaResult\Engine\Engine
     */
    public function detect(CacheItemPoolInterface $cache, $useragent)
    {
        $loader = new EngineLoader($cache);

        /* @var \UaResult\Engine\Engine $engine */
        try {
            $engine = (new EngineFactory($loader))->detect($useragent);
        } catch (\Exception $e) {
            $engine = null;
        }

        if (null === $engine || in_array($engine->getName(), [null, 'unknown'])) {
            $engine = new \UaResult\Engine\Engine(null);
        }

        return $engine;
    }
}
