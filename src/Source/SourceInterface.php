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

interface SourceInterface
{
    public const DELIMETER_HEADER = '{{::==::}}';

    public const DELIMETER_HEADER_ROW = '::==::';

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return iterable|string[]
     */
    public function getUserAgents(): iterable;

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    public function getHeaders(): iterable;

    /**
     * @return string
     */
    public function getName(): string;

    /**
     * @throws \LogicException
     * @throws \RuntimeException
     *
     * @return array[]|iterable
     */
    public function getProperties(): iterable;
}
