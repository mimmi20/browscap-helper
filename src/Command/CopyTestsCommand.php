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
namespace BrowscapHelper\Command;

use BrowscapHelper\Source\BrowscapSource;
use BrowscapHelper\Source\CollectionSource;
use BrowscapHelper\Source\CrawlerDetectSource;
use BrowscapHelper\Source\MobileDetectSource;
use BrowscapHelper\Source\PiwikSource;
use BrowscapHelper\Source\TxtFileSource;
use BrowscapHelper\Source\UapCoreSource;
use BrowscapHelper\Source\WhichBrowserSource;
use BrowscapHelper\Source\WootheeSource;
use BrowscapHelper\Source\YzalisSource;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Class CopyTestsCommand
 *
 * @category   Browscap Helper
 */
class CopyTestsCommand extends Command
{
    /**
     * @var string
     */
    private $sourcesDirectory = '';

    /**
     * @var string
     */
    private $targetDirectory = '';

    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @param \Monolog\Logger $logger
     * @param string          $sourcesDirectory
     * @param string          $targetDirectory
     */
    public function __construct(Logger $logger, string $sourcesDirectory, string $targetDirectory)
    {
        $this->sourcesDirectory = $sourcesDirectory;
        $this->targetDirectory  = $targetDirectory;
        $this->logger           = $logger;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this
            ->setName('copy-tests')
            ->setDescription('Copies tests from browscap and other libraries')
            ->addOption(
                'resources',
                null,
                InputOption::VALUE_REQUIRED,
                'Where the resource files are located',
                $this->sourcesDirectory
            );
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @param InputInterface  $input  An InputInterface instance
     * @param OutputInterface $output An OutputInterface instance
     *
     * @throws \LogicException When this abstract method is not implemented
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see    setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $this->logger->pushHandler(new PsrHandler($consoleLogger));

        $sourcesDirectory = $input->getOption('resources');

        $output->writeln('reading already existing tests ...');

        $testSource = 'tests';
        $txtChecks  = [];

        foreach ($this->getHelper('useragent')->getHeaders(new JsonFileSource($this->logger, $testSource), false) as $header) {
            $seachHeader = json_encode($header);

            if (array_key_exists($seachHeader, $txtChecks)) {
                $this->logger->alert('    Header "' . $seachHeader . '" added more than once --> skipped');

                continue;
            }

            $txtChecks[$seachHeader] = $header;
        }

        $output->writeln('remove existing tests ...');

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

        $output->writeln('init sources ...');

        $source = new CollectionSource(
            [
                new BrowscapSource($this->logger),
                new PiwikSource($this->logger),
                new UapCoreSource($this->logger),
                new WhichBrowserSource($this->logger),
                new WootheeSource($this->logger),
                new MobileDetectSource($this->logger),
                new YzalisSource($this->logger),
                new CrawlerDetectSource($this->logger),
                new TxtFileSource($this->logger, $sourcesDirectory),
            ]
        );

        $output->writeln('copy tests from sources ...');

        $txtTotalCounter = 0;

        foreach ($this->getHelper('useragent')->getHeaders($source) as $header) {
            $seachHeader = json_encode($header);

            if (array_key_exists($seachHeader, $txtChecks)) {
                continue;
            }

            $txtChecks[$seachHeader] = $header;
            ++$txtTotalCounter;
        }

        $output->writeln('rewrite tests ...');

        $folderChunks = array_chunk(array_unique($txtChecks), 1000);

        foreach ($folderChunks as $folderId => $folderChunk) {
            $this->getHelper('json-test-writer')->write(
                $folderChunk,
                $testSource,
                $folderId
            );
        }

        $output->writeln('');
        $output->writeln('tests copied for Browscap helper:    ' . $txtTotalCounter);
        $output->writeln('tests available for Browscap helper: ' . count($txtChecks));

        return 0;
    }
}
