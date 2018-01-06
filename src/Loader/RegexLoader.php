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
namespace BrowscapHelper\Loader;

use BrowserDetector\Cache\CacheInterface;
use Psr\Log\LoggerInterface;
use Seld\JsonLint\JsonParser;

/**
 * detection class using regexes
 *
 * @category  BrowserDetector
 *
 * @author    Thomas Mueller <mimmi20@live.de>
 * @copyright 2012-2017 Thomas Mueller
 * @license   http://www.opensource.org/licenses/MIT MIT License
 */
class RegexLoader
{
    private const CACHE_PREFIX = 'regex';

    /**
     * @var \BrowserDetector\Cache\CacheInterface
     */
    private $cache;

    /**
     * an logger instance
     *
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var self|null
     */
    private static $instance;

    /**
     * @param \BrowserDetector\Cache\CacheInterface $cache
     * @param \Psr\Log\LoggerInterface              $logger
     */
    private function __construct(CacheInterface $cache, LoggerInterface $logger)
    {
        $this->cache  = $cache;
        $this->logger = $logger;
    }

    /**
     * @param \BrowserDetector\Cache\CacheInterface $cache
     * @param \Psr\Log\LoggerInterface              $logger
     *
     * @return self
     */
    public static function getInstance(CacheInterface $cache, LoggerInterface $logger)
    {
        if (null === self::$instance) {
            self::$instance = new self($cache, $logger);
        }

        return self::$instance;
    }

    /**
     * @return void
     */
    public static function resetInstance(): void
    {
        self::$instance = null;
    }

    /**
     * initializes cache
     *
     * @throws \Seld\JsonLint\ParsingException
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @return void
     */
    private function init(): void
    {
        $initKey = $this->getCacheKey('initialized');

        if ($this->cache->hasItem($initKey) && $this->cache->getItem($initKey)) {
            return;
        }

        foreach ($this->getRegexes() as $regexKey => $data) {
            $cacheKey = $this->getCacheKey((string) $regexKey);

            if ($this->cache->hasItem($cacheKey)) {
                continue;
            }

            $this->cache->setItem($cacheKey, $data);
        }

        $this->cache->setItem($initKey, true);
    }

    /**
     * @throws \Seld\JsonLint\ParsingException
     *
     * @return \Generator|\stdClass[]
     */
    public function getRegexes(): \Generator
    {
        static $regexes = null;

        if (null === $regexes) {
            $jsonParser = new JsonParser();
            $regexes    = $jsonParser->parse(
                file_get_contents(__DIR__ . '/../../data/regexes.yaml'),
                JsonParser::DETECT_KEY_CONFLICTS
            );
        }

        foreach ($regexes as $regexKey => $data) {
            yield $regexKey => $data;
        }
    }

    /**
     * @param string $deviceKey
     *
     * @return string
     */
    private function getCacheKey(string $deviceKey): string
    {
        return self::CACHE_PREFIX . '_' . str_replace(['{', '}', '(', ')', '/', '\\', '@', ':'], '_', $deviceKey);
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     * @throws \Seld\JsonLint\ParsingException
     *
     * @return void
     */
    public function warmupCache(): void
    {
        $this->init();
    }
}
