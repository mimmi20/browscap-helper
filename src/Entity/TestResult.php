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

final readonly class TestResult
{
    /** @api */
    public const int STATUS_OK = 0;

    /** @api */
    public const int STATUS_DUPLICATE = 1;

    /** @api */
    public const int STATUS_SKIPPED = 2;

    /** @api */
    public const int STATUS_ERROR = 3;

    /** @api */
    public const int EXIT_NO_RESULT = 1;

    /** @api */
    public const int EXIT_DEVICE_IS_NULL = 2;

    /** @api */
    public const int EXIT_CLIENT_IS_NULL = 3;

    /** @api */
    public const int EXIT_DEVICE_NOT_SCALAR = 4;

    /** @api */
    public const int EXIT_CLIENT_NOT_SCALAR = 5;

    /** @api */
    public const int EXIT_DEVICE_IS_UNKNOW = 6;

    /** @api */
    public const int EXIT_CLIENT_IS_UNKNOW = 7;

    /** @api */
    public const int EXIT_CLIENT_IS_BOT = 8;

    /** @api */
    public const int EXIT_DEVICE_IS_DESKTOP = 9;

    /** @api */
    public const int EXIT_DEVICE_IS_MOBILE = 10;

    /** @api */
    public const int EXIT_DEVICE_IS_TV = 11;

    /** @api */
    public const int EXIT_DEVICE_IS_OTHER = 12;

    /** @api */
    public const int EXIT_DEVICE_IS_GENERAL = 13;

    /** @api */
    public const int EXIT_CLIENT_IS_GENERAL = 14;

    /**
     * @param self::STATUS_*                            $status
     * @param array<non-empty-string, non-empty-string> $headers
     * @param self::EXIT_*                              $exit
     *
     * @throws void
     */
    public function __construct(
        private Result | null $result,
        private int $status,
        private array $headers,
        private int $exit,
    ) {
        // nothing to do
    }

    /**
     * @throws void
     *
     * @api
     */
    public function getResult(): Result | null
    {
        return $this->result;
    }

    /**
     * @return self::STATUS_*
     *
     * @throws void
     *
     * @api
     */
    public function getStatus(): int
    {
        return $this->status;
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
     * @return self::EXIT_*
     *
     * @throws void
     *
     * @api
     */
    public function getExit(): int
    {
        return $this->exit;
    }
}
