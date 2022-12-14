<?php
/**
 * This file is part of the browscap-helper package.
 *
 * Copyright (c) 2015-2022, Thomas Mueller <mimmi20@live.de>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types = 1);

namespace BrowscapHelper;

use BrowscapHelper\Helper\ExistingTestsLoader;
use BrowscapHelper\Helper\ExistingTestsRemover;
use BrowscapHelper\Helper\JsonNormalizer;
use BrowscapHelper\Helper\RewriteTests;
use Exception;
use Symfony\Component\Console\Application;

use function realpath;

final class BrowscapHelper extends Application
{
    public const DEFAULT_RESOURCES_FOLDER = '../sources';

    /** @throws Exception */
    public function __construct()
    {
        parent::__construct('Browscap Helper Project', 'dev-master');

        $sourcesDirectory = (string) realpath(__DIR__ . '/../sources/');

        $jsonNormalizer       = new JsonNormalizer();
        $rewriteTestsHelper   = new RewriteTests($jsonNormalizer);
        $existingTestsLoader  = new ExistingTestsLoader();
        $existingTestsRemover = new ExistingTestsRemover();

        $this->add(new Command\ConvertLogsCommand($existingTestsLoader, $existingTestsRemover, $rewriteTestsHelper, $sourcesDirectory));
        $this->add(new Command\CopyTestsCommand($existingTestsLoader, $existingTestsRemover, $rewriteTestsHelper, $sourcesDirectory));
        $this->add(new Command\RewriteTestsCommand($existingTestsLoader, $existingTestsRemover, $jsonNormalizer));
    }
}
