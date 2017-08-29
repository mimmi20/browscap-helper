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
namespace BrowscapHelper\Module\Check;

use GuzzleHttp\Psr7\Response;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;

/**
 * BrowscapHelper.ini parsing class with caching and update capabilities
 *
 * @category  BrowscapHelper
 *
 * @author    Thomas Mueller <mimmi20@live.de>
 * @copyright 2015 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
interface CheckInterface
{
    /**
     * @param \GuzzleHttp\Psr7\Response          $response
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Psr\Cache\CacheItemPoolInterface  $cache
     * @param \Psr\Log\LoggerInterface           $logger
     * @param string                             $agent
     *
     * @return mixed
     */
    public function getResponse(
        Response $response,
        RequestInterface $request,
        CacheItemPoolInterface $cache,
        LoggerInterface $logger,
        string $agent
    );
}
