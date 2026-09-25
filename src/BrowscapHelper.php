<?php

/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2026, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper;

use BrowscapHelper\Helper\ExistingTestsLoader;
use BrowscapHelper\Helper\ExistingTestsRemover;
use BrowscapHelper\Helper\RewriteTests;
use Exception;
use Symfony\Component\Console\Application;

use function realpath;

final class BrowscapHelper extends Application
{
    /** @api */
    public const string DEFAULT_RESOURCES_FOLDER = '../sources';

    /** @throws Exception */
    public function __construct()
    {
        parent::__construct('Browscap Helper Project', 'dev-master');

        $sourcesDirectory = (string) realpath(__DIR__ . '/../sources/');

        $rewriteTests         = new RewriteTests();
        $existingTestsLoader  = new ExistingTestsLoader();
        $existingTestsRemover = new ExistingTestsRemover();

        $this->addCommand(
            new Command\CopyTestsCommand(
                existingTestsLoader: $existingTestsLoader,
                existingTestsRemover: $existingTestsRemover,
                rewriteTests: $rewriteTests,
                sourcesDirectory: $sourcesDirectory,
            ),
        );
        $this->addCommand(
            new Command\RewriteTestsCommand(
                existingTestsLoader: $existingTestsLoader,
                existingTestsRemover: $existingTestsRemover,
            ),
        );
    }
}
