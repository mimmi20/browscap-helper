<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2018, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);
namespace BrowscapHelper\Command\Helper;

use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Finder\Finder;

class ExistingTestsRemover extends Helper
{
    public function getName()
    {
        return 'existing-tests-remover';
    }

    /**
     * @param string $testSource
     *
     * @return void
     */
    public function remove(string $testSource): void
    {
        $finder = new Finder();
        $finder->files();
        $finder->ignoreDotFiles(true);
        $finder->ignoreVCS(true);
        $finder->sortByName();
        $finder->ignoreUnreadableDirs();
        $finder->in($testSource);

        foreach ($finder as $file) {
            unlink($file->getPathname());
        }
    }
}
