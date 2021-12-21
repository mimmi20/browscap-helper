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

namespace BrowscapHelper\Source\Ua;

use BrowscapHelper\Source\SourceInterface;

use function explode;
use function implode;
use function sprintf;

final class UserAgent
{
    /** @var array<string, string> */
    private array $header = [];

    /**
     * @param array<string, string> $header
     *
     * @throws void
     */
    public function __construct(array $header)
    {
        $this->header = $header;
    }

    /**
     * @throws void
     */
    public function __toString(): string
    {
        $stringHeaders = [];

        foreach ($this->header as $name => $value) {
            $stringHeaders[] = sprintf('%s%s%s', $name, SourceInterface::DELIMETER_HEADER_ROW, $value);
        }

        return implode(SourceInterface::DELIMETER_HEADER, $stringHeaders);
    }

    /**
     * @return array<string, string>
     *
     * @throws void
     */
    public function getHeaders(): array
    {
        return $this->header;
    }

    /**
     * @throws void
     */
    public static function fromUseragent(string $useragent): self
    {
        return new self(['user-agent' => $useragent]);
    }

    /**
     * @throws void
     */
    public static function fromString(string $string): self
    {
        $stringHeaders = explode(SourceInterface::DELIMETER_HEADER, $string);
        $headers       = [];

        foreach ($stringHeaders as $value) {
            [$name, $valueRow] = explode(SourceInterface::DELIMETER_HEADER_ROW, $value);

            $headers[$name] = $valueRow;
        }

        return new self($headers);
    }

    /**
     * @param array<string, string> $headers
     *
     * @throws void
     */
    public static function fromHeaderArray(array $headers): self
    {
        return new self($headers);
    }
}
