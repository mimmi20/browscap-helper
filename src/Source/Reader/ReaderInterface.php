<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2017, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Source\Reader;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 *
 * @author     James Titcumb <james@asgrim.com>
 */
interface ReaderInterface
{
    /**
     * @param string $file
     */
    public function setLocalFile($file): void;

    /**
     * @return array
     */
    public function getAgents(): iterator;
}
