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
namespace BrowscapHelper\Source;

use BrowserDetector\Helper\GenericRequestFactory;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use UaResult\Browser\Browser;
use UaResult\Device\Device;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;

/**
 * Class DirectorySource
 *
 * @author  Thomas Mueller <mimmi20@live.de>
 */
class MobileDetectSource implements SourceInterface
{
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cache;

    /**
     * @param \Psr\Log\LoggerInterface          $logger
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     */
    public function __construct(LoggerInterface $logger, CacheItemPoolInterface $cache)
    {
        $this->logger = $logger;
        $this->cache  = $cache;
    }

    /**
     * @param int $limit
     *
     * @return iterable|string[]
     */
    public function getUserAgents(int $limit = 0): iterable
    {
        $counter = 0;

        foreach ($this->loadFromPath() as $agent) {
            if ($limit && $counter >= $limit) {
                return;
            }

            if (empty($agent)) {
                continue;
            }

            yield $agent;
            ++$counter;
        }
    }

    /**
     * @return iterable|\UaResult\Result\Result[]
     */
    public function getTests(): iterable
    {
        foreach ($this->loadFromPath() as $agent) {
            if (empty($agent)) {
                continue;
            }

            $request  = (new GenericRequestFactory())->createRequestFromString($agent);
            $browser  = new Browser(null);
            $device   = new Device(null, null);
            $platform = new Os(null, null);
            $engine   = new Engine(null);

            yield $agent => new Result($request->getHeaders(), $device, $platform, $browser, $engine);
        }
    }

    /**
     * @return array[]|iterable
     */
    private function loadFromPath(): iterable
    {
        $path = 'vendor/mobiledetect/mobiledetectlib/tests/providers/vendors';

        if (!file_exists($path)) {
            return;
        }

        $this->logger->info('    reading path ' . $path);

        $allTests = [];
        $finder   = new Finder();
        $finder->files();
        $finder->name('*.php');
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($path);

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            if (!$file->isFile()) {
                $this->logger->emergency('not-files selected with finder');

                continue;
            }

            if ('php' !== $file->getExtension()) {
                $this->logger->emergency('wrong file extension [' . $file->getExtension() . '] found with finder');

                continue;
            }

            $filepath = $file->getPathname();

            $this->logger->info('    reading file ' . str_pad($filepath, 100, ' ', STR_PAD_RIGHT));
            $data = include $filepath;
            $key  = $file->getBasename('.php');

            if (!is_array($data) || !array_key_exists($key, $data) || !is_array($data[$key])) {
                continue;
            }

            foreach (array_keys($data[$key]) as $agent) {
                $agent = trim($agent);

                if (array_key_exists($agent, $allTests)) {
                    continue;
                }

                yield $agent;
                $allTests[$agent] = 1;
            }
        }
    }
}
