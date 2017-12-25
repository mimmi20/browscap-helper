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

/**
 * Class DirectorySource
 *
 * @author  Thomas Mueller <mimmi20@live.de>
 */
class CollectionSource implements SourceInterface
{
    /**
     * @var \BrowscapHelper\Source\SourceInterface[]
     */
    private $collection;

    /**
     * @param \BrowscapHelper\Source\SourceInterface[] $collection
     */
    public function __construct(array $collection)
    {
        foreach ($collection as $source) {
            if (!$source instanceof SourceInterface) {
                throw new SourceException('unsupported type of source found');
            }

            $this->collection[] = $source;
        }
    }

    /**
     * @param int $limit
     *
     * @return iterable|string[]
     */
    public function getUserAgents(int $limit = 0): iterable
    {
        $counter   = 0;
        $allAgents = [];

        foreach ($this->collection as $source) {
            if ($limit && $counter >= $limit) {
                return;
            }

            foreach ($source->getUserAgents($limit) as $ua) {
                if ($limit && $counter >= $limit) {
                    return;
                }

                if (empty($ua)) {
                    continue;
                }

                if (array_key_exists($ua, $allAgents)) {
                    continue;
                }

                yield $ua;
                $allAgents[$ua] = 1;
                ++$counter;
            }
        }
    }
}
