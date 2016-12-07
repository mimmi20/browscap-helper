<?php
/**
 * Copyright (c) 1998-2014 Browser Capabilities Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   Browscap
 * @copyright  1998-2014 Browser Capabilities Project
 * @license    MIT
 */

namespace BrowscapHelper\Reader;

use BrowscapHelper\Helper\Regex;
use FileLoader\Loader;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class LogFileReader implements ReaderInterface
{
    /**
     * @var string|null
     */
    private $targetInfoFile = null;

    /**
     * @var \FileLoader\Loader
     */
    private $loader = null;

    public function __construct()
    {
        $this->loader = new Loader();
    }

    /**
     * @param string $targetInfoFile
     */
    public function setTargetInfoFile($targetInfoFile)
    {
        $this->targetInfoFile = $targetInfoFile;
    }

    /**
     * @param string $file
     */
    public function setLocalFile($file)
    {
        $this->loader->setLocalFile($file);
    }

    /**
     * @return array
     */
    public function getAgents()
    {
        /** @var \GuzzleHttp\Psr7\Response $response */
        $response = $this->loader->load();

        /** @var \FileLoader\Psr7\Stream $stream */
        $stream = $response->getBody();

        $stream->read(1);
        $stream->rewind();

        $agents = [];
        $regex  = (new Regex())->getRegex();

        while (!$stream->eof()) {
            $line        = $stream->read(8192);
            $lineMatches = [];

            if (!preg_match($regex, $line, $lineMatches)) {
                file_put_contents($this->targetInfoFile, 'no useragent found in line "' . $line . '"' . "\n", FILE_APPEND | LOCK_EX);

                continue;
            }

            if (isset($lineMatches['userAgentString'])) {
                $agentOfLine = trim($lineMatches['userAgentString']);
            } else {
                $agentOfLine = trim($this->extractAgent($line));
            }

            if (!array_key_exists($agentOfLine, $agents)) {
                $agents[$agentOfLine] = 1;
            } else {
                ++$agents[$agentOfLine];
            }
        }

        return $agents;
    }

    /**
     * @param string $text
     *
     * @return string
     */
    private function extractAgent($text)
    {
        $parts = explode('"', $text);
        array_pop($parts);

        $userAgent = array_pop($parts);

        return $userAgent;
    }
}
