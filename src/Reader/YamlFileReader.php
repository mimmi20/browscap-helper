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

use Symfony\Component\Yaml\Yaml;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class YamlFileReader implements ReaderInterface
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
        $list   = Yaml::parse(file_get_contents($this->localFile));
        $agents = [];

        foreach ($list['test_cases'] as $part) {
            if (!array_key_exists('user_agent_string', $part)) {
                continue;
            }

            $agent = $part['user_agent_string'];

            if (!array_key_exists($agent, $agents)) {
                $agents[$agent] = 1;
            } else {
                ++$agents[$agent];
            }
        }

        return $agents;
    }
}
