<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2019, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Command\Helper;

use Symfony\Component\Console\Helper\Helper;

final class TargetDirectory extends Helper
{
    public function getName()
    {
        return 'target-directory';
    }

    /**
     * @param string $targetDirectory
     *
     * @throws \UnexpectedValueException
     *
     * @return int
     */
    public function getNextTest(string $targetDirectory): int
    {
        if (!is_readable($targetDirectory)) {
            throw new \UnexpectedValueException('directory "' . $targetDirectory . '" is not readable');
        }

        if (!is_dir($targetDirectory)) {
            throw new \UnexpectedValueException('directory "' . $targetDirectory . '" is not a directory');
        }

        $filesArray = (array) scandir($targetDirectory, SCANDIR_SORT_ASCENDING);
        $number     = 0;
        $filesFound = false;

        foreach ($filesArray as $filename) {
            if (in_array($filename, ['.', '..', '.gitkeep'])) {
                continue;
            }

            $filesFound = true;
            $file       = new \SplFileInfo($targetDirectory . $filename);
            $basename   = $file->getBasename('.' . $file->getExtension());

            if ((int) $basename > $number) {
                $number = (int) $basename;
            }
        }

        if (!$filesFound) {
            return 0;
        }

        ++$number;

        return $number;
    }
}
