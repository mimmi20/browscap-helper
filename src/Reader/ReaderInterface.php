<?php
/**
 * Copyright (c) 1998-2014 Browser Capabilities Project
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * Refer to the LICENSE file distributed with this package.
 *
 * @category   Browscap
 * @copyright  1998-2014 Browser Capabilities Project
 * @license    MIT
 */

namespace BrowscapHelper\Reader;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
interface ReaderInterface
{
    /**
     * @param string $file
     */
    public function setLocalFile($file);

    /**
     * @return array
     */
    public function getAgents();
}
