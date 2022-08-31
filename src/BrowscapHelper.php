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

use BrowscapHelper\Command\Helper\ExistingTestsLoader;
use BrowscapHelper\Command\Helper\ExistingTestsRemover;
use BrowscapHelper\Command\Helper\JsonNormalizer;
use BrowscapHelper\Command\Helper\RewriteTests;
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

        $this->add(new Command\ConvertLogsCommand($sourcesDirectory));
        $this->add(new Command\CopyTestsCommand($sourcesDirectory));
        $this->add(new Command\RewriteTestsCommand());

        $existingTestsLoader = new ExistingTestsLoader();
        $this->getHelperSet()->set($existingTestsLoader);

        $existingTestsRemover = new ExistingTestsRemover();
        $this->getHelperSet()->set($existingTestsRemover);

        $rewriteTestsHelper = new RewriteTests();
        $this->getHelperSet()->set($rewriteTestsHelper);

        $jsonNormalizer = new JsonNormalizer();
        $this->getHelperSet()->set($jsonNormalizer);
    }
}
