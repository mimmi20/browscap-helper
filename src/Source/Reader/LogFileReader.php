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
namespace BrowscapHelper\Source\Reader;

use BrowscapHelper\Source\Helper\Regex;
use FileLoader\Loader;
use Psr\Log\LoggerInterface;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 *
 * @author     James Titcumb <james@asgrim.com>
 */
class LogFileReader implements ReaderInterface
{
    /**
     * @var \FileLoader\Loader
     */
    private $loader;

    public function __construct()
    {
        $this->loader = new Loader();
    }

    /**
     * @param string $file
     */
    public function setLocalFile(string $file): void
    {
        $this->loader->setLocalFile($file);
    }

    /**
     * @param \Psr\Log\LoggerInterface $logger
     *
     * @return \Generator
     */
    public function getAgents(LoggerInterface $logger): iterable
    {
        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $this->loader->load();

        /** @var \FileLoader\Psr7\Stream $stream */
        $stream = $response->getBody();

        $stream->read(1);
        $stream->rewind();

        $regex = (new Regex())->getRegex();

        while (!$stream->eof()) {
            $line = $stream->read(8192);

            if (empty($line)) {
                continue;
            }

            $lineMatches = [];

            if (!preg_match($regex, $line, $lineMatches)) {
                $logger->warning('no useragent found in line "' . $line . '"');

                continue;
            }

            if (isset($lineMatches['userAgentString'])) {
                $agentOfLine = trim($lineMatches['userAgentString']);
            } else {
                $agentOfLine = trim($this->extractAgent($line));
            }

            if (!is_string($agentOfLine)) {
                continue;
            }

            yield $agentOfLine;
        }
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function extractAgent(string $text): string
    {
        $parts = explode('"', $text);
        array_pop($parts);

        $userAgent = array_pop($parts);

        return $userAgent;
    }
}
