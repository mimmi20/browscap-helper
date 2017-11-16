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
namespace BrowscapHelper\Command;

use BrowscapHelper\Helper\TargetDirectory;
use BrowscapHelper\Source\BrowscapSource;
use BrowscapHelper\Source\CollectionSource;
use BrowscapHelper\Source\DetectorSource;
use BrowscapHelper\Source\PiwikSource;
use BrowscapHelper\Source\UapCoreSource;
use BrowscapHelper\Source\WhichBrowserSource;
use BrowscapHelper\Source\WootheeSource;
use BrowscapHelper\Writer\DetectorTestWriter;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Logger\ConsoleLogger;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CopyTestsCommand
 *
 * @category   Browscap Helper
 *
 * @author     Thomas Müller <mimmi20@live.de>
 */
class CopyTestsCommand extends Command
{
    /**
     * @var \Monolog\Logger
     */
    private $logger;

    /**
     * @var \Psr\Cache\CacheItemPoolInterface
     */
    private $cache;

    /**
     * @param \Monolog\Logger                   $logger
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     */
    public function __construct(Logger $logger, CacheItemPoolInterface $cache)
    {
        $this->logger = $logger;
        $this->cache  = $cache;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this
            ->setName('copy-tests')
            ->setDescription('Copies tests from browscap and other libraries to browser-detector');
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

        $targetDirectoryHelper = new TargetDirectory();

        $output->writeln('detect next test number ...');

        try {
            $number = $targetDirectoryHelper->getNextTest();
        } catch (\UnexpectedValueException $e) {
            $this->logger->critical($e);
            $output->writeln($e->getMessage());

            return 1;
        }

        $output->writeln('next test: ' . $number);
        $output->writeln('detect directory to write new tests ...');

        try {
            $targetDirectory = $targetDirectoryHelper->getPath();
        } catch (\UnexpectedValueException $e) {
            $this->logger->critical($e);
            $output->writeln($e->getMessage());

            return 1;
        }

        $output->writeln('target directory: ' . $targetDirectory);

        if (!file_exists($targetDirectory)) {
            mkdir($targetDirectory);
        }

        $output->writeln('read existing tests ...');
        $existingTests = [];
        foreach ((new DetectorSource($this->logger, $this->cache))->getUserAgents() as $useragent) {
            $useragent = trim($useragent);

            if (isset($existingTests[$useragent])) {
                $this->logger->alert('    UA "' . $useragent . '" added more than once --> skipped');

                continue;
            }

            $existingTests[$useragent] = 1;
        }

        $output->writeln('init sources ...');
        $totalCounter = 0;
        $source       = new CollectionSource(
            [
                new BrowscapSource($this->logger, $this->cache),
                new PiwikSource($this->logger, $this->cache),
                new UapCoreSource($this->logger),
                new WhichBrowserSource($this->logger, $this->cache),
                new WootheeSource($this->logger, $this->cache),
            ]
        );

        $output->writeln('import tests ...');

        $detectorTestWriter = new DetectorTestWriter($this->logger);

        foreach ($source->getTests() as $useragent => $result) {
            $useragent = trim($useragent);

            if (isset($existingTests[$useragent])) {
                $this->logger->info('    UA "' . $useragent . '" added more than once --> skipped');

                continue;
            }

            $existingTests[$useragent] = 1;

            if ($detectorTestWriter->write($result, $targetDirectory, $number, $useragent, $totalCounter)) {
                $number          = $targetDirectoryHelper->getNextTest();
                $targetDirectory = $targetDirectoryHelper->getPath();

                $output->writeln('next test: ' . $number);
                $output->writeln('target directory: ' . $targetDirectory);

                if (!file_exists($targetDirectory)) {
                    mkdir($targetDirectory);
                }
            }
        }

        $output->writeln('');
        $output->writeln('Es wurden ' . $totalCounter . ' Tests exportiert');

        return 0;
    }
}
