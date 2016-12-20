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

namespace BrowscapHelper\Helper;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class FilePath
{
    /**
     * @param \SplFileInfo $file
     *
     * @return string
     */
    public function getPath(\SplFileInfo $file)
    {
        $realpath = realpath($file->getPathname());

        if (false === $realpath) {
            return null;
        }

        switch ($file->getExtension()) {
            case 'gz':
                $path = 'compress.zlib://' . $realpath;
                break;
            case 'bz2':
                $path = 'compress.bzip2://' . $realpath;
                break;
            case 'tgz':
                $path = 'phar://' . $realpath;
                break;
            default:
                $path = $realpath;
                break;
        }

        return $path;
    }
}
