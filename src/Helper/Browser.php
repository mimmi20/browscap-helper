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

use BrowserDetector\Factory\BrowserFactory;
use BrowserDetector\Loader\BrowserLoader;
use Psr\Cache\CacheItemPoolInterface;

/**
 * Class Browser
 *
 * @category   Browscap Helper
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 */
class Browser
{
    /**
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param string                            $useragent
     *
     * @return \UaResult\Browser\Browser
     */
    public function detect(CacheItemPoolInterface $cache, $useragent)
    {
        $loader = new BrowserLoader($cache);

        $browser        = null;
        $browserVersion = null;

        /* @var \UaResult\Browser\Browser $browser */
        try {
            list($browser) = (new BrowserFactory($loader))->detect($useragent);
        } catch (\Exception $e) {
            $browser = null;
        }

        if (null === $browser || in_array($browser->getName(), [null, 'unknown'])) {
            $browser = new \UaResult\Browser\Browser(null);
        }

        return $browser;
    }
}
