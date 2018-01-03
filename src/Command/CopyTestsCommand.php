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
use BrowscapHelper\Source\MobileDetectSource;
use BrowscapHelper\Source\PiwikSource;
use BrowscapHelper\Source\TxtFileSource;
use BrowscapHelper\Source\UapCoreSource;
use BrowscapHelper\Source\WhichBrowserSource;
use BrowscapHelper\Source\WootheeSource;
use BrowscapHelper\Source\YzalisSource;
use BrowscapHelper\Writer\TxtTestWriter;
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
     * @var string
     */
    private $targetDirectory = '';

    /**
     * @param \Monolog\Logger                   $logger
     * @param \Psr\Cache\CacheItemPoolInterface $cache
     * @param string                            $targetDirectory
     */
    public function __construct(Logger $logger, CacheItemPoolInterface $cache, string $targetDirectory)
    {
        $this->logger          = $logger;
        $this->cache           = $cache;
        $this->targetDirectory = $targetDirectory;

        parent::__construct();
    }

    /**
     * Configures the current command.
     */
    protected function configure(): void
    {
        $this
            ->setName('copy-tests')
            ->setDescription('Copies tests from browscap and other libraries');
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
     * @return int|null null or 0 if everything went fine, or an error code
     * @see    setCode()
     * @throws \FileLoader\Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $consoleLogger = new ConsoleLogger($output);
        $this->logger->pushHandler(new PsrHandler($consoleLogger));

        $testSource = 'tests/';

        $output->writeln('reading already existing tests ...');
        $txtChecks = [];

        foreach ((new TxtFileSource($this->logger, $testSource))->getUserAgents() as $useragent) {
            $useragent = trim($useragent);

            if (array_key_exists($useragent, $txtChecks)) {
                $this->logger->alert('    UA "' . $useragent . '" added more than once --> skipped');

                continue;
            }

            $txtChecks[$useragent] = 1;
        }

        $targetDirectoryHelper = new TargetDirectory();

        $output->writeln('detect next test numbers ...');

        $txtNumber = $targetDirectoryHelper->getNextTest($testSource);

        $output->writeln('next test for Browscap helper: ' . $txtNumber);
        $output->writeln('init sources ...');

        $source = new CollectionSource(
            [
                new BrowscapSource($this->logger, $this->cache),
                new PiwikSource($this->logger, $this->cache),
                new UapCoreSource($this->logger),
                new WhichBrowserSource($this->logger, $this->cache),
                new WootheeSource($this->logger, $this->cache),
                new MobileDetectSource($this->logger, $this->cache),
                new YzalisSource($this->logger, $this->cache),
            ]
        );

        $output->writeln('copy tests ...');

        $txtTotalCounter      = 0;

        $txtWriter          = new TxtTestWriter($this->logger);

        foreach ($source->getUserAgents() as $useragent) {
            $useragent = trim($useragent);

            if (!array_key_exists($useragent, $txtChecks)
                && $txtWriter->write($useragent, $testSource, $txtNumber, $txtTotalCounter)
            ) {
                ++$txtNumber;
            }

            $txtChecks[$useragent] = 1;
        }

        $output->writeln('');
        $output->writeln('tests copied for Browscap helper:  ' . $txtTotalCounter);

        return 0;
    }
}
