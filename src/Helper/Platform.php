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

use BrowserDetector\Factory;
use BrowserDetector\Loader\PlatformLoader;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class Platform
 *
 * @category   Browscap Helper
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 */
class Platform
{
    /**
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param string                            $useragent
     *
     * @throws \BrowserDetector\Loader\NotFoundException
     * @throws \UnexpectedValueException
     *
     * @return \UaResult\Os\Os
     */
    public function detect(CacheItemPoolInterface $cache, $useragent)
    {
        $platformLoader = new PlatformLoader($cache);

        /* @var \UaResult\Os\Os $platform */
        try {
            $platform = (new Factory\PlatformFactory($platformLoader))->detect($useragent);
        } catch (\Exception $e) {
            $platform = null;
        }

        if (null === $platform || in_array($platform->getName(), [null, 'unknown'])) {
            $platform = new \UaResult\Os\Os(null, null);
        }

        return $platform;
    }
}
