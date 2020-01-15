<?php
/**
 * This file is part of the browscap-helper-source package.
 *
 * Copyright (c) 2016-2019, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Source\Helper;

final class Sorter
{
    /**
     * @param array $agents
     *
     * @return array
     */
    public function sortAgents(array $agents): array
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
