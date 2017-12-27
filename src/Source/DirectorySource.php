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

use BrowscapHelper\Source\Helper\FilePath;
use FileLoader\Loader;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;

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
    private $dir;

    /**
     * @var \FileLoader\Loader
     */
    private $loader;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param string                   $dir
     */
    public function __construct(LoggerInterface $logger, string $dir)
    {
        $this->logger = $logger;
        $this->dir    = $dir;
        $this->loader = new Loader();
    }

    /**
     * @param int $limit
     *
     * @throws \FileLoader\Exception
     *
     * @return iterable|string[]
     */
    public function getUserAgents(int $limit = 0): iterable
    {
        $counter = 0;

        foreach ($this->loadFromPath() as $line) {
            if ($limit && $counter >= $limit) {
                return;
            }

            $agent = trim($line);

            if (empty($agent)) {
                continue;
            }

            yield $agent;
            ++$counter;
        }
    }

    /**
     * @throws \FileLoader\Exception
     *
     * @return iterable|string[]
     */
    private function loadFromPath(): iterable
    {
        $allLines = [];
        $finder   = new Finder();
        $finder->files();
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($this->dir);

        $fileHelper = new FilePath();

        foreach ($finder as $file) {
            if (!$file->isFile()) {
                $this->logger->emergency('not-files selected with finder');

                continue;
            }

            $this->logger->info('    reading file ' . str_pad($file->getPathname(), 100, ' ', STR_PAD_RIGHT));

            $fullPath = $fileHelper->getPath($file);

            if (null === $fullPath) {
                $this->logger->error('could not detect path for file "' . $file->getPathname() . '"');

                continue;
            }

            $this->loader->setLocalFile($fullPath);

            /** @var \GuzzleHttp\Psr7\Response $response */
            $response = $this->loader->load();

            /** @var \FileLoader\Psr7\Stream $stream */
            $stream = $response->getBody();

            try {
                $stream->read(1);
            } catch (\Throwable $e) {
                $this->logger->emergency(new \RuntimeException('reading file ' . $file->getPathname() . ' caused an error on line 0', 0, $e));
            }

            try {
                $stream->rewind();
            } catch (\Throwable $e) {
                $this->logger->emergency(new \RuntimeException('rewinding file ' . $file->getPathname() . ' caused an error on line 0', 0, $e));
            }

            $i = 1;

            while (!$stream->eof()) {
                try {
                    $line = $stream->read(65535);
                } catch (\Throwable $e) {
                    $this->logger->emergency(new \RuntimeException('reading file ' . $file->getPathname() . ' caused an error on line ' . $i, 0, $e));

                    continue;
                }
                ++$i;

                if (empty($line)) {
                    continue;
                }

                $line = trim($line);

                $this->logger->info('    reading line ' . $line);

                if (array_key_exists($line, $allLines)) {
                    continue;
                }

                yield $line;
                $allLines[$line] = 1;
            }
        }
    }
}
