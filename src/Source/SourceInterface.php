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

namespace BrowscapHelper\Source;

use LogicException;
use RuntimeException;

interface SourceInterface
{
    public const DELIMETER_HEADER = '{{::==::}}';

    public const DELIMETER_HEADER_ROW = '::==::';

    public function isReady(string $parentMessage): bool;

    /**
     * @return array<array<string, string>>|iterable
     *
     * @throws LogicException
     * @throws RuntimeException
     */
    public function getHeaders(string $parentMessage, int &$messageLength = 0): iterable;

    public function getName(): string;
}
