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

use DateTimeImmutable;

final readonly class TestData
{
    /**
     * @param array<non-empty-string, non-empty-string> $headers
     *
     * @throws void
     */
    public function __construct(
        private array $headers,
        private DateTimeImmutable $dateFirst,
        private DateTimeImmutable $dateLast,
    ) {
        // nothing to do
    }

    /**
     * @return array<non-empty-string, non-empty-string>
     *
     * @throws void
     *
     * @api
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getDateFirst(): DateTimeImmutable
    {
        return $this->dateFirst;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getDateLast(): DateTimeImmutable
    {
        return $this->dateLast;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function withDateFirst(DateTimeImmutable $dateFirst): self
    {
        return clone ($this, ['dateFirst' => $dateFirst]);
    }

    /**
     * @throws void
     *
     * @api
     */
    public function withDateLast(DateTimeImmutable $dateLast): self
    {
        return clone ($this, ['dateLast' => $dateLast]);
    }
}
