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

use FileLoader\Loader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use UaResult\Browser\Browser;
use UaResult\Device\Device;
use UaResult\Engine\Engine;
use UaResult\Os\Os;
use UaResult\Result\Result;
use Wurfl\Request\GenericRequestFactory;

/**
 * Class DirectorySource
 *
 * @author  Thomas Mueller <mimmi20@live.de>
 */
class DirectorySource implements SourceInterface
{
    /**
     * @var string
     */
    private $dir = null;

    /**
     * @var \FileLoader\Loader
     */
    private $loader = null;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger = null;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param string                   $dir
     */
    public function __construct(LoggerInterface $logger, $dir)
    {
        $this->logger = $logger;
        $this->dir    = $dir;
        $this->loader = new Loader();
    }

    /**
     * @param int $limit
     *
     * @return string[]
     */
    public function getUserAgents(int $limit = 0): iterator
    {
        $counter = 0;

        foreach ($this->loadFromPath() as $line) {
            if ($limit && $counter >= $limit) {
                return;
            }

            yield $line;
            ++$counter;
        }
    }

    /**
     * @return \UaResult\Result\Result[]
     */
    public function getTests(): iterator
    {
        foreach ($this->loadFromPath() as $line) {
            $request  = (new GenericRequestFactory())->createRequestForUserAgent($line);
            $browser  = new Browser(null);
            $device   = new Device(null, null);
            $platform = new Os(null, null);
            $engine   = new Engine(null);

            yield $line => new Result($request, $device, $platform, $browser, $engine);
        }
    }

    /**
     * @return \Generator
     */
    private function loadFromPath(): iterator
    {
        $allLines = [];
        $finder   = new Finder();
        $finder->files();
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($this->dir);

        foreach ($finder as $file) {
            if (!$file->isFile()) {
                continue;
            }

            $this->logger->info('    reading file ' . str_pad($file->getPathname(), 100, ' ', STR_PAD_RIGHT));

            $this->loader->setLocalFile($file->getPathname());

            /** @var \GuzzleHttp\Psr7\Response $response */
            $response = $this->loader->load();

            /** @var \FileLoader\Psr7\Stream $stream */
            $stream = $response->getBody();

            $stream->read(1);
            $stream->rewind();

            while (!$stream->eof()) {
                $line = $stream->read(8192);

                if (empty($line)) {
                    continue;
                }

                $line = trim($line);

                if (isset($allLines[$line])) {
                    continue;
                }

                yield $line;
                $allLines[$line] = 1;
            }
        }
    }
}
