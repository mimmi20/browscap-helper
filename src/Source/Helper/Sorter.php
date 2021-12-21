<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2021, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Source\Helper;

use function array_multisort;

use const SORT_ASC;
use const SORT_DESC;

final class Sorter
{
    /**
     * @param array<string, int> $agents
     *
     * @return array<string, int>
     *
     * @throws void
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
