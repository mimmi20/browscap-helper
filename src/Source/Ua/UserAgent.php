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
namespace BrowscapHelper\Source\Ua;

use BrowscapHelper\Source\SourceInterface;

final class UserAgent
{
    /**
     * @var array
     */
    private $header = [];

    /**
     * UserAgent constructor.
     *
     * @param array $header
     */
    public function __construct(array $header)
    {
        $this->header = $header;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->header;
    }

    /**
     * @return string
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
     * @param string $useragent
     *
     * @return \BrowscapHelper\Source\Ua\UserAgent
     */
    public static function fromUseragent(string $useragent): self
    {
        return new self(['user-agent' => $useragent]);
    }

    /**
     * @param string $string
     *
     * @return \BrowscapHelper\Source\Ua\UserAgent
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
     * @param array $headers
     *
     * @return \BrowscapHelper\Source\Ua\UserAgent
     */
    public static function fromHeaderArray(array $headers): self
    {
        return new self($headers);
    }
}
