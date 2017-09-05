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
namespace BrowscapHelper\Helper;

/**
 * Class TargetDirectory
 *
 * @category   Browscap Helper
 *
 * @author     Thomas Mueller <mimmi20@live.de>
 */
class TargetDirectory
{
    /**
     * @throws \UnexpectedValueException
     *
     * @return string
     */
    public function getPath(): string
    {
        $number = $this->getNextTest();

        return 'tests/issues/' . sprintf('%1$07d', $number) . '/';
    }

    /**
     * @throws \UnexpectedValueException
     *
     * @return int
     */
    public function getNextTest(): int
    {
        $targetDirectory = 'tests/issues/';

        if (!is_readable($targetDirectory)) {
            throw new \UnexpectedValueException('directory "' . $targetDirectory . '" is not readable');
        }

        $filesArray = scandir($targetDirectory, SCANDIR_SORT_ASCENDING);
        $number     = 0;

        foreach ($filesArray as $filename) {
            if (in_array($filename, ['.', '..'])) {
                continue;
            }

            $file     = new \SplFileInfo($targetDirectory . $filename);
            $basename = $file->getBasename('.' . $file->getExtension());

            if ((int) $basename > $number) {
                $number = (int) $basename;
            }
        }

        ++$number;

        return $number;
    }
}
