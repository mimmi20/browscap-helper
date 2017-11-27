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

use Psr\Cache\CacheItemPoolInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use UaResult\Result\ResultFactory;
use Seld\JsonLint\JsonParser;
use Seld\JsonLint\ParsingException;

/**
 * Class DirectorySource
 *
 * @author  Thomas Mueller <mimmi20@live.de>
 */
class DetectorSource implements SourceInterface
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

        foreach ($this->loadFromPath() as $test) {
            if ($limit && $counter >= $limit) {
                return;
            }

            $agent = trim($test->ua);

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
        $resultFactory = new ResultFactory();

        foreach ($this->loadFromPath() as $test) {
            $agent = trim($test->ua);

            if (empty($agent)) {
                continue;
            }

            yield $agent => $resultFactory->fromArray($this->cache, $this->logger, (array) $test->result);
        }
    }

    /**
     * @return iterable|\StdClass[]
     */
    private function loadFromPath(): iterable
    {
        $path = 'vendor/mimmi20/browser-detector-tests/tests/issues';

        if (!file_exists($path)) {
            return;
        }

        $this->logger->info('    reading path ' . $path);

        $allTests = [];
        $finder   = new Finder();
        $finder->files();
        $finder->name('*.json');
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($path);

        $jsonParser = new JsonParser();

        foreach ($finder as $file) {
            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            if (!$file->isFile()) {
                $this->logger->emergency('not-files selected with finder');

                continue;
            }

            if ('json' !== $file->getExtension()) {
                $this->logger->emergency('wrong file extension [' . $file->getExtension() . '] found with finder');

                continue;
            }

            $filepath = $file->getPathname();

            $this->logger->info('    reading file ' . str_pad($filepath, 100, ' ', STR_PAD_RIGHT));

            try {
                $data = $jsonParser->parse(
                    file_get_contents($filepath),
                    JsonParser::DETECT_KEY_CONFLICTS
                );
            } catch (ParsingException $e) {
                $this->logger->crit(new \Exception('    parsing file content [' . $filepath . '] failed', 0, $e));

                continue;
            }

            if (!($data instanceof \stdClass)) {
                continue;
            }

            foreach ($data as $test) {
                if (!isset($test->ua)) {
                    continue;
                }

                $agent = trim($test->ua);

                if (array_key_exists($agent, $allTests)) {
                    continue;
                }

                yield $test;
                $allTests[$agent] = 1;
            }
        }
    }
}
