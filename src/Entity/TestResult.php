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

final readonly class TestResult
{
    public const int STATUS_OK = 0;

    public const int STATUS_DUPLICATE = 1;

    public const int STATUS_SKIPPED = 2;

    public const int STATUS_ERROR = 3;

    public const int EXIT_NO_RESULT = 1;

    public const int EXIT_DEVICE_IS_NULL = 2;

    public const int EXIT_CLIENT_IS_NULL = 3;

    public const int EXIT_DEVICE_NOT_SCALAR = 4;

    public const int EXIT_CLIENT_NOT_SCALAR = 5;

    public const int EXIT_DEVICE_IS_UNKNOW = 6;

    public const int EXIT_CLIENT_IS_UNKNOW = 7;

    public const int EXIT_CLIENT_IS_BOT = 8;

    public const int EXIT_DEVICE_IS_DESKTOP = 9;

    public const int EXIT_DEVICE_IS_MOBILE = 10;

    public const int EXIT_DEVICE_IS_TV = 11;

    public const int EXIT_DEVICE_IS_OTHER = 12;

    public const int EXIT_DEVICE_IS_GENERAL = 13;

    public const int EXIT_CLIENT_IS_GENERAL = 14;

    /**
     * @param array{headers: array<non-empty-string, string>, device: array{architecture: string|null, deviceName: string|null, marketingName: string|null, manufacturer: string|null, brand: string|null, dualOrientation: bool|null, simCount: int|null, display: array{width: int|null, height: int|null, touch: bool|null, size: float|null}, type: string|null, ismobile: bool, istv: bool, bits: int|null}, os: array{name: string|null, marketingName: string|null, version: string|null, manufacturer: string|null}, client: array{name: string|null, version: string|null, manufacturer: string|null, type: string|null, isbot: bool}, engine: array{name: string|null, version: string|null, manufacturer: string|null}}|null $result
     * @phpstan-param self::STATUS_* $status
     * @phpstan-param array<non-empty-string, non-empty-string> $headers
     * @phpstan-param self::EXIT_* $exit
     *
     * @throws void
     */
    public function __construct(
        private array | null $result,
        private int $status,
        private array $headers,
        private int $exit,
    ) {
        // nothing to do
    }

    /**
     * @return array{headers: array<non-empty-string, string>, device: array{architecture: string|null, deviceName: string|null, marketingName: string|null, manufacturer: string|null, brand: string|null, dualOrientation: bool|null, simCount: int|null, display: array{width: int|null, height: int|null, touch: bool|null, size: float|null}, type: string|null, ismobile: bool, istv: bool, bits: int|null}, os: array{name: string|null, marketingName: string|null, version: string|null, manufacturer: string|null}, client: array{name: string|null, version: string|null, manufacturer: string|null, type: string|null, isbot: bool}, engine: array{name: string|null, version: string|null, manufacturer: string|null}}|null
     *
     * @throws void
     *
     * @api
     */
    public function getResult(): array | null
    {
        return $this->result;
    }

    /**
     * @phpstan-return self::STATUS_*
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
     * @phpstan-return array<non-empty-string, non-empty-string>
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
     * @phpstan-return self::EXIT_*
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
