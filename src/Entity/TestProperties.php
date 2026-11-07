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

final class TestProperties
{
    /** @throws void */
    public function __construct(
        private readonly Result $result,
        private readonly string | null $file,
        private string $dateFirst,
        private string $dateLast,
        private readonly mixed $raw,
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
    public function getDateFirst(): string
    {
        return $this->dateFirst;
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getDateLast(): string
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
}
