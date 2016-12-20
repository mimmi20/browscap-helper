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

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class DetectorTestReader implements ReaderInterface
{
    /**
     * @var string|null
     */
    private $localFile = null;

    /**
     * @param string $file
     */
    public function setLocalFile($file)
    {
        $this->localFile = $file;
    }

    /**
     * @return array
     */
    public function getAgents()
    {
        $tests  = $this->getTests();
        $agents = [];

        foreach ($tests as $test) {
            if (is_array($test)) {
                $test = (object) $test;
            }

            $ua = $test->ua;

            if (!array_key_exists($ua, $agents)) {
                $agents[$ua] = 1;
            } else {
                ++$agents[$ua];
            }
        }

        return $agents;
    }

    /**
     * @return array
     */
    public function getTests()
    {
        $file = new \SplFileInfo($this->localFile);

        return json_decode(file_get_contents($file->getPathname()));
    }
}
