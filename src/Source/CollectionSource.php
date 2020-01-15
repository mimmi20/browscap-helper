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
namespace BrowscapHelper\Source;

final class CollectionSource implements SourceInterface
{
    /**
     * @var \BrowscapHelper\Source\SourceInterface[]
     */
    private $collection;

    /**
     * CollectionSource constructor.
     *
     * @param \BrowscapHelper\Source\SourceInterface ...$collection
     */
    public function __construct(SourceInterface ...$collection)
    {
        $this->collection = $collection;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return 'collection';
    }

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return iterable|string[]
     */
    public function getUserAgents(): iterable
    {
        foreach ($this->collection as $source) {
            yield from $source->getUserAgents();
        }
    }

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    public function getHeaders(): iterable
    {
        foreach ($this->collection as $source) {
            yield from $source->getHeaders();
        }
    }

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    public function getProperties(): iterable
    {
        foreach ($this->collection as $source) {
            yield from $source->getProperties();
        }
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->collection);
    }
}
