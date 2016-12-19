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

use FileLoader\Loader;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class TxtFileReader implements ReaderInterface
{
    /**
     * @var \FileLoader\Loader
     */
    private $loader = null;

    public function __construct()
    {
        $this->loader = new Loader();
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

        while (!$stream->eof()) {
            $line = $stream->read(8192);

            if (!is_string($line)) {
                continue;
            }

            $line = trim($line);

            if (!array_key_exists($line, $agents)) {
                $agents[$line] = 1;
            } else {
                ++$agents[$line];
            }
        }

        return $agents;
    }
}
