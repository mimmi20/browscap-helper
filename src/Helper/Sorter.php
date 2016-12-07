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

namespace BrowscapHelper\Helper;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class Sorter
{
    /**
     * @param array  $agents
     *
     * @return array
     */
    public function sortAgents(array $agents)
    {
        $sortCount = [];
        $sortAgent = [];

        foreach ($agents as $agentOfLine => $count) {
            $sortCount[$agentOfLine] = $count;
            $sortAgent[$agentOfLine] = $agentOfLine;
        }

        array_multisort($sortCount, SORT_DESC, $sortAgent, SORT_ASC, $agents);

        return $agents;
    }
}
