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

use League\Flysystem\UnreadableFileException;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class DiffCommand
 *
 * @category   Browscap
 * @author     James Titcumb <james@asgrim.com>
 */
class TargetDirectory
{
    /**
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \League\Flysystem\UnreadableFileException
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
     * @return int
     */
    public function getNextTest(OutputInterface $output)
    {
        $output->writeln('detect next test number ...');

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
        $output->writeln('nexst test: ' . $number);

        return $number;
    }
}
