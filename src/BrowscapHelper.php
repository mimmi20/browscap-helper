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
namespace BrowscapHelper;

use BrowscapHelper\Command\Helper\BrowscapTestWriter;
use BrowscapHelper\Command\Helper\DetectorTestWriter;
use BrowscapHelper\Command\Helper\ExistingTestsLoader;
use BrowscapHelper\Command\Helper\ExistingTestsRemover;
use BrowscapHelper\Command\Helper\JsonTestWriter;
use BrowscapHelper\Command\Helper\RegexFactory;
use BrowscapHelper\Command\Helper\RegexLoader;
use BrowscapHelper\Command\Helper\RewriteTests;
use BrowscapHelper\Command\Helper\TxtTestWriter;
use BrowscapHelper\Command\Helper\YamlTestWriter;
use Symfony\Component\Console\Application;

class BrowscapHelper extends Application
{
    /**
     * @var string
     */
    public const DEFAULT_RESOURCES_FOLDER = '../sources';

    /**
     * BrowscapHelper constructor.
     *
     * @throws \Exception
     */
    public function __construct()
    {
        parent::__construct('Browscap Helper Project', 'dev-master');

        $sourcesDirectory = (string) realpath(__DIR__ . '/../sources/');
        $targetDirectory  = (string) realpath(__DIR__ . '/../results/');

        $commands = [
            new Command\ConvertLogsCommand($sourcesDirectory, $targetDirectory),
            new Command\CopyTestsCommand($sourcesDirectory, $targetDirectory),
            new Command\CreateTestsCommand($sourcesDirectory, $targetDirectory),
            new Command\RewriteTestsCommand(),
        ];

        foreach ($commands as $command) {
            $this->add($command);
        }

        $targetDirectoryHelper = new Command\Helper\TargetDirectory();
        $this->getHelperSet()->set($targetDirectoryHelper);

        $browscapTestWriter = new BrowscapTestWriter($targetDirectory);
        $this->getHelperSet()->set($browscapTestWriter);

        $txtTestWriter = new TxtTestWriter();
        $this->getHelperSet()->set($txtTestWriter);

        $detectorTestWriter = new DetectorTestWriter();
        $this->getHelperSet()->set($detectorTestWriter);

        $yamlTestWriter = new YamlTestWriter();
        $this->getHelperSet()->set($yamlTestWriter);

        $jsonTestWriter = new JsonTestWriter();
        $this->getHelperSet()->set($jsonTestWriter);

        $regexFactory = new RegexFactory();
        $this->getHelperSet()->set($regexFactory);

        $regexLoader = new RegexLoader();
        $this->getHelperSet()->set($regexLoader);

        $existingTestsLoader = new ExistingTestsLoader();
        $this->getHelperSet()->set($existingTestsLoader);

        $existingTestsRemover = new ExistingTestsRemover();
        $this->getHelperSet()->set($existingTestsRemover);

        $rewriteTestsHelper = new RewriteTests();
        $this->getHelperSet()->set($rewriteTestsHelper);
    }
}
