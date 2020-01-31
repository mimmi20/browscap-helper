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
namespace BrowscapHelper\Command\Helper;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

final class ExistingTestsRemover extends Helper
{
    public function getName()
    {
        return 'existing-tests-remover';
    }

    /**
     * @param OutputInterface $output
     * @param string          $testSource
     *
     * @throws \Symfony\Component\Finder\Exception\DirectoryNotFoundException
     * @throws \LogicException
     *
     * @return void
     */
    public function remove(OutputInterface $output, string $testSource): void
    {
        $baseMessage   = 'remove old files';
        $messageLength = 0;

        $message = $baseMessage . ' ...';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $output->writeln(str_pad($message, $messageLength, ' ', STR_PAD_RIGHT), OutputInterface::VERBOSITY_NORMAL);

        $finder = new Finder();
        $finder->files();
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($testSource);

        $counter = 0;

        foreach ($finder as $file) {
            $message = $baseMessage . sprintf(' [%7d] - %s', $counter, $file->getPathname()) . ' ...';

            if (mb_strlen($message) > $messageLength) {
                $messageLength = mb_strlen($message);
            }

            $output->write("\r" . '<fg=yellow>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</>', false, OutputInterface::VERBOSITY_VERBOSE);

            //unlink($file->getPathname());

            ++$counter;
        }

        $message = $baseMessage . ' - done';

        if (mb_strlen($message) > $messageLength) {
            $messageLength = mb_strlen($message);
        }

        $output->write("\r" . '<info>' . str_pad($message, $messageLength, ' ', STR_PAD_RIGHT) . '</info>', false, OutputInterface::VERBOSITY_VERBOSE);
        $output->writeln('', OutputInterface::VERBOSITY_VERBOSE);
    }
}
