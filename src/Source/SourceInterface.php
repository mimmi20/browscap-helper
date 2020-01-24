<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2020, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Source;

interface SourceInterface
{
    public const DELIMETER_HEADER = '{{::==::}}';

    public const DELIMETER_HEADER_ROW = '::==::';

    /**
     * @param string $parentMessage
     *
     * @return bool
     */
    public function isReady(string $parentMessage): bool;

    /**
     * @param string $parentMessage
     * @param int    $messageLength
     *
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    public function getHeaders(string $parentMessage, int &$messageLength = 0): iterable;

    /**
     * @return string
     */
    public function getName(): string;
}
