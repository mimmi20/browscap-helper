<?php

/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2026, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper\Entity;

use UaResult\Result\Result;

use function array_merge_recursive;

final class TestProperties
{
    /**
     * @param non-empty-string                                                                                    $sourceName
     * @param array<non-empty-string, array{date-first: non-empty-string|null, date-last: non-empty-string|null}> $sources
     *
     * @throws void
     */
    public function __construct(
        private readonly Result $result,
        private readonly string | null $file,
        private string | null $dateFirst,
        private string | null $dateLast,
        private readonly mixed $raw,
        private string $sourceName,
        private array $sources,
    ) {
        // nothing to do
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getResult(): Result
    {
        return $this->result;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getDateFirst(): string | null
    {
        return $this->dateFirst;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getDateLast(): string | null
    {
        return $this->dateLast;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getFile(): string | null
    {
        return $this->file;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getRaw(): mixed
    {
        return $this->raw;
    }

    /**
     * @return non-empty-string
     *
     * @throws void
     *
     * @api
     */
    public function getSourceName(): string
    {
        return $this->sourceName;
    }

    /**
     * @return array<non-empty-string, array{date-first: non-empty-string|null, date-last: non-empty-string|null}>
     *
     * @throws void
     *
     * @api
     */
    public function getSources(): array
    {
        return $this->sources;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function withDateFirst(string $dateFirst): self
    {
        $cloned            = clone $this;
        $cloned->dateFirst = $dateFirst;

        return $cloned;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function withDateLast(string $dateLast): self
    {
        $cloned           = clone $this;
        $cloned->dateLast = $dateLast;

        return $cloned;
    }

    /**
     * @param non-empty-string $sourceName
     *
     * @throws void
     *
     * @api
     */
    public function withSourceName(string $sourceName): self
    {
        return clone ($this, ['sourceName' => $sourceName]);
    }

    /**
     * @param array<non-empty-string, array{date-first: non-empty-string|null, date-last: non-empty-string|null}> $sources
     *
     * @throws void
     *
     * @api
     */
    public function withSources(array $sources): self
    {
        return clone ($this, ['sources' => array_merge_recursive($this->sources, $sources)]);
    }
}
