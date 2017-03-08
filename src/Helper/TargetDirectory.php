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

use League\Flysystem\UnreadableFileException;
use Symfony\Component\Console\Output\OutputInterface;

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
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \League\Flysystem\UnreadableFileException
     *
     * @return string
     */
    public function getPath(OutputInterface $output)
    {
        $number = $this->getNextTest($output);

        return 'vendor/mimmi20/browser-detector-tests/tests/issues/' . sprintf('%1$05d', $number) . '/';
    }

    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \League\Flysystem\UnreadableFileException
     *
     * @return int
     */
    public function getNextTest(OutputInterface $output)
    {
        $targetDirectory = 'vendor/mimmi20/browser-detector-tests/tests/issues/';

        if (!is_readable($targetDirectory)) {
            throw new UnreadableFileException('directory "' . $targetDirectory . '" is not readable');
        }

        $filesArray      = scandir($targetDirectory, SCANDIR_SORT_ASCENDING);
        $number          = 0;

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
